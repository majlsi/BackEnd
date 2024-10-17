<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Imports\StakeholdersImport as ImportsStakeholdersImport;
use Helpers\SecurityHelper;
use Illuminate\Http\Request;
use Models\Stakeholder;
use Models\User;
use Services\StakeholderService;
use Services\UserService;
use Services\OrganizationService;
use Services\ChatService;
use Helpers\StakeholderHelper;
use Helpers\UserHelper;
use Helpers\SignatureHelper;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Excel;
use Exception;
use Helpers\ImageHelper;
use Services\RoleService;
use Services\CommitteeService;
use Services\CommitteeUserService;

class StakeholderController extends Controller
{
    private $securityHelper, $stakeholderService,
        $userService, $stakeholderHelper, $organizationService, $chatService, $userHelper, $signatureHelper, $roleService,
        $committeeService, $committeeUserService;
    private ImageHelper $imageHelper;

    public function __construct(
        SecurityHelper $securityHelper,
        StakeholderService $stakeholderService,
        UserService $userService,
        StakeholderHelper $stakeholderHelper,
        OrganizationService $organizationService,
        ChatService $chatService,
        UserHelper $userHelper,
        SignatureHelper $signatureHelper,
        RoleService $roleService,
        CommitteeService $committeeService,
        CommitteeUserService $committeeUserService,
        ImageHelper $imageHelper
    ) {
        $this->securityHelper = $securityHelper;
        $this->stakeholderService = $stakeholderService;
        $this->userService = $userService;
        $this->stakeholderHelper = $stakeholderHelper;
        $this->organizationService = $organizationService;
        $this->chatService = $chatService;
        $this->userHelper = $userHelper;
        $this->signatureHelper = $signatureHelper;
        $this->roleService = $roleService;
        $this->committeeService = $committeeService;
        $this->committeeUserService = $committeeUserService;
        $this->imageHelper = $imageHelper;
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        try {
            $this->stakeholderService->openTransaction();
            $data = $request->all();
            $user = $this->securityHelper->getCurrentUser();
            $data['organization_id'] = null;
            if ($user && $user->organization_id) {
                $userOrganizationData = $this->organizationService->getOrganizationData($user->organization_id);

                if ($userOrganizationData->stakeholders_number >= $userOrganizationData->stakeholders_count) {
                    return response()->json([
                        'error' => [
                            [
                                [
                                    'message' => Lang::get('validation.custom.stakeholders.count', [], 'en'),
                                    'message_ar' => Lang::get('validation.custom.stakeholders.count', [], 'ar')
                                ]
                            ]
                        ]
                    ], 400);
                }
                $data['organization_id'] = $user->organization_id;
            } else if (!$user || $user->role_id != config('roles.admin')) {
                return response()->json([
                    'error' => [
                        [
                            [
                                'message' => Lang::get('validation.custom.stakeholders.role', [], 'en'),
                                'message_ar' => Lang::get('validation.custom.stakeholders.role', [], 'ar')
                            ]
                        ]
                    ]
                ], 400);
            }
            // validate total share <= 100
            if (!$this->stakeholderService->validateTotalShare($data['share'], 0, $user->organization_id)) {
                return response()->json(
                    [
                        "error" => "Total share must be greater than or equal to 0 and less than or equal 100",
                        'error_ar' => 'مجموع نسب المساهمين يجب ان يكون أكبر من أو يساوي 0 وأصغر من أو يساوي 100'
                    ],
                    400
                );
            }

            if ($user->role_id === config('roles.admin')) {
                $profileImage = $this->imageHelper->profileImageForUsersCreatedByAdmin();
            } else {
                $profileImage = [];
            }
            $data['username'] = $data['email'];
            $data['oauth_provider'] = config('providers.custom');
            $data['is_verified'] = 1;
            // generate random password
            $data['password'] = $this->securityHelper->generateRandomPassword();
            $data['role_id'] = $this->roleService->getRoleByCode(config('roleCodes.stakeholder'))->id;
            $validator = Validator::make($data, User::rules('save'), User::messages('save'));
            if ($validator->fails()) {
                return response()->json(['error' => array_values($validator->errors()->toArray())], 400);
            }
            $stakeholderData = $this->stakeholderHelper->prepareStakeholderDataOnCreate($data);
            $validator = Validator::make($stakeholderData, Stakeholder::rules('save'), Stakeholder::messages('save'));
            if ($validator->fails()) {
                return response()->json(['error' => array_values($validator->errors()->toArray())], 400);
            }
            $data['is_stakeholder'] = true;
            $user = $this->userService->create(['user_data' => $data, 'profile_image' => $profileImage]);
            $stakeholderData['user_id'] = $user->id;

            $this->chatService->createChatUsers([$user]);
            $stakeholder = $this->stakeholderService->create(['stakeholder_data' => $stakeholderData]);
            $committee = $this->committeeService->getOrganizationCommitteeByCode($user->organization_id, config('committee.stakeholders'));
            $committeeUser = [
                'user_id' => $user->id,
                'committee_id' => $committee->id
            ];
            $this->committeeUserService->create($committeeUser);
            $this->stakeholderService->closeTransaction();
            return response()->json($stakeholder, 200);
        } catch (\Exception $e) {
            $this->stakeholderService->rollback();
            return response()->json([
                'error' => [
                    [
                        [
                            'message' => Lang::get('validation.custom.stakeholders.exception', [], 'en'),
                            'message_ar' => Lang::get('validation.custom.stakeholders.exception', [], 'ar')
                        ]
                    ]
                ]
            ], 400);
        }
    }

