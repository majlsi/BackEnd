<?php

Route::group(['prefix' => 'v1', 'middleware' => ['jwt.customAuth', 'userActivated', 'userDeleted', 'organizationExpirationLicense', 'organizationAccess', 'organizationCompletedProfile', 'throttle:1000000000,1', 'checkNumberOfFailedLoginAttempt']],
    function () {
        Route::group(['prefix' => 'admin'], function () {
            // circular decisions
            Route::post('circular-decisions', 'VoteController@storeCircularDecicion')->middleware('checkAuthorization:'.config('rights.addCircularDecision'));
            Route::get('circular-decisions/{circular_decision_id}', 'VoteController@getCircularDecicion');
            Route::put('circular-decisions/{circular_decision_id}', 'VoteController@updateCircularDecicion')->middleware('checkAuthorization:'.config('rights.editCircularDecision'));
            Route::delete('circular-decisions/{circular_decision_id}', 'VoteController@deleteCircularDecicion')->middleware('checkAuthorization:'.config('rights.deleteCircularDecision'));
            Route::delete('circular-decisions/{circular_decision_id}/attachments/{attachment_id}', 'VoteController@destroyVoteAttachment');
            Route::post('circular-decisions/filtered-list', 'VoteController@getCircularDecisionsPagedList')->middleware('checkAuthorization:'.config('rights.circularDecisionsFilter'));
            Route::post('circular-decisions/{circular_decision_id}/no', 'VoteController@setCircularDecisionResultToNo');
            Route::post('circular-decisions/{circular_decision_id}/yes', 'VoteController@setCircularDecisionResultToYes');
            Route::post('circular-decisions/{circular_decision_id}/abstained', 'VoteController@setCircularDecisionResultToAbstained');
            Route::post('circular-decisions/{circular_decision_id}/tasks', 'VoteController@getTasks')->middleware('checkAuthorization:'.config('rights.editCircularDecision'));

            Route::post('meeting-votes/filtered-list', 'VoteController@getPagedList')->middleware('checkAuthorization:'.config('rights.meetingDecisionsFilter'));

            Route::post('circular-decisions/{circular_decision_id}/signature-login', 'VoteController@loginUserToVoteSignature');
            Route::get('circular-decisions/{circular_decision_id}/pdf-download/{lang}', 'VoteController@downloadCircularDecisionPdf');

        });
});

