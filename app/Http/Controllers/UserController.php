<?php

namespace App\Http\Controllers;

use App\Exports\ExportBlacklistedUsers;
use Helpers\ImageHelper;
use Helpers\SecurityHelper;
use Helpers\UserHelper;
use Helpers\SignatureHelper;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Models\User;
use Services\OrganizationService;
use Services\UserService;
use Services\ChatService;
use Services\LdapService;
use Tymon\JWTAuth\Facades\JWTAuth;
use Validator;

class UserController extends Controller
{

    private $userService, $securityHelper;
    private $organizationService;
    private $userHelper;
    private $imageHelper;
    private $chatService;
    private $signatureHelper;
    private $ldapService;

    public function __construct(
        UserService $userService,
        SecurityHelper $securityHelper,
        OrganizationService $organizationService,
        UserHelper $userHelper,
        ImageHelper $imageHelper,
        ChatService $chatService,
        SignatureHelper $signatureHelper,
        LdapService $ldapService
    ) {
        $this->userService = $userService;
        $this->securityHelper = $securityHelper;
        $this->organizationService = $organizationService;
        $this->userHelper = $userHelper;
        $this->imageHelper = $imageHelper;
        $this->chatService = $chatService;
        $this->signatureHelper = $signatureHelper;
        $this->ldapService = $ldapService;
    }

