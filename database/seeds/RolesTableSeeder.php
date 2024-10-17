<?php

use Illuminate\Database\Seeder;
use Models\Role;
use Helpers\RightHelper;

class RolesTableSeeder extends Seeder
{
    private $rightHelper;
    public function __construct(RightHelper $rightHelper)
    {
        $this->rightHelper = $rightHelper;
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            ['id' => 1, 'role_name' => 'Admin', 'role_name_ar' => 'ادمن', 'can_assign' => 1, 'role_code' => config('roleCodes.admin'), 'is_system' => 1, 'is_organization' => 0],
            ['id' => 2, 'role_name' => 'Secretary of the Board of Directors', 'role_name_ar' => 'امين مجلس الادراة', 'can_assign' => 1, 'role_code' => config('roleCodes.organizationAdmin'), 'is_system' => 1, 'is_organization' => 1, 'organization_id' => null]
        ];

        $organization_roles = [
            ['role_name' => 'Secretary of committees', 'role_name_ar' => 'امين لجان', 'organization_id' => null, 'is_meeting_role' => 1, 'can_assign' => 1, 'role_code' => config('roleCodes.secretary'), 'is_system' => 1, 'is_organization' => 1],
            ['role_name' => 'Board Members', 'role_name_ar' => 'الاعضاء', 'organization_id' => null, 'is_meeting_role' => 1, 'can_assign' => 1, 'role_code' => config('roleCodes.boardMembers'), 'is_system' => 1, 'is_organization' => 1],
            ['role_name' => 'Participant', 'role_name_ar' => 'مشارك', 'organization_id' => null, 'is_meeting_role' => 1, 'can_assign' => 1, 'role_code' => config('roleCodes.participant'), 'is_system' => 1, 'is_organization' => 1],
            ['role_name' => 'Stakeholder', 'role_name_ar' => 'مساهم', 'organization_id' => null, 'is_meeting_role' => 0, 'can_assign' => 0, 'role_code' => config('roleCodes.stakeholder'), 'is_system' => 1, 'is_organization' => 1],
            /* Add Guest Role */
            ['role_name' => 'Guest', 'role_name_ar' => 'ضيف', 'organization_id' => null, 'is_meeting_role' => 0, 'can_assign' => 0, 'role_code' => config('roleCodes.guest'), 'is_system' => 1, 'is_organization' => 1]
        ];
        foreach ($roles as $role) {
            # code...
            Role::updateOrCreate(['id' => $role['id']], $role);
        }


        foreach ($organization_roles as $role) {
            $createdRole =  Role::updateOrCreate(['role_code' => $role['role_code'], 'organization_id' => null], $role);

            $count = $createdRole->rights()->count();
            if ($count == 0) {
                if ($createdRole->role_code == config('roleCodes.participant')) {
                    $rights = $this->rightHelper->prepareRightDataForMembers();
                    $createdRole->rights()->createMany($rights);
                }
                if ($createdRole->role_code == config('roleCodes.secretary')) {
                    $sec_rights = $this->rightHelper->prepareRightDataForSecretary();
                    $createdRole->rights()->createMany($sec_rights);
                }
                if ($createdRole->role_code == config('roleCodes.boardMembers')) {
                    $boardMember_rights = $this->rightHelper->prepareRightDataForBoardMembers();
                    $createdRole->rights()->createMany($boardMember_rights);
                }
                /* Add Guest Rights To Guest Role */
                if ($createdRole->role_code == config('roleCodes.guest')) {
                    $guestRights = $this->rightHelper->prepareRightDataForGuest();
                    $createdRole->rights()->createMany($guestRights);
                }
            }
        }



        DB::statement('
            UPDATE
                users
            JOIN roles AS original_roles
            ON
                users.role_id = original_roles.id
            JOIN roles AS system_roles
            ON
                original_roles.role_code = system_roles.role_code
            SET
                users.role_id = system_roles.id
            WHERE
                system_roles.organization_id IS NULL
        ');

        DB::statement('
            UPDATE
                meeting_participants
            JOIN roles AS original_roles
            ON
                meeting_participants.meeting_role_id = original_roles.id
            JOIN roles AS system_roles
            ON
                original_roles.role_code = system_roles.role_code
            SET
                meeting_participants.meeting_role_id = system_roles.id
            WHERE
                system_roles.organization_id IS NULL
            ');


        DB::statement('
        DELETE
            role_rights
        FROM
            role_rights
        JOIN roles AS original_roles
        ON
            original_roles.id = role_rights.role_id
        JOIN roles AS system_roles
        ON
            original_roles.role_code = system_roles.role_code
        WHERE
            system_roles.organization_id IS NULL AND original_roles.organization_id IS NOT NULL');
        $roleCodes = array_column($organization_roles, 'role_code');
        $parsed_codes = json_encode($roleCodes);
        $formated_codes = str_replace('[', "(", $parsed_codes);
        $formated_codes = str_replace(']', ")", $formated_codes);
        DB::statement("
        DELETE
        FROM
            roles
        WHERE
            roles.organization_id IS NOT NULL AND roles.role_code  in " . $formated_codes);
    }
}
