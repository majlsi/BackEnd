<?php
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // $this->call(StrorageRightsTableSeeder::class);
        $this->call(RightTypesTableSeeder::class);
        $this->call(ModulesTableSeeder::class);
        $this->call(RightsTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(RolesRightsTableSeeder::class);
        $this->call(ImageTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(TimeZoneTableSeeder::class);
        $this->call(MeetingTypesTableSeeder::class);
        $this->call(ReminderTableSeeder::class);
        $this->call(MeetingStatusTableSeeder::class);
        $this->call(AgendaPurposesTableSeeder::class);
        $this->call(VoteStatusTableSeeder::class);
        $this->call(MeetingAttendanceStatusTableSeeder::class);
        $this->call(VoteTypesTableSeeder::class);
        $this->call(OrganizationTypesTableSeeder::class);
        $this->call(LanguagesTableSeeder::class);
        $this->call(TaskStatusesTableSeeder::class);
        $this->call(configrationsTableSeeder::class);
        $this->call(SettingsTableSeeder::class);
        $this->call(OnlineMeetingAppsTableSeeder::class);
        $this->call(ChatGroupTypesTableSeeder::class);
        $this->call(AddTemplatesToOldMeetingsSeeder::class);
        $this->call(AddSettingsRightsToSecretarySeeder::class);
        $this->call(DocumentStatusesTableSeeder::class);
        $this->call(AddReviowsRoomForAllRolesSeeder::class);
        $this->call(NotificationOptionsTableSeeder::class);
        $this->call(UpdateSerialNumberForTasksSeeder::class);
        $this->call(DecisionTypesTableSeeder::class);
        $this->call(VoteResultStatusesSeeder::class);
        $this->call(updateLastVotesStatusesSeeder::class);
        $this->call(FileTypesTableSeeder::class);
        $this->call(VideoIconsTableSeeder::class);
        $this->call(CommitteeSeeder::class);
        $this->call(RequestTypesTableSeeder::class);
        $this->call(CommitteeTypesTableSeeder::class);
        $this->call(CommitteeStatusesTableSeeder::class);
        $this->call(EvaluationSeeder::class);
        $this->call(RecommendationStatusTableSeeder::class);
        $this->call(CommitteeNaturesTableSeeder::class);
    }
}
