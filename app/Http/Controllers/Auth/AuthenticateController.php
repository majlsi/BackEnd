<?php

namespace App\Http\Controllers\Auth;

use Jobs\SendEmailToAdminsForNewRegisteration;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Connectors\SmsConnector;
use Helpers\EmailHelper;
use Helpers\OrganizationHelper;
use Helpers\SecurityHelper;
use Helpers\SmsHelper;
use Helpers\UserHelper;
use Helpers\FailedLoginAttemptHelper;
use Illuminate\Http\Request;
use Models\Organization;
use Models\User;
use Services\SocialService;
use Services\UserService;
use Services\UserVerificationTokenService;
use Services\FailedLoginAttemptService;
use Connectors\ChatConnector;
use Connectors\LdapConnector;
use Connectors\SmsSwccGatewayConnector;
use Services\MeetingGuestService;
use Services\MeetingService;
use Services\ChatService;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Validator;

class AuthenticateController extends Controller
{

    private $userService;
    private $meetingService;
    private $socialService;
    private $userHelper;
    private $securityHelper;
    private $organizationHelper;
    private $emailHelper;
    private $failedLoginAttemptHelper;
    private $failedLoginAttemptService;
    private $userVerificationTokenService;
    private $smsConnector;
    private $smsSwccConnector;
    private MeetingGuestService $meetingGuestService;

    private $chatService;

    public function __construct(
        UserService $userService,
        SocialService $socialService,
        UserHelper $userHelper,
        SecurityHelper $securityHelper,
        OrganizationHelper $organizationHelper,
        EmailHelper $emailHelper,
        UserVerificationTokenService $userVerificationTokenService,
        SmsConnector $smsConnector,
        SmsSwccGatewayConnector $smsSwccConnector,
        FailedLoginAttemptHelper $failedLoginAttemptHelper,
        MeetingService $meetingService,
        FailedLoginAttemptService $failedLoginAttemptService,
        MeetingGuestService $meetingGuestService,
        ChatService $chatService,
        
    ) {
        $this->userService = $userService;
        $this->socialService = $socialService;
        $this->userHelper = $userHelper;
        $this->securityHelper = $securityHelper;
        $this->organizationHelper = $organizationHelper;
        $this->emailHelper = $emailHelper;
        $this->smsConnector = $smsConnector;
        $this->smsSwccConnector = $smsSwccConnector;
        $this->userVerificationTokenService = $userVerificationTokenService;
        $this->failedLoginAttemptHelper = $failedLoginAttemptHelper;
        $this->failedLoginAttemptService = $failedLoginAttemptService;
        $this->meetingService = $meetingService;
        $this->meetingGuestService = $meetingGuestService;
        $this->chatService = $chatService;
    }

    public function index()
    {
        // TODO: show users
    }

    public function register(Request $request)
    {

        $data = $request->all();
        $errors = [];

        $userData = $this->userHelper->prepareUserDataOnCreate($data, null, config('providers.custom'));
        $validator = Validator::make($userData, User::rules('register'), User::messages('register'));

        if ($validator->fails()) {
            $errors = array_merge($errors, array_values($validator->errors()->toArray()));
        }

        $organizationData = $this->organizationHelper->prepareOrganizationDataOnCreate($data['organization']);
        $validator = Validator::make($organizationData, Organization::rules('save'), Organization::messages('save'));
        if ($validator->fails()) {
            $errors = array_merge($errors, array_values($validator->errors()->toArray()));
        }

        // $logoData = $this->imageHelper->prepareLogoDataOnCreate($data['organization']);
        // $validator = Validator::make($logoData, Image::rules('save'), Image::messages('save'));
        // if($validator->fails()){
        //     $errors = array_merge($errors, array_values($validator->errors()->toArray()));
        // }

        if (count($errors) != 0) {
            return response()->json(['error' => $errors], 400);
        }

        $registrationData = ['user_data' => $userData, 'organization_data' => $organizationData];
        $created = false;
        try {
            $created = $this->userService->create($registrationData);
        } catch (\Exception $e) {
            report($e);
            return response($e, 200);
        }
        if ($created) {
            $this->emailHelper->sendRegistrationMail($created->username, $created->name_ar, $created->name);
            SendEmailToAdminsForNewRegisteration::dispatch($created);
            return response(['message' => 'Your registration completed.'], 200);
        }
    }

