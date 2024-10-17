<?php

Route::group(['prefix' => 'v1', 'middleware' => ['jwt.customAuth', 'userActivated', 'userDeleted', 'organizationExpirationLicense', 'organizationAccess', 'organizationCompletedProfile', 'throttle:1000000000,1', 'checkNumberOfFailedLoginAttempt']],
    function () {
    //failed-login-attempts
    Route::post('failed-login-attempts/filtered-list', 'FailedLoginAttemptController@getPagedList')->middleware('checkAuthorization:'.config('rights.failedLoginAttemptsFilter'));
    Route::delete('failed-login-attempts/{id}', 'FailedLoginAttemptController@destroy')->middleware('checkAuthorization:'.config('rights.deleteFailedLoginAttempt'));

    Route::group(['prefix' => 'admin'], function () {
        Route::get('organizations/organiztion-statistics/pie-chart', 'OrganizationController@getOrganizationsPieChartStatistics')->middleware('checkAuthorization:'.config('rights.adminDashboard'));
        Route::get('organizations/organiztion-statistics/bar-chart', 'OrganizationController@getOrganizationsBarChartStatistics')->middleware('checkAuthorization:'.config('rights.adminDashboard'));
        Route::get('organizations/organiztion-statistics/high-active-organizations', 'OrganizationController@getHighActiveOrganizations')->middleware('checkAuthorization:'.config('rights.adminDashboard'));

        Route::post('organizations/0/filtered-list', 'OrganizationController@getRejectedPagedList')->middleware('checkAuthorization:'.config('rights.rejectOrganisations'));
        Route::post('organizations/1/filtered-list', 'OrganizationController@getActivePagedList')->middleware('checkAuthorization:'.config('rights.approvedOrganisations'));
        Route::post('organizations/requests/filtered-list', 'OrganizationController@getRequestsPagedList')->middleware('checkAuthorization:'.config('rights.requests'));


        Route::post('organizations/activation', 'OrganizationController@activeDeactiveOrganization');

        Route::get('organization/statistics/general-statistics/{id}', 'OrganizationController@getOrganizationGeneralStatisticsByOrganizationId')->middleware('checkAuthorization:'.config('rights.organizationDashboard'));
        Route::get('organization/statistics/meeting-statistics/{id}', 'OrganizationController@getOrganizationMeetingStatisticsByOrganizationId')->middleware('checkAuthorization:'.config('rights.organizationDashboard'));
        Route::get('organization/statistics/user-statistics/{id}', 'OrganizationController@getOrganizationUserStatisticsByOrganizationId')->middleware('checkAuthorization:'.config('rights.organizationDashboard'));
    });
    Route::group(['prefix' => 'system-admin'], function () {
        Route::get('organizations/{id}', 'OrganizationController@show')->middleware('checkAuthorization:'.config('rights.editOrganization'));
        Route::put('organizations/{id}', 'OrganizationController@update')->middleware('checkAuthorization:'.config('rights.editOrganization'));
    });
});