<?php

use Illuminate\Database\Seeder;

class DeleteOrganizationDataTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("SET FOREIGN_KEY_CHECKS=0");

      DB::statement("DELETE FROM role_rights 
      where role_id NOT IN (1,2);");
      DB::statement("DELETE 
      FROM time_zones 
      WHERE organization_id IS NOT NULL;");
      DB::statement("DELETE FROM agenda_presenters;");
      DB::statement("TRUNCATE TABLE agenda_presenters;");
      DB::statement("DELETE FROM attachments;");
      DB::statement("TRUNCATE TABLE attachments;");
      DB::statement("DELETE FROM committee_users;");
      DB::statement("TRUNCATE TABLE committee_users;");
      DB::statement("DELETE FROM meeting_organisers;");
      DB::statement("TRUNCATE TABLE meeting_organisers;");
      DB::statement("DELETE FROM meeting_participants;");
      DB::statement("TRUNCATE TABLE meeting_participants;");
      DB::statement("DELETE FROM meeting_reminders;");
      DB::statement("TRUNCATE TABLE meeting_reminders;");
      DB::statement("DELETE FROM meeting_status_history;");

      DB::statement("TRUNCATE TABLE meeting_status_history;");
      DB::statement("DELETE FROM vote_results;");
      DB::statement("TRUNCATE TABLE vote_results;");
      DB::statement("DELETE FROM  user_comments;");
      DB::statement("TRUNCATE TABLE user_comments;");

      DB::statement("DELETE FROM votes;");
      DB::statement("TRUNCATE TABLE votes;");
      DB::statement("DELETE FROM moms;");
      DB::statement("TRUNCATE TABLE moms;");
      DB::statement("DELETE FROM  meeting_agendas;");
      DB::statement("TRUNCATE TABLE meeting_agendas;");

      DB::statement("DELETE FROM meetings;");
      DB::statement("TRUNCATE TABLE meetings;");
      DB::statement("DELETE FROM committees;");
      DB::statement("TRUNCATE TABLE committees;");
      DB::statement("DELETE FROM  proposals;");
      DB::statement("TRUNCATE TABLE proposals;");

      DB::statement("DELETE FROM organizations;");
      DB::statement("TRUNCATE TABLE organizations;");
      DB::statement("DELETE 
      FROM users 
      WHERE organization_id IS NOT NULL;");
      DB::statement("DELETE 
      FROM roles 
      WHERE organization_id IS NOT NULL;");
      DB::statement("DELETE FROM  images;");
      DB::statement("TRUNCATE TABLE images;");


      DB::statement("DELETE 
      FROM meeting_types 
      WHERE organization_id IS NOT NULL;");
      DB::statement("SET FOREIGN_KEY_CHECKS=1;");

        
    }
}        