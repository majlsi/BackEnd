<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Services\UserService;
use Services\ChatService;
use Connectors\ChatConnector;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use Helpers\FailedLoginAttemptHelper;
use Services\FailedLoginAttemptService;
use Illuminate\Support\Facades\Validator;
use Models\User;
use Services\LdapService;

class LdapAuthenticationController extends Controller
{
    private $userService;
    private $ldapService;
    private $chatService;
    private $failedLoginAttemptHelper;
    private $failedLoginAttemptService;
    public function __construct(UserService $userService, LdapService $ldapService, ChatService $chatService, FailedLoginAttemptHelper $failedLoginAttemptHelper, FailedLoginAttemptService $failedLoginAttemptService)
    {
        $this->userService = $userService;
        $this->ldapService = $ldapService;
        $this->chatService = $chatService;
        $this->failedLoginAttemptHelper = $failedLoginAttemptHelper;
        $this->failedLoginAttemptService = $failedLoginAttemptService;
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
            $user = $this->userService->login(trim($credentials['username']), $credentials['password']);
            if ($user == null || ($user->role_id != config('roles.admin') && $user->role_id != config('roles.organizationAdmin'))) {
                try {
                    $user = $this->authenticateWithLdap($request, $credentials['username'], $credentials['password']);
                    if (!$user)
                        return response()->json(['error' => 'Wrong email or password', 'error_ar' => 'بريد إلكتروني أو كلمة مرور خاطئة'], 400);
                } catch (\Exception $e) {
                    $user = $this->userService->getUserByEmail($credentials['username']);
                    $failedLoginAttemptData = $this->failedLoginAttemptHelper->prepareFaildLoginAttemptDataAtCreate($user, $request);
                    $this->failedLoginAttemptService->create($failedLoginAttemptData);
                    return response()->json(['error' => 'Wrong email or password', 'error_ar' => 'بريد إلكتروني أو كلمة مرور خاطئة'], 400);
                }
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
    private function authenticateWithLdap(Request $request, string $username, string $password)
    {
        $ldapUser = $this->ldapService->authenticateWithLdap($request, $username, $password);
        if (!empty($ldapUser)) {
            $user = $this->registerUserFromLdap($request, $ldapUser);
        }
        return $user;
    }
    private function registerUserFromLdap(Request $request, array $user)
    {
        $data = $request->all();
        $userData = $this->ldapService->registerUserFromLdap($request, $user);
        $user = $this->userService->getUserByEmail($data['email']);
        if (!$user) {
            $validator = Validator::make($userData, User::rules('save'), User::messages('save'));
            if ($validator->fails()) {
                return response()->json(['error' => array_values($validator->errors()->toArray())], 400);
            }
            $user = $this->userService->create(['user_data' => $userData, 'profile_image' => []]);
            $this->chatService->createChatUsers([$user]);
        }
        return $user;
    }
}
