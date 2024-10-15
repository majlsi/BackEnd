<?php

Route::group(['prefix' => 'v1', 'middleware' => ['jwt.customAuth', 'userActivated', 'userDeleted', 'organizationExpirationLicense', 'organizationAccess', 'organizationCompletedProfile', 'throttle:1000000000,1', 'checkNumberOfFailedLoginAttempt']],
    function () {
        
        Route::get('member-committee-dashboard', 'CommitteeDashboardController@getMemberCommitteesDashboardStatistics')->middleware('checkAuthorization:'.config('rights.memberDashboard'));
        Route::get('board-dashboard', 'CommitteeDashboardController@getBoardDashboardStatistics')->middleware('checkAuthorization:'.config('rights.boardDashboard'));
        Route::get('committee-dashboard/{id}', 'CommitteeDashboardController@getCommitteeDashboardStatistics')->middleware('checkAuthorization:'.config('rights.committeesDashboard'));
        Route::get('user-committees', 'CommitteeDashboardController@getUserManagedCommittees')->middleware('checkAuthorization:'.config('rights.committeesDashboard'));

});