    /**
     * Login
     * @param Request $request
     * @return Token
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->only('username', 'password');
        try {
            // verify the credentials and create a token for the user
            $user = $this->userService->login(trim($credentials['username']), $credentials['password']);
            if (!$user) {
                // create failed login attempt
                $user = $this->userService->getUserByEmail($credentials['username']);
                $failedLoginAttemptData = $this->failedLoginAttemptHelper->prepareFaildLoginAttemptDataAtCreate($user, $request);
                $this->failedLoginAttemptService->create($failedLoginAttemptData);
                return response()->json(['error' => 'Wrong email or password', 'error_ar' => 'بريد إلكتروني أو كلمة مرور خاطئة'], 400);
            } else if ($user && $user->role_id !== config('roles.admin')  && ($user->organization_id && ($user->organization_is_active == null || $user->organization_is_active == 0))) {
                return response()->json(['error' => 'Your organization is not active', 'error_ar' => 'منشأتك غير مفعلة'], 400);
            } else if ($user && $user->role_id !== config('roles.admin')  && ($user->organization_id && ($user->expiry_date_to  && Carbon::parse($user->expiry_date_to) < Carbon::now()))) {
                return response()->json(['error' => 'Your organization license has been ended', 'error_ar' => 'تم إنتهاء ترخيص منشأتك'], 400);
            } else if ($user && ($user->is_active == null || $user->is_active == 0)) {
                return response()->json(['error' => 'Your account is not active', 'error_ar' => 'حسابك غير فعال'], 400);
            }
        } catch (JWTException $e) {
            // something went wrong
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        $customClaims = ['user_id' => $user->id];
        // login into chat app
        if ($user->chat_user_id) {
            $loginResponse = ChatConnector::login(['username' => $user->username, 'app_id' => config('chat.chatAppId')]);
            if ($loginResponse['is_success']) {
                $customClaims['chat_token'] = $loginResponse['response']['token'];
            } else {
                return response()->json(['error' => 'error in chat login', 'error_ar' => 'لا يمكن الدخول الى حساب المحادثه'], 400);
            }
        }
        $token = JWTAuth::fromUser($user, $customClaims);
        // if no errors are encountered we can return a JWT
        $is_verified = $user->is_verified;
        $roles = [$user->role->role_name];
        if ($user->mainRight) {
            $mainRightUrl = $user->mainRight->right_url;
        } else {
            $mainRightUrl = $user->mainRight;
        }
        if ($user->organization) {
            $hasTwoFactorAuth = $user->organization->has_two_factor_auth;
        } else {
            $hasTwoFactorAuth = 0;
        }
        $this->userService->update($user->id, ["last_login" => Carbon::now()]);

        return response()->json(compact('token', 'is_verified', 'roles', 'mainRightUrl', 'hasTwoFactorAuth'), 200);
    }

    /**
     * Get Current Authenticated User
     *
     * @return User
     */
    public function getAuthenticatedUser()
    {

        try {
            $payload = JWTAuth::parseToken()->getPayload();
            // allow meeting guests and normal users
            if (!$payload->get('user_id') && !$payload->get('meeting_id')) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {

            return response()->json(['token_expired'], 400);
        } catch (TokenInvalidException $e) {

            return response()->json(['token_invalid'], 400);
        } catch (JWTException $e) {

            return response()->json(['token_absent'], 400);
        }

        if($payload->get('user_id')){
            $user = $this->userService->getUserDetails($payload->get('user_id'));
            $user["signatureUrl"] = $user->organization ? $user->organization->signature_url : null;
            $user["signatureUserName"] = $user->organization ? $user->organization->signature_username : null;
            $user["signaturePassword"] = $user->organization ? $user->organization->signature_password : null;
    
        } else {
            // allow meeting guests
            $user = $this->meetingGuestService->getById($payload->get('meeting_guest_id'));
            $user['organization'] = $user->meeting->organization;
            $user['meeting_guest_id'] = $user->id;
        }
        // the token is valid and we have found the user via the sub claim
        return response()->json(compact('user'), 200);
    }

    /**
     * Log Out
     */
    public function invalidate()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }

    public function verifyLoginCode(Request $request)
    {
        $verificationCode = $request->only('verificationCode');

        if (!$verificationCode || $verificationCode["verificationCode"] == '') {
            return response()->json(["error" => "Please Enter your Code"], 400);
        }

        $account = $this->securityHelper->getCurrentUser();

        $codeExists = $this->userVerificationTokenService->checkCode($verificationCode["verificationCode"], $account->id);

        if (!$codeExists) {
            return response()->json(["error" => "Invalid Code"], 404);
        } else {
            // update flag is used
            $this->userVerificationTokenService->update($codeExists->id, ['is_used' => 1]);
        }

        if ($account->mainRight) {
            $mainRightUrl = $account->mainRight->right_url;
        } else {
            $mainRightUrl = $account->mainRight;
        }

        return response()->json(compact('mainRightUrl'), 200);
    }

