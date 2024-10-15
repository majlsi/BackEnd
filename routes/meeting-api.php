<?php

Route::group(['prefix' => 'v1', 'middleware' => ['jwt.customAuth', 'userActivated', 'userDeleted', 'organizationExpirationLicense', 'organizationAccess', 'organizationCompletedProfile', 'throttle:1000000000,1', 'checkNumberOfFailedLoginAttempt']],
    function () {

    Route::get('meeting-attachments/{attachment_id}', 'AttachmentController@getAttachmentSlides');
    Route::post('meeting-attachments/{attachment_id}/slide-notes', 'AttachmentController@fireSlideNotes');

    Route::post('users/meetings-per-month', 'MeetingController@getMeetingsForUserByMonth');
    Route::get('get-meeting-recommendation-feature-variable', 'MeetingRecommendationController@getMeetingRecommendationsFeatureVariable');


    Route::group(['prefix' => 'admin'],
        function () {
        Route::get('meetings/{meeting_id}/meeting-all-data/{current_presented_attachment_id?}', 'MeetingController@getMeetingAllData');
        Route::post('meetings/{meeting_id}/meeting-attachments/{attachment_id}/present', 'AttachmentController@presentAttachment');
        Route::post('meetings/{meeting_id}/meeting-attachments/{attachment_id}/present-with-no-end-notification', 'AttachmentController@presentAttachmentWithoutEndNotification');
        Route::post('meetings/{meeting_id}/meeting-attachments/{attachment_id}/end', 'AttachmentController@endPresentation');
        Route::get('meetings/{meeting_id}/check-current-attachment', 'AttachmentController@getMeetingPresentationAttachment');

        Route::post('meetings/{meeting_id}/meeting-attachments/{attachment_id}/end-with-no-notification', 'AttachmentController@endPresentationWithoutNotification');

        Route::post('meetings/{meeting_id}/meeting-attachments/{attachment_id}/change-presenter', 'AttachmentController@changePresenter');
        Route::post('meetings/{meeting_id}/meeting-attachments/{attachment_id}/check-presentation-master', 'AttachmentController@checkPresentationMaster');
        Route::get('meetings/{meeting_id}/meeting-agendas/{agenda_id}', 'MeetingAgendaController@getAgendaForMeeting');
        Route::get('attachments/{attachment_id}', 'AttachmentController@show');
        Route::post('meetings/{meeting_id}/user-comment', 'UserCommentController@store');

        Route::get('meetings/{meeting_id}/meeting-attachments', 'AttachmentController@getAttachmentsForMeeting');
        Route::post('meetings/{meeting_id}/meeting-attachments', 'AttachmentController@setAttachmentsForMeeting')->middleware('checkAuthorization:'.config('rights.editMeeting'));
        Route::delete('meetings/{meeting_id}/meeting-agendas/{agenda_id}/attachments/{attachment_id}', 'MeetingAgendaController@destroyAttachment')->middleware('checkAuthorization:'.config('rights.deleteMeeting'));

        Route::get('meetings/{meeting_id}/meeting-participants', 'MeetingParticipantController@getMeetingParticipantsForMeeting');
        Route::post('meetings/{meeting_id}/meeting-participants', 'MeetingParticipantController@storeMeetingParticipantsForMeeting')->middleware('checkAuthorization:'.config('rights.editMeeting'));

        Route::get('meetings/{meeting_id}/meeting-organisers', 'MeetingOrganiserController@getMeetingOrganisersForMeeting');
        Route::post('meetings/{meeting_id}/meeting-organisers', 'MeetingOrganiserController@storeMeetingOrganisersForMeeting')->middleware('checkAuthorization:'.config('rights.editMeeting'));

        Route::get('meetings/{meeting_id}/meeting-agendas', 'MeetingAgendaController@getMeetingAgendasForMeeting');

        Route::post('meetings/{meeting_id}/meeting-agendas', 'MeetingAgendaController@setMeetingAgendasForMeeting')->middleware('checkAuthorization:'.config('rights.editMeeting'));
        Route::delete('meetings/{meeting_id}/meeting-agendas/{agenda_id}', 'MeetingAgendaController@destroy')->middleware('checkAuthorization:'.config('rights.deleteMeeting'));

        Route::get('meetings/{meeting_id}/meeting-votes', 'VoteController@getMeetingVotes');
        Route::post('meetings/{meeting_id}/meeting-votes', 'VoteController@setMeetingVotes')->middleware('checkAuthorization:'.config('rights.editMeeting'));
        Route::delete('meetings/{meeting_id}/meeting-votes/{vote_id}', 'VoteController@destroy')->middleware('checkAuthorization:'.config('rights.deleteMeeting'));

        Route::get('meetings/{meeting_id}/moms', 'MomController@getMeetingMom');
        Route::post('meetings/{meeting_id}/moms', 'MomController@setMeetingMom');
        Route::delete('meetings/{meeting_id}/moms/{mom_id}', 'MomController@destroy');
        Route::get('meetings/{meeting_id}/mom-templates/{mom_template_id}', 'MeetingController@getMeetingMomTemplate');
        Route::post('meetings/{meeting_id}/publish-meeting', 'MeetingController@publishMeeting');
        Route::post('meetings/{meeting_id}/publish-agenda-meeting', 'MeetingController@publishMeetingAgenda');
        Route::post('meetings/{meeting_id}/start-meeting', 'MeetingController@startMeeting');
        Route::post('meetings/{meeting_id}/end-meeting', 'MeetingController@endMeeting');
        Route::post('meetings/{meeting_id}/send-email-after-end-meeting', 'MeetingController@sendEmailAfterEndMeeting');

        Route::post('meetings/{meeting_id}/cancel-meeting', 'MeetingController@cancelMeeting');
        Route::post('meetings/{meeting_id}/undo-cancel-meeting', 'MeetingController@draftMeeting');
        Route::post('meetings/{meeting_id}/committee/users', 'MeetingController@meetingCommitteeUsers');

        Route::delete('meetings/{meeting_id}/moms/attachments/{attachment_id}', 'AttachmentController@destroy');
        Route::post('meetings/{meeting_id}/check-schedule-conflict', 'MeetingController@checkScheduleConflict');
        // Route::resource('meetings/{meeting_id}/proposals', 'ProposalController');
        // Route::get('meetings/{meeting_id}/meeting-proposals', 'ProposalController@getMeetingProposals');

        Route::delete('meetings/{meeting_id}/user-comment/{user_comment_id}', 'UserCommentController@destroy');
        Route::post('meetings/{meeting_id}/check-organiser', 'MeetingOrganiserController@checkIfOrganiser');

        Route::post('meetings/{meeting_id}/end-vote', 'VoteController@endVote');
        Route::post('meetings/{meeting_id}/start-vote', 'VoteController@startVote');
        Route::post('meetings/{meeting_id}/send-signature-mail', 'MeetingController@sendSignatureToAllParticipants');
        Route::post('meetings/{meeting_id}/participant-send-signature-mail', 'MeetingController@sendSignatureToParticipant');

        Route::get('meetings/{meeting_id}/zoom/start-url', 'MeetingController@getZoomMeetingStartUrl');
        Route::post('meetings/{meeting_id}/publish-changes', 'MeetingController@publishMeetingChanges');

        Route::post('meetings/{meeting_id}/change-mom-template', 'MeetingController@changeMeetingMomTemplate');
        Route::post('meetings/{meeting_id}/change-mom-pdf', 'MeetingController@changeMeetingMomPdf');

        Route::get('meetings-versions/{meeting_id}', 'MeetingController@getMeetingVersionData');
                    
        Route::get('meetings', 'MeetingController@index');
        Route::post('meetings', 'MeetingController@store')->middleware('checkAuthorization:'.config('rights.addMeeting'));
        Route::get('meetings/{meeting_id}', 'MeetingController@show');
        Route::put('meetings/{meeting_id}', 'MeetingController@update')->middleware('checkAuthorization:'.config('rights.editMeeting'));
        Route::delete('meetings/{meeting_id}', 'MeetingController@destroy')->middleware('checkAuthorization:'.config('rights.deleteMeeting'));

        Route::get('preview-mom/{meeting_id}/language/{lang}', 'MeetingController@previewMom');

        Route::post('meetings/{meeting_id}/participant/attend-attendance-status', 'MeetingParticipantController@setAttendForMeentingParticipant');
        Route::post('meetings/{meeting_id}/participant/absent-attendance-status', 'MeetingParticipantController@setAbsentForMeentingParticipant');
        Route::post('meetings/{meeting_id}/participants/attend-attendance-status', 'MeetingParticipantController@setAttendForMeentingParticipants');
        Route::post('meetings/{meeting_id}/participants/absent-attendance-status', 'MeetingParticipantController@setAbsentForMeentingParticipants');
        Route::post('meetings/{meeting_id}/participant/accept-absent-attendance-status', 'MeetingParticipantController@setAcceptAbsentForMeentingParticipant');
        Route::post('meetings/{meeting_id}/participants/accept-absent-attendance-status', 'MeetingParticipantController@setAcceptAbsentForMeentingParticipants');

        Route::get('meetings/{meeting_id}/participants', 'MeetingParticipantController@getMeetingParticipants');
        Route::get('meetings/{meeting_id}/attendance-percentage', 'MeetingController@getMeetingPercentage');

        Route::post('meetings/filtered-list', 'MeetingController@getPagedList')->middleware('checkAuthorization:'.config('rights.meetingsFilter'));

        Route::post('meetings/current', 'MeetingController@getCurrentList')->middleware('checkAuthorization:'.config('rights.meetingDashboard'));
        Route::post('meetings/previous', 'MeetingController@getPreviousList')->middleware('checkAuthorization:'.config('rights.meetingDashboard'));
        Route::post('meetings/upcoming', 'MeetingController@getUpComingList');
        Route::post('meetings/today', 'MeetingController@getTodayMeetingList');

        Route::post('meetings/current-previous/filtered-list', 'MeetingController@getCurrentPreviousList');
        Route::post('organizations/meetings-per-month', 'MeetingController@getMeetingsForOrganizationByMonth');
        Route::post('manage-absence/filtered-list', 'MeetingParticipantAlternativeController@getPagedList')->middleware('checkAuthorization:'.config('rights.meetingAbsenceFilter'));
        Route::post('meetings/{meeting_id}/tasks', 'MeetingController@getTasks')->middleware('meetingAccess');
        Route::post('meetings/{meeting_id}/meeting-participants-share', 'MeetingController@getMeetingParticipantsShare')->middleware('meetingAccess');
        Route::post('meetings/{meeting_id}/meeting-attendance-share', 'MeetingController@getMeetingAttendanceShare')->middleware('meetingAccess');
        Route::post('meetings/{meeting_id}/add-approval-meeting', 'MeetingController@AddApprovalToMeeting')->middleware('checkAuthorization:' . config('rights.editMeeting'));
        Route::put('meetings/{meeting_id}/update-approval-meeting/{approval:id}', 'MeetingController@UpdateApprovalToMeeting')->middleware('checkAuthorization:' . config('rights.editMeeting'));
        Route::delete('meetings/{meeting_id}/delete-approval-from-meeting/{approval:id}', 'MeetingController@DeleteApprovalFromMeeting')->middleware('checkAuthorization:' . config('rights.editMeeting'));
        // meeting recommendation
        Route::get('meetings/{meeting_id}/meeting-recommendation', 'MeetingRecommendationController@getMeetingRecommendationsForMeeting');
        Route::get('meetings/{meeting_id}/send-meeting-recommendation', 'MeetingController@sendMeetingRecommendations');
        Route::post('meetings/{meeting_id}/meeting-recommendation', 'MeetingRecommendationController@setMeetingRecommendationsForMeeting')->middleware('checkAuthorization:' . config('rights.editMeeting'));
        Route::delete('meetings/{meeting_id}/meeting-recommendation/{recommendation_id}', 'MeetingRecommendationController@destroy')->middleware('checkAuthorization:' . config('rights.editMeeting'));
    });

    Route::group(['prefix' => 'participant', 'middleware' => ['meetingAccess']],
        function () {
        Route::get('statistics/meeting-statistics', 'MeetingController@getParticipantMeetingStatistics')->middleware('checkAuthorization:'.config('rights.participantDashboard'));

        Route::get('meetings/{meeting_id}/meeting-all-data/{current_presented_attachment_id?}', 'MeetingController@getMeetingAllData');
        
        Route::post('meetings/{meeting_id}/change-participant-status', 'MeetingParticipantController@changeStatus');
        Route::get('meetings/{meeting_id}/current-presenting-attachment', 'MeetingController@getCurrentPresentingAttachment');
        Route::post('meetings/{meeting_id}/proposals', 'ProposalController@store');
        Route::put('meetings/{meeting_id}/proposals/{proposal_id}', 'ProposalController@update');
        Route::post('meetings/{meeting_id}/vote-results', 'VoteResultController@store');
        Route::put('meetings/{meeting_id}/vote-results/{vote_result_id}', 'VoteResultController@update');
        Route::post('meetings/{meeting_id}/participant-attendance', 'MeetingParticipantController@updateParticipantAttendance');
        Route::get('meetings/{meeting_id}/meeting-votes/{vote_id}/results', 'VoteResultController@getVoteResults');
        Route::get('meetings/{meeting_id}/mom-download/{lang}', 'MeetingController@downloadMomPdf');
        Route::post('meetings/{meeting_id}/signature-user-login', 'MeetingController@loginUserToSignature');

        Route::group(['middleware' => ['voteAccess']],
            function () {
            /** votes action api */
            Route::post('meetings/{meeting_id}/meeting-votes/{vote_id}/yes', 'VoteController@changeVoteResultToYes');
            Route::post('meetings/{meeting_id}/meeting-votes/{vote_id}/no', 'VoteController@changeVoteResultToNo');
            Route::post('meetings/{meeting_id}/meeting-votes/{vote_id}/abstained', 'VoteController@changeVoteResultToAbstained');

            /** meeting attendance apis */
            Route::post('meetings/{meeting_id}/attend-attendance-status', 'MeetingParticipantController@attendMeenting');
            Route::post('meetings/{meeting_id}/absent-attendance-status', 'MeetingParticipantController@absentMeenting');
            Route::post('meetings/{meeting_id}/mayattend-attendance-status', 'MeetingParticipantController@mayAttendMeenting');
        });
    });

});