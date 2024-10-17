<?php

use Illuminate\Database\Seeder;
use Models\Meeting;
use Models\Organization;

class AddTemplatesToOldMeetingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $meetingWithNoMomTemplate = Meeting::whereNull('meeting_mom_template_id')->get();
        $organizations = Organization::get();
        foreach ($organizations as $key => $organization) {
            if (count($organization->momTemplates) == 0) {
                
                $momTemplateData = [
                    'template_name_en' => config('momTemplate.template_name_en'),
                    'template_name_ar' => config('momTemplate.template_name_ar'),
                    'introduction_template_ar' => config('momTemplate.introduction_template_ar'),
                    'introduction_template_en' => config('momTemplate.introduction_template_en'),
                    'member_list_introduction_template_ar' => config('momTemplate.member_list_introduction_template_ar'),
                    'member_list_introduction_template_en' => config('momTemplate.member_list_introduction_template_en'),
                ];
                $momTemplate = $organization->momTemplates()->create($momTemplateData);

            }

        }
        foreach ($meetingWithNoMomTemplate as $key => $meeting) {
            if($meeting->organization){
                $meeting->meeting_mom_template_id = $meeting->organization->momTemplates[0]->id;
                $meeting->save();
            }
           
        }

    }
}