    public function socialLogin(Request $request)
    {
        $data = $request->all();
        $provider = $data["provider"];
        $accessToken = $request->social_access_token;
        $googleAuthCode = $request->google_auth_code;

        $socialUser = $this->socialService->getUserFromToken($provider, $accessToken, $googleAuthCode);

        if ($socialUser != null) {
            $user = $this->socialService->handleSocialUser($socialUser, $provider);
        } else {
            return response()->json(['error' => "Not Valid"], 400);
        }

        //login here
        $customClaims = ['user_id' => $user->id];
        $token = JWTAuth::fromUser($user, $customClaims);
        $is_verified = $user->is_verified;
        $is_profile_completed = $user->username ? 1 : 0;
        return response()->json(compact('token', 'is_verified', 'is_profile_completed'), 200);
    }

    public function handleSocialCallback(Request $request, $provider)
    {
        $user = $this->socialService->socialLogin($provider);
        if (isset($user["error"])) {
            return response()->json(['error' => $user["error"]], 400);
        }
        //login here
        $customClaims = ['user_id' => $user->id];
        $token = JWTAuth::fromUser($user, $customClaims);
        $is_verified = $user->is_verified;
        $is_profile_completed = $user->username ? 1 : 0;
        return response()->json(compact('token', 'is_verified', 'is_profile_completed'), 200);
    }

    public function sendCode(Request $request)
    {
        $data = $request->all();
        $lang = $data['lang'];
        if (isset($data['notification_option_id'])) {
            $user = $this->securityHelper->getCurrentUser();
            $hasTwoFactorAuth = $user->organization ? $user->organization->has_two_factor_auth : 0;
            if ($hasTwoFactorAuth == 1) {
                $code = $this->userVerificationTokenService->create(['user_id' => $user->id]);
                $this->sendNotificationForCode($user, $code, $data, $lang);
                return response()->json(['message' => 'Code sent successfully', 'message_ar' => 'تم إرسال الرمز'], 200);
            }
        } else {
            return response()->json(['error' => 'Notification option is required', 'error_ar' => 'طريقة الإشعار مطلوبه'], 400);
        }
    }

    public function sendNotificationForCode($user, $code, $data, $lang)
    {
        $languageId = $lang == 'en' ? config('languages.en') : config('languages.ar');
        switch ($data['notification_option_id']) {
            case config('notificationOptions.send_email'):
                $this->emailHelper->sendLoginVerificationCode($user->email, $user->name_ar, $user->name, $code["verification_code"], '', $languageId);
                break;
            case config('notificationOptions.send_sms'):
                $this->sendSMS($user, $code, $languageId);
                break;
            case config('notificationOptions.send_email_and_sms'):
                $this->emailHelper->sendLoginVerificationCode($user->email, $user->name_ar, $user->name, $code["verification_code"], '', $languageId);
                $this->sendSMS($user, $code, $languageId);
                break;
        }
    }

    public function sendSMS($user, $code, $languageId)
    {
        if ($user->user_phone) {
            if(config('smsGateway.smsSwccGateway'))
            {
                if ($languageId == config('languages.ar')) {
                    $this->smsSwccConnector->sendSMS($user->user_phone, SmsHelper::getSmsBody('sms.TwoFactorAuthCodeAr', ["code" => $code["verification_code"]]));
                } else if ($languageId == config('languages.en')) {
                    $this->smsSwccConnector->sendSMS($user->user_phone, SmsHelper::getSmsBody('sms.TwoFactorAuthCodeEn', ["code" => $code["verification_code"]]));
                }
            }
            else
            {
                if ($languageId == config('languages.ar')) {
                    $this->smsConnector->sendSMS($user->user_phone, SmsHelper::getSmsBody('sms.TwoFactorAuthCodeAr', ["code" => $code["verification_code"]]));
                } else if ($languageId == config('languages.en')) {
                    $this->smsConnector->sendSMS($user->user_phone, SmsHelper::getSmsBody('sms.TwoFactorAuthCodeEn', ["code" => $code["verification_code"]]));
                }
            }
        }
    }

    /**
     * Validate Guest Token
     * @param Request $request
     * @return Response
     */
    public function AuthenticateGuest(Request $request)
    {
        $payload = JWTAuth::parseToken()->getPayload();
        $meetingID = $payload->get('meeting_id');
        $email = $payload->get('email');

        $result = $this->userService->ValidateGuestToken($meetingID, $email);
        if ($result != null && !isset($result->meeting_title_ar)) {
            return response()->json($result, 400);
        }

        return response()->json($result, 200);
    }
}
