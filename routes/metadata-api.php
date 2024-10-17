<?php

use App\Http\Controllers\CommitteeUserController;
use App\Http\Controllers\CommitteeRecommendationController;

Route::group(
    ['prefix' => 'v1', 'middleware' => ['jwt.customAuth', 'userActivated', 'userDeleted', 'organizationExpirationLicense', 'organizationAccess', 'organizationCompletedProfile', 'throttle:1000000000,1', 'checkNumberOfFailedLoginAttempt']],
    function () {
        //Roles & access
        Route::post('roles/filtered-list', 'RoleController@getPagedList')->middleware('checkAuthorization:' . config('rights.rolesFilter'));
        Route::post('roles', 'RoleController@store')->middleware('checkAuthorization:' . config('rights.addRole'));
        Route::get('roles/{role_id}', 'RoleController@show');
        Route::put('roles/{role_id}', 'RoleController@update')->middleware('checkAuthorization:' . config('rights.editRole'));
        Route::delete('roles/{role_id}', 'RoleController@destroy')->middleware('checkAuthorization:' . config('rights.deleteRole'));

        Route::group(['prefix' => 'admin'], function () {
            //agenda templates
            Route::post('agenda-templates/filtered-list', 'AgendaTemplateController@getPagedList')->middleware('checkAuthorization:' . config('rights.agendaTemplatesFilter'));
            Route::post('agenda-templates', 'AgendaTemplateController@store')->middleware('checkAuthorization:' . config('rights.addAgendaTemplate'));
            Route::get('agenda-templates/{agenda_template_id}', 'AgendaTemplateController@show');
            Route::put('agenda-templates/{agenda_template_id}', 'AgendaTemplateController@update')->middleware('checkAuthorization:' . config('rights.editAgendaTemplate'));
            Route::delete('agenda-templates/{agenda_template_id}', 'AgendaTemplateController@destroy')->middleware('checkAuthorization:' . config('rights.deleteAgendaTemplate'));

            //html mom templates
            Route::post('html-mom-templates/filtered-list', 'HtmlMomTemplateController@getPagedList')->middleware('checkAuthorization:' . config('rights.htmlMomTemplatesFilter'));
            Route::post('html-mom-templates', 'HtmlMomTemplateController@store')->middleware('checkAuthorization:' . config('rights.addHtmlMomTemplate'));
            Route::get('html-mom-templates/{html_mom_template_id}', 'HtmlMomTemplateController@show');
            Route::put('html-mom-templates/{html_mom_template_id}', 'HtmlMomTemplateController@update')->middleware('checkAuthorization:' . config('rights.editHtmlMomTemplate'));
            Route::delete('html-mom-templates/{html_mom_template_id}', 'HtmlMomTemplateController@destroy')->middleware('checkAuthorization:' . config('rights.deleteHtmlMomTemplate'));

            //Organization job title
            Route::post('job-titles', 'JobTitleController@store')->middleware('checkAuthorization:' . config('rights.addJobTitle'));
            Route::get('job-titles/{job_title_id}', 'JobTitleController@show');
            Route::put('job-titles/{job_title_id}', 'JobTitleController@update')->middleware('checkAuthorization:' . config('rights.editJobTitle'));
            Route::delete('job-titles/{job_title_id}', 'JobTitleController@destroy')->middleware('checkAuthorization:' . config('rights.deleteJobTitle'));
            Route::post('job-titles/filtered-list', 'JobTitleController@getPagedList')->middleware('checkAuthorization:' . config('rights.jobTitlesFilter'));

            //meeting types
            Route::post('meeting-types', 'MeetingTypeController@store')->middleware('checkAuthorization:' . config('rights.addMeetingType'));
            Route::get('meeting-types/{meeting_type_id}', 'MeetingTypeController@show');
            Route::put('meeting-types/{meeting_type_id}', 'MeetingTypeController@update')->middleware('checkAuthorization:' . config('rights.editMeetingType'));
            Route::delete('meeting-types/{meeting_type_id}', 'MeetingTypeController@destroy')->middleware('checkAuthorization:' . config('rights.deleteMeetingType'));
            Route::post('meeting-types/filtered-list', 'MeetingTypeController@getPagedList')->middleware('checkAuthorization:' . config('rights.meetingTypesFilter'));
            Route::post('organizations/meeting-types', 'OrganizationController@getOrganizationMeetingTypes');

            //mom templates
            Route::post('mom-templates/filtered-list', 'MomTemplateController@getPagedList')->middleware('checkAuthorization:' . config('rights.momTemplatesFilter'));
            Route::post('mom-templates', 'MomTemplateController@store')->middleware('checkAuthorization:' . config('rights.addMomTemplate'));
            Route::get('mom-templates/{mom_template_id}', 'MomTemplateController@show');
            Route::put('mom-templates/{mom_template_id}', 'MomTemplateController@update')->middleware('checkAuthorization:' . config('rights.editMomTemplate'));
            Route::delete('mom-templates/{mom_template_id}', 'MomTemplateController@destroy')->middleware('checkAuthorization:' . config('rights.deleteMomTemplate'));

            //Organization nickname
            Route::post('nicknames', 'NicknameController@store')->middleware('checkAuthorization:' . config('rights.addNickname'));
            Route::get('nicknames/{nickname_id}', 'NicknameController@show');
            Route::put('nicknames/{nickname_id}', 'NicknameController@update')->middleware('checkAuthorization:' . config('rights.editNickname'));
            Route::delete('nicknames/{nickname_id}', 'NicknameController@destroy')->middleware('checkAuthorization:' . config('rights.deleteNickname'));
            Route::post('nicknames/filtered-list', 'NicknameController@getPagedList')->middleware('checkAuthorization:' . config('rights.nicknamesFilter'));

            //Organization user title
            Route::post('user-titles', 'UserTitleController@store')->middleware('checkAuthorization:' . config('rights.addUserTitle'));
            Route::get('user-titles/{user_title_id}', 'UserTitleController@show');
            Route::put('user-titles/{user_title_id}', 'UserTitleController@update')->middleware('checkAuthorization:' . config('rights.editUserTitle'));
            Route::delete('user-titles/{user_title_id}', 'UserTitleController@destroy')->middleware('checkAuthorization:' . config('rights.deleteUserTitle'));
            Route::post('user-titles/filtered-list', 'UserTitleController@getPagedList')->middleware('checkAuthorization:' . config('rights.userTitlesFilter'));

            //time zones
            Route::post('time-zones', 'TimeZoneController@store')->middleware('checkAuthorization:' . config('rights.addTimeZone'));
            Route::put('time-zones/{time_zone_id}', 'TimeZoneController@update')->middleware('checkAuthorization:' . config('rights.editTimeZone'));
            Route::get('time-zones/{time_zone_id}', 'TimeZoneController@show');
            Route::delete('time-zones/{time_zone_id}', 'TimeZoneController@destroy')->middleware('checkAuthorization:' . config('rights.deleteTimeZone'));
            Route::post('time-zones/filtered-list', 'TimeZoneController@getPagedList')->middleware('checkAuthorization:' . config('rights.timeZonesFilter'));

            //Faqs
            Route::post('faqs', 'FaqController@store')->middleware('checkAuthorization:' . config('rights.addFaq'));
            Route::put('faqs/{faq_id}', 'FaqController@update')->middleware('checkAuthorization:' . config('rights.editFaq'));
            Route::get('faqs/{faq_id}', 'FaqController@show');
            Route::delete('faqs/{faq_id}', 'FaqController@destroy')->middleware('checkAuthorization:' . config('rights.deleteFaq'));
            Route::post('faqs/filtered-list', 'FaqController@getPagedList')->middleware('checkAuthorization:' . config('rights.faqsFilter'));
            Route::get('faqs/section/tree', 'FaqController@getSectionQuestionsTree')->middleware('checkAuthorization:' . config('rights.faqTree'));
            //Faq sections
            Route::post('faq-sections', 'FaqSectionController@store')->middleware('checkAuthorization:' . config('rights.addFaqSection'));
            Route::put('faq-sections/{faq_section_id}', 'FaqSectionController@update')->middleware('checkAuthorization:' . config('rights.editFaqSection'));
            Route::get('faq-sections/{faq_section_id}', 'FaqSectionController@show');
            Route::delete('faq-sections/{faq_section_id}', 'FaqSectionController@destroy')->middleware('checkAuthorization:' . config('rights.deleteFaqSection'));
            Route::post('faq-sections/filtered-list', 'FaqSectionController@getPagedList')->middleware('checkAuthorization:' . config('rights.faqSectionFilter'));
            Route::get('faq-sections/parents/list', 'FaqSectionController@getParentSections');
            Route::get('faq-sections/leafs/list', 'FaqSectionController@getLeafSections');

            // Videos guide
            Route::post('videos-guide', 'GuideVideoController@store')->middleware('checkAuthorization:' . config('rights.addVideoGuide'));
            Route::put('videos-guide/{video_guide_id}', 'GuideVideoController@update')->middleware('checkAuthorization:' . config('rights.editVideoGuide'));
            Route::get('videos-guide/{video_guide_id}', 'GuideVideoController@show');
            Route::delete('videos-guide/{video_guide_id}', 'GuideVideoController@destroy')->middleware('checkAuthorization:' . config('rights.deleteVideoGuide'));
            Route::post('videos-guide/filtered-list', 'GuideVideoController@getPagedList')->middleware('checkAuthorization:' . config('rights.videoGuideFilter'));
            Route::get('video-icons', 'VideoIconController@index');

            //user online configuratons
            Route::post('user-online-configurations', 'UserOnlineConfigurationController@store')->middleware('checkAuthorization:' . config('rights.addOnlineConfiguration'));
            Route::get('user-online-configurations/{user_online_configuration_id}', 'UserOnlineConfigurationController@show');
            Route::put('user-online-configurations/{user_online_configuration_id}', 'UserOnlineConfigurationController@update')->middleware('checkAuthorization:' . config('rights.editOnlineConfiguration'));
            Route::delete('user-online-configurations/{user_online_configuration_id}', 'UserOnlineConfigurationController@destroy')->middleware('checkAuthorization:' . config('rights.deleteOnlineConfiguration'));
            Route::post('user-online-configurations/filtered-list', 'UserOnlineConfigurationController@getPagedList')->middleware('checkAuthorization:' . config('rights.onlineConfigurationsFilter'));

            // Microsoft Teams documentation
            Route::get('microsoft-teams-documentation/{lang}', 'MicrosoftTeamConfigurationController@downloadMicrosoftTeamsDocumentationPdf');

            // decision types
            Route::post('decision-types', 'DecisionTypeController@store')->middleware('checkAuthorization:' . config('rights.addDecisionType'));
            Route::get('decision-types/{decision_type_id}', 'DecisionTypeController@show');
            Route::put('decision-types/{decision_type_id}', 'DecisionTypeController@update')->middleware('checkAuthorization:' . config('rights.editDecisionType'));
            Route::delete('decision-types/{decision_type_id}', 'DecisionTypeController@destroy')->middleware('checkAuthorization:' . config('rights.deleteDecisionType'));
            Route::post('decision-types/filtered-list', 'DecisionTypeController@getPagedList')->middleware('checkAuthorization:' . config('rights.decisionTypesFilter'));

            Route::get('configrations/{id}', 'ConfigrationController@getFirstConfigration')->middleware('checkAuthorization:' . config('rights.editConfigration'));
            Route::put('configrations/{id}', 'ConfigrationController@update')->middleware('checkAuthorization:' . config('rights.editConfigration'));

            //Committees
            Route::get('committees/change-committee-status-job', 'CommitteeController@changeCommitteeStatusJob');
            Route::post('committees/filtered-list', 'CommitteeController@getPagedList')->middleware('checkAuthorization:' . config('rights.committeesFilter'));
            Route::post('my-committees/filtered-list', 'CommitteeController@getMyCommitteesPagedList')->middleware('checkAuthorization:' . config('rights.myCommittees'));
            Route::post('committees/list', 'CommitteeController@getList')->middleware('checkAuthorization:' . config('rights.committeesFilter'));
            Route::post('committees', 'CommitteeController@store')->middleware('checkAuthorization:' . config('rights.addCommittee'));
            Route::get('committees/notify-head-members-committee-job', 'CommitteeController@notifyHeadMembersCommitteeJob');
            Route::get('committees/{committee_id}/can-request-delete-member', 'CommitteeController@canRequestDeleteUser')->middleware('checkAuthorization:' . config('rights.editCommittee'));
            Route::get('committees/{committee_id}/reminder-committee-members', 'CommitteeController@reminderCommitteeMembers')->middleware('checkAuthorization:' . config('rights.reminderFinalCommitteeWork'));
            Route::post('committees/{committee_id}/recommendations', 'CommitteeController@addCommitteeRecommendations')->middleware('checkAuthorization:' . config('rights.editCommittee'));
            Route::post('committees/{committee_id}/add-final-output-file', 'CommitteeController@addFinalOutputFileToCommittee')->middleware('checkAuthorization:' . config('rights.editCommittee'));
            Route::get('committees/{committee_id}', 'CommitteeController@show');
            Route::put('committees/{committee_id}', 'CommitteeController@update')->middleware('checkAuthorization:' . config('rights.editCommittee'));
            Route::delete('committees/{committee_id}', 'CommitteeController@destroy')->middleware('checkAuthorization:' . config('rights.deleteCommittee'));
            Route::post('committees/standing-committees/filtered-list', 'CommitteeController@getStandingcommitteesPagedList')->middleware('checkAuthorization:' . config('rights.permanentCommittee'));
            Route::post('committees/temporary-committees/filtered-list', 'CommitteeController@getTemporaryCommitteesPagedList')->middleware('checkAuthorization:' . config('rights.temporaryCommittee'));
            Route::put('committees/{committee_id}/update-committee-recommendations-status', 'CommitteeController@updateCommitteeRecommendationsStatus')->middleware('checkAuthorization:' . config('rights.editCommittee'));
            Route::post('committee-users/{id}/add-disclosure-to-committee-user', [CommitteeUserController::class, 'addDisclosureToCommitteeUser'])->middleware('checkAuthorization:' . config('rights.editCommittee'));
            Route::put('/committee-users/{id}', [CommitteeUserController::class, 'putCommitteeUserEvaluation']);

            // Committee Final Output
            Route::get('committee-final-output/{id}/download-final-output', 'CommitteeFinalOutputController@downloadFinalOutput')->middleware('checkAuthorization:' . config('rights.editCommittee'));

            // Recommendations
            Route::put('recommendations/{recommend_id}', [CommitteeRecommendationController::class, 'update'])->middleware('checkAuthorization:' . config('rights.editCommittee'));
            Route::delete('recommendations/{recommend_id}', [CommitteeRecommendationController::class, 'destroy'])->middleware('checkAuthorization:' . config('rights.editCommittee'));


            // Evaluation
            Route::get('evaluations', 'EvaluationController@index');

            Route::get('works-done/{committee_id}', 'WorkDoneByCommitteeController@getAllWorksDoneByCommittee')->middleware('checkAuthorization:' . config('rights.editCommittee'));
            Route::post('works-done', 'WorkDoneByCommitteeController@store')->middleware('checkAuthorization:' . config('rights.editCommittee'));
            Route::put('works-done/{id}', 'WorkDoneByCommitteeController@update')->middleware('checkAuthorization:' . config('rights.editCommittee'));
            Route::delete('works-done/{id}', 'WorkDoneByCommitteeController@destroy')->middleware('checkAuthorization:' . config('rights.editCommittee'));


            // proposals
            Route::post('proposals', 'ProposalController@store')->middleware('checkAuthorization:' . config('rights.addProposal'));
            Route::get('proposals/{proposal_id}', 'ProposalController@show');
            Route::put('proposals/{proposal_id}', 'ProposalController@update');
            Route::delete('proposals/{proposal_id}', 'ProposalController@destroy');
            Route::post('proposals/filtered-list', 'ProposalController@getPagedList')->middleware('checkAuthorization:' . config('rights.proposalsFilter'));
        });
    }
);
