<?php

use Helpers\CommitteeHelper;
use Helpers\MeetingTypeHelper;
use Illuminate\Database\Seeder;
// use Models\Organization;
// use Models\RoleRight;

class InsertNewRightsTableSeeder extends Seeder
{

    private $meetingTypeHelper;
    private $committeeHelper;

    public function __construct(
        MeetingTypeHelper $meetingTypeHelper, CommitteeHelper $committeeHelper) {

        $this->meetingTypeHelper = $meetingTypeHelper;

        $this->committeeHelper = $committeeHelper;
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $organizations =  Organization::all();
        // foreach($organizations as $organization){
        //     $organizationRoles = $organization->roles;
        //     foreach($organizationRoles as $organizationRole){
        //         RoleRight::insert(['role_id' => $organizationRole->id,'right_id' => 48]);
        //     }
        // }
        // RoleRight::insert(['role_id' => 2,'right_id' => 48]);

        // $organizations = Organization::all();
        // foreach ($organizations as $organization) {
        //     $data = [];
        //     $meetingTypes = [];
        //     $meetingTypes = $this->meetingTypeHelper->prepareMeetingTypesForOrganizationAdmin($organization->id);
        //     $organization->meetingTypes()->createMany($meetingTypes);
        //     $data = $this->committeeHelper->prepareCommiteesOrganizationAdmin($organization->id);
        //     $organization->committees()->create($data);
        // }

        // DB::statement("update meetings set meeting_type_id = (select id from meeting_types where meeting_types.organization_id = meetings.organization_id and meeting_type_code is null)");
        //DB::statement("update organizations set organization_type_id = 1 , api_url ='http://www.mjlsi.com/app/BackEnd/public' ,front_url ='http://www.mjlsi.com/app',redis_url ='3.130.57.105'");
       // DB::statement("update users set language_id = 1");
       //DB::statement("update organizations set signature_username='mjlsi@gmail.com',signature_password='123',signature_url='https://www.mjlsi.com/ds-api/api/'");
       
    //    DB::statement("UPDATE roles SET can_assign=1 where role_code='participant_5'");
    //       DB::statement("DELETE FROM role_rights WHERE right_id IN (33,34,35,47,45,44)");
    //       DB::statement(" DELETE FROM `rights` WHERE id IN (33,34,35,47,45,44)");
    //     DB::statement("
    //     INSERT INTO role_rights (
    //         role_id, 
    //         right_id
    //     )
    //     SELECT 
    //        role_id, 
    //        64
    //     FROM 
    //         role_rights
    //     WHERE 
    //      right_id =24 and deleted_at is null
        //  ");
        // //Dlete duplicate with 2 ( run for 65 ,66,67)

        // DB::statement("
        // INSERT INTO role_rights ( role_id, right_id ) SELECT DISTINCT role_id, 68 FROM role_rights WHERE deleted_at is null
        // ");

    }
}
