<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Helpers\SecurityHelper;
use Illuminate\Http\Request;
use Models\Role;
use Services\ModuleService;
use Services\RoleRightService;
use Services\RoleService;
use Services\UserService;
use Services\OrganizationService;
use Validator;

class RoleController extends Controller
{


    private $roleService;
    private $userService;
    private $roleRightService;
    private $securityHelper;
    private $moduleService;
    private $organizationService;

    public function __construct(
        RoleService $roleService,
        UserService $userService,
        RoleRightService $roleRightService,
        SecurityHelper $securityHelper,
        ModuleService $moduleService,
        OrganizationService $organizationService
    ) {
        $this->roleService = $roleService;
        $this->userService = $userService;
        $this->roleRightService = $roleRightService;
        $this->securityHelper = $securityHelper;
        $this->moduleService = $moduleService;
        $this->organizationService = $organizationService;
    }

    public function index()
    {
        return response()->json($this->roleService->getAll(), 200);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $user = $this->securityHelper->getCurrentUser();

        $role = $this->roleService->getById($id)->load('rights');
        $role_read_only = $this->roleService->is_read_only($id, $user->organization_id);
        $role["is_read_only"] = $role_read_only["is_read_only"];
        return response()->json($role, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, Role::rules('save'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        $user = $this->securityHelper->getCurrentUser();
        if ($user->role_id !== config('roles.admin') && $user->organization_id) {
            $data['organization_id'] =  $user->organization_id;
        }
        $data['can_assign'] =  1;
        $role = $this->roleService->create($data);
        return response()->json($role, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $user = $this->securityHelper->getCurrentUser();
        $data = $request->all();
        $role_read_only = $this->roleService->is_read_only($id, $user->organization_id);
        $validator = Validator::make($data, Role::rules('update', $id));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        if ($role_read_only["is_read_only"]) {
            return response()->json(['error' => "Can't update this role!", 'error_ar' => 'لا يمكن تعديل هذا الدور'], 400);
        }
        $role = $this->roleService->update($id, $data);
        return response()->json(['message' => ['Item Updated successfully.']], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        try {
            $role = $this->roleService->getById($id);
            if ($role) {
                $user = $this->securityHelper->getCurrentUser();

                $role_read_only = $this->roleService->is_read_only($id, $user->organization_id);
                if ($role_read_only["is_read_only"]) {
                    return response()->json(['error' => "Can't delete this role!", 'error_ar' => 'لا يمكن حذف هذا الدور'], 400);
                }
                $role->rights()->delete();
                $this->roleService->delete($id);
                return response()->json(['message' => 'Role is deleted successfully'], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => "Can't delete this role!, it has related items to it.", 'error_ar' => 'لا يمكن حذف هذا الدور, يوجد عناصر مرتبطة بها'], 400);
        }
    }

    public function getPagedList(Request $request)
    {
        $data = (object)$request->all();
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->roleService->filterRoles($data, $user->role_id, $user->organization_id), 200);
    }

    public function getRoleRights()
    {
        $user = $this->securityHelper->getCurrentUser();
        $roleId = $user->role_id;
        $roleRights = $this->moduleService->getRoleRights($roleId);
        if ($user->organization_id) {
            $organization = $this->organizationService->getById($user->organization_id);
            if (!$organization->is_stakeholder_enabled) {
                // remove stakeholder from the list if it is not enabled for the organization
                if (($key = array_search(config('modules.members'), array_column($roleRights->toArray(), 'id'))) != false) {
                    if (($key2 = array_search(config('rights.shareholdersFilter'), array_column($roleRights->toArray()[$key]['submenu'], 'id'))) != false) {
                        unset($roleRights[$key]['submenu'][$key2]);
                    }
                }
            }
        }
        return response()->json($roleRights, 200);
    }

    public function CanAccess($rightId)
    {
        $user = $this->securityHelper->getCurrentUser();
        // allow meeting guests and normal users
        $roleId = $user->role_id ?? $user->meeting_role_id;
        $canAccess = $this->roleRightService->canAccess($roleId, $rightId);
        if (count($canAccess) > 0) {
            return response()->json(['canAccess' => 1], 200);
        } else {
            return response()->json(['canAccess' => 0], 200);
        }
    }

    public function getModulesRights()
    {
        $user = $this->securityHelper->getCurrentUser();
        if ($user->role_id  == config('roles.admin')) {
            $rightTypeId = config('rightTypes.forAdmin');
        } else if ($user->organization_id) {
            $rightTypeId = config('rightTypes.forOrganizationAdmin');
        }
        $modules = $this->moduleService->getAvailableAllRights($rightTypeId);
        return response()->json($modules, 200);
    }

    public function getRolesWithoutAdmin()
    {
        $user = $this->securityHelper->getCurrentUser();
        if ($user->role_id !== config('roles.admin') && $user->organization_id) {
            return response()->json($this->roleService->getOrganizationRoles($user->organization_id), 200);
        } else {
            return response()->json($this->roleService->getAdminRole(), 200);
        }
    }

    public function getRoleAccessRights()
    {
        $user = $this->securityHelper->getCurrentUser();
        $roleId = $user->role_id;
        $roleRights = $this->roleService->getRoleAccessRights($roleId);

        return response()->json($roleRights, 200);
    }

    public function getAllRoleAccessRights()
    {
        $user = $this->securityHelper->getCurrentUser();
        $roleId = $user->role_id ?? $user->meeting_role_id;
        $roleRights = $this->roleService->getAllRoleAccessRights($roleId);

        return response()->json($roleRights, 200);
    }

    public function getConversationRight()
    {
        $user = $this->securityHelper->getCurrentUser();
        $conversationRight = [];
        if ($user->role_id != config('roles.admin')) {
            $conversationRight = $this->moduleService->getconversationRight();
        }
        return response()->json($conversationRight, 200);
    }
}