    public function getPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        $stakeholders = $this->stakeholderService->filteredStakeholders($filter, $user->role_id, $user->organization_id);
        $stakeholders->Results = $this->stakeholderHelper->mapListOfStakeholders($stakeholders->Results);
        return response()->json($stakeholders, 200);
    }

    public function activateDeactivateStakeholder(Request $request)
    {
        try {
            $this->stakeholderService->openTransaction();
            $data = $request->all();
            $currentUser = $this->securityHelper->getCurrentUser();
            if (!$currentUser) {
                return response()->json(['error' => 'You don\'t have permission.'], 400);
            }
            $validator = Validator::make($data, Stakeholder::rules('activate-deactivate'), Stakeholder::messages('activate-deactivate'));
            if ($validator->fails()) {
                return response()->json(['error' => array_values($validator->errors()->toArray())], 400);
            }
            $stakeholder = $this->stakeholderService->getById($data['stakesholder_id']);
            $activationUser = $this->userService->getById($stakeholder['user_id']);
            if ($activationUser->organization_id != $currentUser->organization_id) { // organization admin
                return response()->json(['error' => 'You don\'t have permission.'], 400);
            }
            $this->userService->activeDeactiveUser($stakeholder['user_id'], $data['is_active']);
            $this->stakeholderService->activateDeactivateStakeholder($data['stakesholder_id'], $data['is_active']);
            $this->stakeholderService->closeTransaction();
            return response()->json(['message' => 'Stakeholder Update successfully.'], 200);
        } catch (\Exception $e) {
            $this->stakeholderService->rollback();
            return response()->json(['error' => 'You can\'t Activate/Deactivate this stakeholder', 'error_ar' => 'لا يمكن تفعيل/إلغاء تفعيل هذا المساهم'], 400);
        }
    }

    public function destroy($id)
    {
        try {
            $user = $this->securityHelper->getCurrentUser();
            $stakeholder = $this->stakeholderService->getById($id);
            if (!$stakeholder) {
                return response()->json(['error' => 'Stakeholder not found', 'error_ar' => 'المساهم غير موجود'], 400);
            }
            $committee = $this->committeeService->getOrganizationCommitteeByCode($user->organization_id, config('committee.stakeholders'));

            $this->stakeholderService->openTransaction();
            $this->stakeholderService->delete($id);
            $this->committeeUserService->deleteByUserIdAndCommitteeId($stakeholder['user_id'], $committee->id);
            $this->userService->delete($stakeholder['user_id']);
            $this->stakeholderService->closeTransaction();
            return response()->json(['message' => 'Stakeholder deleted successfully'], 200);
        } catch (\Exception $e) {
            $this->stakeholderService->rollback();
            return response()->json(['error' => 'You can\'t delete this stakeholder', 'error_ar' => 'لا يمكن حذف هذا المساهم'], 400);
        }
    }

    public function show($id)
    {
        $user = $this->stakeholderService->getStakeholderById($id);
        return response()->json($user, 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $this->stakeholderService->openTransaction();
            $data = $request->all();
            $data['username'] = $data['email'];
            $validator = Validator::make($data, Stakeholder::rules('update', $id), Stakeholder::messages('update'));
            if ($validator->fails()) {
                return response()->json(["error" => array_values($validator->errors()->toArray())], 400);
            }
            $stakeholder = $this->stakeholderService->getById($id);
            $user = $this->userService->getById($stakeholder['user_id']);
            if (!$this->stakeholderService->validateTotalShare($data['share'], $stakeholder['share'], $user->organization_id)) {
                return response()->json(
                    [
                        "error" => "Total share must be greater than or equal to 0 and less than or equal 100",
                        'error_ar' => 'مجموع نسب المساهمين يجب ان يكون أكبر من أو يساوي 0 وأصغر من أو يساوي 100'
                    ],
                    400
                );
            }
            $user->username = $data['username'];
            $this->signatureHelper->updateUserByEmail($user->organization, $user->email, $data['email'], $data['user_phone']);
            $response = $this->chatService->updateChatUser($user);
            if ($response['is_success']) {
                $stakeholderUpdateData = $this->stakeholderHelper->prepareStakeholderDataOnUpdate($data);
                $this->stakeholderService->update($id, $stakeholderUpdateData);
                $userUpdateData = $this->userHelper->prepareUpdateStakeholderUser($data);
                $updated = $this->userService->update($user->id, $userUpdateData);
                if ($updated) {
                    $this->stakeholderService->closeTransaction();
                    return response()->json(["message" => ['Stakeholder updated successfully']], 200);
                }
            } else {
                return response()->json(['error' => 'You can\'t update this Stakeholder', 'error_ar' => 'لا يمكن تعديل بيانات هذا المساهم'], 400);
            }
        } catch (\Exception $e) {
            $this->stakeholderService->rollback();
            return response()->json(['error' => 'You can\'t update this Stakeholder', 'error_ar' => 'لا يمكن تعديل بيانات هذا المساهم'], 400);
        }
        $data = $request->all();
        $data['username'] = $data['email'];
        $validator = Validator::make($data, Stakeholder::rules('update', $id), Stakeholder::messages('update'));
        if ($validator->fails()) {
            return response()->json(["error" => array_values($validator->errors()->toArray())], 400);
        }
        $stakeholder = $this->stakeholderService->getById($id);
        $user = $this->userService->getById($stakeholder['user_id']);
        $user->username = $data['username'];
        $this->signatureHelper->updateUserByEmail($user->organization, $user->email, $data['email'], $data['user_phone']);
        $response = $this->chatService->updateChatUser($user);
        if ($response['is_success']) {
            $stakeholderUpdateData = $this->stakeholderHelper->prepareStakeholderDataOnUpdate($data);
            $this->stakeholderService->update($id, $stakeholderUpdateData);
            $userUpdateData = $this->userHelper->prepareUpdateStakeholderUser($data);
            $updated = $this->userService->update($user->id, $userUpdateData);
            if ($updated) {
                return response()->json(["message" => ['Stakeholder updated successfully']], 200);
            }
        } else {
            return response()->json(['error' => 'You can\'t update this Stakeholder', 'error_ar' => 'لا يمكن تعديل بيانات هذا المساهم'], 400);
        }
    }

    public function getTotalShares()
    {
        $user = $this->securityHelper->getCurrentUser();
        if ($user && $user->organization_id) {
            return response()->json(['total_shares' => $this->stakeholderService->getTotalShares($user->organization_id)->total_share], 200);
        }
    }

    public function downloadBlankExcelTemplate(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        $headers = array(
            'Content-Type: text/csv',
        );
        $lang = $user->language_id == config('languages.ar') ?  'ar' : 'en';
        $file_name = $lang . '.csv';
        $file = resource_path('stakeholders-templates' . DIRECTORY_SEPARATOR  . $file_name);
        return response()->download($file, $file_name, $headers);
    }

    public function validateStakeholdersFromExcel(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        $lang = $user->language_id;
        $stakeholders = Excel::toArray(new ImportsStakeholdersImport, request()->file('file'));
        // validate template
        $columns = head($stakeholders[0]);
        $lang = $lang == config('languages.ar') ? 'ar' : 'en';
        $error = $this->stakeholderService->validateColumns($columns, $lang);
        if (count($error) > 0) {
            return response()->json($error, 400);
        }
        array_shift($stakeholders[0]);
        // validate count
        if ($user && $user->organization_id) {
            $userOrganizationData = $this->organizationService->getOrganizationData($user->organization_id);

            if (
                $userOrganizationData->stakeholders_number >= $userOrganizationData->stakeholders_count ||
                ($userOrganizationData->stakeholders_number + count($stakeholders[0])) > $userOrganizationData->stakeholders_count
            ) {
                return response()->json([
                    'error' => Lang::get('validation.custom.stakeholders.limit', [], 'en'),
                    'error_ar' => Lang::get('validation.custom.stakeholders.limit', [], 'ar')
                ], 400);
            }
        }
        // validate data
        $data = $this->stakeholderService->validateStakeholdersFromExcel($stakeholders[0], $user->organization_id, $user->language_id);
        return response()->json(['stakeholders' => $data], 200);
    }

    public function bulkInsertStakeholdersFromExcel(Request $request)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $lang = $user->language_id;
        $roleId =
            $this->roleService->getRoleByCode(config('roleCodes.stakeholder'))->id;
        // 1- map data
        $data = $this->userHelper->mapDataFromExcel($data, $lang, $user->organization_id, $roleId);
        
        // validate count
        if ($user && $user->organization_id) {
            $userOrganizationData = $this->organizationService->getOrganizationData($user->organization_id);

            if (
                $userOrganizationData->stakeholders_number >= $userOrganizationData->stakeholders_count ||
                ($userOrganizationData->stakeholders_number + count($data)) > $userOrganizationData->stakeholders_count
            ) {
                return response()->json([
                    'error' => Lang::get('validation.custom.stakeholders.limit', [], 'en'),
                    'error_ar' => Lang::get('validation.custom.stakeholders.limit', [], 'ar')
                ], 400);
            }
        }
        // 2- validate data
        foreach ($data as $item) {
            $user = $item['user'];
            $stakeholder = $item['stakeholder'];
            $validator = Validator::make($user, User::rules('save'), User::messages('save'));
            if ($validator->fails()) {
                return response()->json(['error' => array_values($validator->errors()->toArray())], 400);
            }
            $validator = Validator::make($stakeholder, Stakeholder::rules('save'), Stakeholder::messages('save'));
            if ($validator->fails()) {
                return response()->json(['error' => array_values($validator->errors()->toArray())], 400);
            }
        }
        // 3- insert data
        try {
            $this->stakeholderService->openTransaction();
            $data = $this->userService->insertListFromExcel($data);
            // TODO: validate count 
            // $data['organization_id'] = null;
            // if ($user && $user->organization_id) {
            //     $userOrganizationData = $this->organizationService->getOrganizationData($user->organization_id);

            //     if ($userOrganizationData->stakeholders_number >= $userOrganizationData->stakeholders_count) {
            //         return response()->json([
            //             'error' => [
            //                 [
            //                     [
            //                         'message' => Lang::get('validation.custom.stakeholders.count', [], 'en'),
            //                         'message_ar' => Lang::get('validation.custom.stakeholders.count', [], 'ar')
            //                     ]
            //                 ]
            //             ]
            //         ], 400);
            //     }
            //     $data['organization_id'] = $user->organization_id;
            // }
            $user = $this->securityHelper->getCurrentUser();
            $committee = $this->committeeService->getOrganizationCommitteeByCode($user->organization_id, config('committee.stakeholders'));
            foreach ($data as &$item) {
                $user = $item['user'];
                $stakeholderData = $item['stakeholder'];
                $this->chatService->createChatUsers([$user]);
                $stakeholder = $this->stakeholderService->create(['stakeholder_data' => $stakeholderData]);
                $committeeUser = [
                    'user_id' => $user->id,
                    'committee_id' => $committee->id
                ];
                $this->committeeUserService->create($committeeUser);
                $item['stakeholder'] = $stakeholder;
            }
            $this->stakeholderService->closeTransaction();
            return response()->json([
                'message' => ['Stakeholders imported successfully'],
                'data' => $data
            ], 200);
        } catch (Exception $e) {
            $this->stakeholderService->rollback();
            return response()->json(['error' => 'Stakeholders could not imported successfully'], 400);
        }
    }
}