    public function getPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->userService->filteredUsers($filter, $user->role_id, $user->organization_id), 200);
    }

    public function show($id)
    {
        $user = $this->userService->getById($id);
        return response()->json($user, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $user = $this->securityHelper->getCurrentUser();
        $data['organization_id'] = null;
        if ($user && $user->organization_id) {
            $userOrganizationData = $this->organizationService->getOrganizationData($user->organization_id);
            if ($userOrganizationData->users_number >= $userOrganizationData->organization_number_of_users) {
                return response()->json(['error' => 'You can\'t add new user'], 400);
            }
            $data['organization_id'] = $user->organization_id;
        } else if (!$user || $user->role_id != config('roles.admin')) {
            return response()->json(['error' => 'You can\'t add users'], 400);
        }
        if ($user->role_id === config('roles.admin')) {
            $profileImage = $this->imageHelper->profileImageForUsersCreatedByAdmin();
        } else {
            $profileImage = [];
        }
        $data['username'] = $data['email'];
        $data['oauth_provider'] = config('providers.custom');
        $data['is_verified'] = 1;

        $validator = Validator::make($data, User::rules('save'), User::messages('save'));
        if ($validator->fails()) {
            return response()->json(['error' => array_values($validator->errors()->toArray())], 400);
        }
        $user = $this->userService->create(['user_data' => $data, 'profile_image' => $profileImage]);
        //if ($user->role_id != config('roles.admin')) { // organization user only
        // create user at chat app
        $this->chatService->createChatUsers([$user]);
        //}
        return response()->json($user, 200);
    }

    public function addMultiple(Request $request)
    {
        $data = $request->all();

        $user = $this->securityHelper->getCurrentUser();
        if ($user && $user->organization_id) {
            $userOrganizationData = $this->organizationService->getOrganizationData($user->organization_id);
            if (($userOrganizationData->users_number + count($data)) > $userOrganizationData->organization_number_of_users) {
                return response()->json([[['error' => ['Users added count is more than the organization allowed number of users'], 'error_ar' => ['عدد المستخدمين المضاف اكبر من العدد المسموح لأعضاء المنشأه']]]], 400);
            }
        } else if (!$user) {
            return response()->json(['error' => 'You can\'t add users'], 400);
        }

        $profileImage = [];

        $userData = $this->userHelper->prepareMultipleUserDataOnCreate($data, config('providers.custom'), $user->organization_id);

        $validator = Validator::make($userData, User::rules('save-multiple',null,$userData), User::messages('save-multiple'));
        if ($validator->fails()) {
            return response()->json(array_values($validator->errors()->toArray()), 400);
        }
        $users = $this->userService->create(['user_data' => $userData, 'profile_image' => $profileImage, "multiple" => true]);
        // create users at chat app
        $this->chatService->createChatUsers($users);
        return response()->json($users, 200);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $data['username'] = $data['email'];
        $validator = Validator::make($data, User::rules('update', $id), User::messages('update'));
        if ($validator->fails()) {
            return response()->json(["error" => array_values($validator->errors()->toArray())], 400);
        }
        $user = $this->userService->getById($id);
        $user->username = $data['username'];
        $this->signatureHelper->updateUserByEmail($user->organization, $user->email, $data['email'], $data['user_phone']);
        $response = $this->chatService->updateChatUser($user);
        if ($response['is_success']) {
            $updated = $this->userService->update($id, $data);
            if ($updated) {
                return response()->json(["message" => ['User updated successfully']], 200);
            }
        } else {
            return response()->json(['error' => 'You can\'t update this user', 'error_ar' => 'لا يمكن تعديل بيانات هذا المستخدم'], 400);
        }
    }

    public function destroy($id)
    {
        try {
            $user = $this->userService->getById($id);
            if (!$user) {
                return response()->json(['error' => 'You can\'t delete this user', 'error_ar' => 'لا يمكن حذف هذا المستخدم'], 400);
            }

            $deleted = $this->userService->delete($id);
            if ($deleted != 0) {
                return response()->json(['message' => 'data deleted successfully'], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'You can\'t delete this user', 'error_ar' => 'لا يمكن حذف هذا المستخدم'], 400);
        }
    }

    public function getOrganizationUsers()
    {
        $user = $this->securityHelper->getCurrentUser();
        if (!$user) {
            return response()->json(['error' => 'You can\'t access!'], 400);
        }
        return response()->json($this->userService->getOrganizationUsers($user->organization_id)->load('image'), 200);
    }

    public function getOrganizationUsersWithStakeholders(Request $request)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        if (!$user) {
            return response()->json(['error' => 'You can\'t access!'], 400);
        }
        $includeStakeholders = isset($data['include_stakeholders']) ? $data['include_stakeholders'] : false;
        $name = isset($data['name']) ? $data['name'] : '';
        if(config('customSetting.ldapIntegration')&&isset($data['name']))
        {
            $ldapUsers=$this->ldapService->getLdapUsers($data['name']);
            $localUsers=$this->userService->getOrganizationUsersWithStakeholders($user->organization_id, $includeStakeholders, $name);

            $ldapUsersArray = is_object($ldapUsers) ? $ldapUsers->toArray() : [];
            $localUsersArray = is_object($localUsers) ? $localUsers->toArray() : [];

            $filteredLdapUsers = collect($ldapUsersArray)->filter(function ($ldapUser) use ($localUsersArray) {
                return !collect($localUsersArray)->contains('email', $ldapUser['email']);
            });
            $combinedUsers = collect(array_merge($filteredLdapUsers->toArray(), $localUsersArray));
            return response()->json($combinedUsers, 200);
        }
        return response()->json($this->userService->getOrganizationUsersWithStakeholders($user->organization_id, $includeStakeholders, $name), 200);
    }
    public function getMatchedOrganizationUsers(Request $request)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        if (!$user) {
            return response()->json(['error' => 'You can\'t access!'], 400);
        }
        if (!isset($data['name'])) {
            return response()->json(['error' => 'The name field is required.'], 400);
        }
        if(config('customSetting.ldapIntegration'))
        {
            $ldapUsers=$this->ldapService->getLdapUsers($data['name']);
            $localUsers=$this->userService->getMatchedOrganizationUsers($user->organization_id, $data['name']);

            $ldapUsersArray = is_object($ldapUsers) ? $ldapUsers->toArray() : [];
            $localUsersArray = is_object($localUsers) ? $localUsers->toArray() : [];

            $filteredLdapUsers = collect($ldapUsersArray)->filter(function ($ldapUser) use ($localUsersArray) {
                return !collect($localUsersArray)->contains('email', $ldapUser['email']);
            });
            $combinedUsers = collect(array_merge($filteredLdapUsers->toArray(), $localUsersArray));
            return response()->json($combinedUsers, 200);
        }
        return response()->json($this->userService->getMatchedOrganizationUsers($user->organization_id, $data['name']), 200);
    }

    public function activeDeactiveUser(Request $request)
    {
        $data = $request->all();
        $errors = [];
        $currentUser = $this->securityHelper->getCurrentUser();
        if (!$currentUser) {
            return response()->json(['error' => 'You don\'t have permission.'], 400);
        }
        if (!isset($data['user_id'])) {
            $errors[] = 'The user id field is required.';
        }
        if (!isset($data['is_active'])) {
            $errors[] = 'The is active field is required.';
        }
        if (count($errors) !== 0) {
            return response()->json(['error' => $errors], 400);
        }
        $activationUser = $this->userService->getById($data['user_id']);
        if ($activationUser->organization_id != $currentUser->organization_id) { // organization admin
            return response()->json(['error' => 'You don\'t have permission.'], 400);
        }
        $this->userService->activeDeactiveUser($data['user_id'], $data['is_active']);
        return response()->json(['message' => 'User Update successfully.'], 200);
    }

    public function updateMyProfile(Request $request)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        if (!$user || $user->id !== $data['id']) {
            return response()->json(['error' => 'You can\'t access!'], 400);
        }
        $userData = $this->userHelper->prepareUserDataOnUpdate($data, $user);

        $validator = Validator::make($userData, User::rules('update', $user->id));
        $validatorAr = Validator::make($userData, User::rules('update', $user->id), User::messages('update'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all(), "error_ar" => [array_values(($validatorAr->errors()->toArray()))[0][0]["message_ar"]]], 400);
        }
        $user->username = $userData['username'];
        $this->signatureHelper->updateUserByEmail($user->organization, $user->email, $data['email'], $data['user_phone']);
        $response = $this->chatService->updateChatUser($user);
        if ($response['is_success']) {
            $userData['is_updated_by_user'] = 1;
            $updated = $this->userService->update($user->id, $userData);
            if ($updated) {
                return response()->json(["message" => ['Your profile updated successfully']], 200);
            }
        } else {
            return response()->json(['error' => 'You can\'t update your profile', 'error_ar' => 'لا يمكن تعديل بياناتك '], 400);
        }
    }

    public function checkIfOrganizationCanAddUsers(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        $data = ['can_add' => 1];
        if (!$user) {
            return response()->json(["error" => 'You can\'t access!'], 400);
        } else if ($user->organization_id) {
            $userOrganizationData = $this->organizationService->getOrganizationData($user->organization_id);
            if ($userOrganizationData->users_number >= $userOrganizationData->organization_number_of_users) {
                $data = ['can_add' => 0];
            }
        }
        return response()->json((object) $data, 200);
    }

    public function searchOrganizationUsersAndCommittees(Request $request)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        if (!$user) {
            return response()->json(['error' => 'You can\'t access!'], 400);
        }
        if (!isset($data['name'])) {
            return response()->json(['error' => 'The name field is required.'], 400);
        }
        return response()->json($this->userService->searchOrganizationUsersAndCommittees($user->organization_id, $data['name']), 200);
    }

    public function getCurrentURL(Request $request)
    {
        $url = $request->root();
        return response()->json(["apiUrl" => $url, "frontUrl" => config('appUrls.front.cloudUrl'), "redisUrl" => config('appUrls.redis.cloudUrl')], 200);
    }

    public function getOrganizationUsersPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->userService->filteredOrganizationUsersPagedList($filter, $user->organization_id, $user->id), 200);
    }

    public function downloadUserDisclosure()
    {
        $user = $this->securityHelper->getCurrentUser();
        if ($user->disclosure_url) {
            $pathToFile = public_path() . '/' . $user->disclosure_url;
            return response()->download($pathToFile);
        } else {
            return response()->json(['error' => 'You don\'t upload a disclosure'], 400);
        }
    }

    public function downloadOrganizationOrDefaultDisclosure()
    {
        $user = $this->securityHelper->getCurrentUser();
        if ($user->organization->disclosure_url) {
            $pathToFile = public_path() . '/' . $user->organization->disclosure_url;
        } else {
            $pathToFile = public_path() . '/doc/disclosure_template.doc';
        }
        return response()->download($pathToFile);
    }

    /**
     * Update Guest Info
     * @param Request $request
     * @return Response
     */
    public function UpdateGuest(Request $request)
    {
        $payload = JWTAuth::parseToken()->getPayload();
        $meetingID = $payload->get('meeting_id');
        $email = $payload->get('email');
        $name = $request->get('full_name');

        $result = $this->userService->ValidateUpdateGuest($meetingID, $email, $name);
        if ($result != null) {
            return response()->json($result, 400);
        }

        return response()->json(["message" => ['User updated successfully']], 200);
    }
    /**
     * Update Guest Info
     * @param Request $request
     * @return Response
     */
    public function GetGuestInfo(Request $request)
    {
        $payload = JWTAuth::parseToken()->getPayload();
        $meetingID = $payload->get('meeting_id');
        $email = $payload->get('email');

        $result = $this->userService->GetGuestByMeetingIdAndEmail($meetingID, $email);
        if ($result == null) {
            return response()->json([
                'error' => 'Guest haven\'t access to meeting',
                'error_code' => '1',
                'error_ar' => 'الضيف ليس لديه حق الوصول إلى الاجتماع'
            ], 400);
        }

        return response()->json($result, 200);
    }

    public function blockUnblockedUser(Request $request)
    {
        $data = $request->all();
        $currentUser = $this->securityHelper->getCurrentUser();
        $this->userService->blockUnblockedUser($data,$currentUser);
        return response()->json(['message' => 'User Update successfully.'], 200);
    }


    public function getBlockUserFeatureVariable()
    {
        $variableValue = config('customSetting.blockUserFeature');
        return response()->json(['blockUserFeature' => $variableValue], 200);
    }

    public function exportUnActiveUsers()
    {
        $user = $this->securityHelper->getCurrentUser();
        $lang = $user->language_id;
        $lang = $lang == config('languages.ar') ? 'ar' : 'en';
        return Excel::download(new ExportBlacklistedUsers($lang, $user->organization_id), 'all_blacklistedUsers.xlsx');
    }
}
