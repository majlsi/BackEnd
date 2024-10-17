<?php

Route::group(['prefix' => 'v1', 'middleware' => ['jwt.customAuth', 'userActivated', 'userDeleted', 'organizationExpirationLicense', 'organizationAccess', 'organizationCompletedProfile', 'throttle:1000000000,1', 'checkNumberOfFailedLoginAttempt']],
    function () {
    Route::group(['prefix' => 'admin'], function () {
        Route::get('organizations/{id}', 'OrganizationController@show');

        Route::get('organizations/statistics/general-statistics', 'OrganizationController@getOrganizationGeneralStatistics')->middleware('checkAuthorization:'.config('rights.secertaryDashboard'));
        Route::get('organizations/statistics/user-statistics', 'OrganizationController@getOrganizationUserStatistics')->middleware('checkAuthorization:'.config('rights.secertaryDashboard'));
        Route::get('organizations/statistics/meeting-statistics', 'OrganizationController@getOrganizationMeetingStatistics')->middleware('checkAuthorization:'.config('rights.secertaryDashboard'));


        // committee-dashboard
        Route::get('organizations/statistics/permanent-committees', 'OrganizationController@getOrganizationPermanentCommitteesStatistics')->middleware('checkAuthorization:'.config('rights.committeeDashboard'));
        Route::get('organizations/statistics/temporary-committees', 'OrganizationController@getOrganizationTemporaryCommitteesStatistics')->middleware('checkAuthorization:'.config('rights.committeeDashboard'));
        Route::get('organizations/statistics/standing-committee-members', 'OrganizationController@getNumberOfStandingCommitteeMembers')->middleware('checkAuthorization:'.config('rights.committeeDashboard'));
        Route::get('organizations/statistics/freezed-committee-members', 'OrganizationController@getNumberOfFreezedCommitteeMembers')->middleware('checkAuthorization:'.config('rights.committeeDashboard'));

        Route::post('organizations/statistics/committee-days-passed/filtered-list', 'OrganizationController@getCommitteeDaysPassedPagedList')->middleware('checkAuthorization:'.config('rights.committeeDashboard'));
        Route::post('organizations/statistics/committee-remain-percentage/filtered-list', 'OrganizationController@getCommitteeRemainPercentageToFinishPagedList')->middleware('checkAuthorization:'.config('rights.committeeDashboard'));
        Route::post('organizations/statistics/most-member-participate/filtered-list', 'OrganizationController@getMostMemberParticipateInCommitteesPagedList')->middleware('checkAuthorization:'.config('rights.committeeDashboard'));
        Route::post('organizations/statistics/number-of-committees-per-decision-responsible/filtered-list', 'OrganizationController@getNumberOfCommitteesAccordingToCommitteeDecisionResponsiblePagedList')->middleware('checkAuthorization:'.config('rights.committeeDashboard'));


        Route::get('organizations/statistics/committees-statuses', 'OrganizationController@getCommitteesStatuses')->middleware('checkAuthorization:'.config('rights.committeeDashboard'));
        Route::get('organizations/statistics/percentage-of-evaluation', 'OrganizationController@getPercentageOfEvaluations')->middleware('checkAuthorization:'.config('rights.committeeDashboard'));

    });
});

Route::group(['prefix' => 'v1', 'middleware' => ['jwt.customAuth', 'userActivated', 'userDeleted', 'organizationExpirationLicense', 'throttle:1000000000,1', 'checkNumberOfFailedLoginAttempt']],
    function () {
    Route::group(['prefix' => 'admin'],
        function () {
        Route::put('organizations/{id}', 'OrganizationController@update')->middleware('checkAuthorization:'.config('rights.editMyOrganization'));
    });
});
