<?php

namespace Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Blade;

class MomHelper
{

    public function getMomOfMeeting($meetingAllData,$languageId)
    {
        $meetingMom = [];
        if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.swcc')) {
            $view_name = $languageId == config('languages.ar') ? 'mom.swcc-meeting-mom-template-ar' : 'mom.swcc-meeting-mom-template-en';
        } else {
            $view_name = $languageId == config('languages.ar') ? 'mom.meeting-mom-template-ar' : 'mom.meeting-mom-template-en';
        }
        $meetingAllData['meeting_mom_template_logo'] = env('APP_URL') .'/' . $meetingAllData['meeting_mom_template_logo'];
        $meetingMom['meeting_id'] = $meetingAllData['id'];
        $meetingAllData['meeting_title_ar'] = $meetingAllData['meeting_title_ar']? $meetingAllData['meeting_title_ar'] : $meetingAllData['meeting_title_en'];
        $meetingAllData['meeting_title_en'] = $meetingAllData['meeting_title_en']? $meetingAllData['meeting_title_en'] : $meetingAllData['meeting_title_ar'];
        $meetingAllData['committee_name_ar'] = $meetingAllData['committee_name_ar']? $meetingAllData['committee_name_ar'] : $meetingAllData['committee_name_en'];
        $meetingAllData['committee_name_en'] = $meetingAllData['committee_name_en']? $meetingAllData['committee_name_en'] : $meetingAllData['committee_name_ar'];
        $meetingAllData['meeting_venue_ar'] = $meetingAllData['meeting_venue_ar']? $meetingAllData['meeting_venue_ar'] : $meetingAllData['meeting_venue_en'];
        $meetingAllData['meeting_venue_en'] = $meetingAllData['meeting_venue_en']? $meetingAllData['meeting_venue_en'] : $meetingAllData['meeting_venue_ar'];
        $meetingAllData['introduction_template_ar'] = Blade::render($meetingAllData["introduction_template_ar"], ['data'=>$meetingAllData]);
        $meetingAllData['introduction_template_en'] = Blade::render($meetingAllData["introduction_template_en"], ['data'=>$meetingAllData]);
        $meetingAllData['canSignParticipants'] = collect($meetingAllData['meeting_participants'])->where('can_sign', 1);
        $meetingAllData['meeting_schedule_date_from'] = Carbon::parse($meetingAllData['meeting_schedule_from'])->format('Y-m-d');
        $meetingAllData['meeting_schedule_time_from'] = Carbon::parse($meetingAllData['meeting_schedule_from'])->format('g:i A');
        $meetingMom['mom_summary'] = view($view_name, ['data' => $meetingAllData])->render();
        $meetingMom['mom_title_ar'] = $meetingAllData['meeting_title_ar'];
        $meetingMom['mom_title_en'] = $meetingAllData['meeting_title_en'];
        $meetingMom['language_id'] = $languageId;

        return $meetingMom;
    }

    public function renderMomTemplateData($meetingAllData,$momTemplate)
    {
        $meetingAllData['show_mom_header'] = $momTemplate['show_mom_header'];
        $meetingAllData['show_agenda_list'] = $momTemplate['show_agenda_list'];
        $meetingAllData['show_timer'] = $momTemplate['show_timer'];
        $meetingAllData['show_presenters'] = $momTemplate['show_presenters'];
        $meetingAllData['show_purpose'] = $momTemplate['show_purpose'];
        $meetingAllData['show_participant_nickname'] = $momTemplate['show_participant_nickname'];
        $meetingAllData['show_participant_job'] = $momTemplate['show_participant_job'];
        $meetingAllData['show_participant_title'] = $momTemplate['show_participant_title'];
        $meetingAllData['show_conclusion'] = $momTemplate['show_conclusion'];
        $meetingAllData['show_vote_results'] = $momTemplate['show_vote_results'];
        $meetingAllData['show_vote_status'] = $momTemplate['show_vote_status'];
        $meetingAllData['conclusion_template_en'] = $momTemplate['conclusion_template_en'];
        $meetingAllData['conclusion_template_ar'] = $momTemplate['conclusion_template_ar'];
        $meetingAllData['member_list_introduction_template_en'] = $momTemplate['member_list_introduction_template_en'];
        $meetingAllData['member_list_introduction_template_ar'] = $momTemplate['member_list_introduction_template_ar'];
        $meetingAllData['introduction_template_en'] = $momTemplate['introduction_template_en'];
        $meetingAllData['introduction_template_ar'] = $momTemplate['introduction_template_ar'];
        $meetingAllData['meeting_mom_template_logo'] = $momTemplate['meeting_mom_template_logo'];
        
        return $meetingAllData;
    }
}