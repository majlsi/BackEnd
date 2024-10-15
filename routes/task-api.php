<?php

Route::group(['prefix' => 'v1', 'middleware' => ['jwt.customAuth', 'userActivated', 'userDeleted', 'organizationExpirationLicense', 'organizationAccess', 'organizationCompletedProfile', 'throttle:1000000000,1', 'checkNumberOfFailedLoginAttempt']],
    function () {
    Route::group(['prefix' => 'participant'], function () {
        Route::post('task-management/my-dashboard', 'TaskManagementController@myTasksDashboard')->middleware('checkAuthorization:'.config('rights.myTasksDashboard'));
        Route::post('task-management/start-task', 'TaskManagementController@startTask');
        Route::post('task-management/end-task', 'TaskManagementController@endTask');
        Route::post('task-management/renew-task', 'TaskManagementController@renewTask');
        Route::post('task-management/comment', 'TaskManagementController@addCommentToTaskHistory');
    });

    Route::group(['prefix' => 'admin'], function () {
        Route::post('task-management', 'TaskManagementController@store')->middleware('checkAuthorization:'.config('rights.addTask'));
        Route::get('task-management', 'TaskManagementController@index');
        Route::get('task-management/{task_id}', 'TaskManagementController@show');
        Route::get('task-management/{task_id}/details', 'TaskManagementController@details')->middleware('checkAuthorization:'.config('rights.editTask'));
        Route::put('task-management/{task_id}', 'TaskManagementController@update')->middleware('checkAuthorization:'.config('rights.editTask'));
        Route::delete('task-management/{task_id}', 'TaskManagementController@destroy')->middleware('checkAuthorization:'.config('rights.deleteTask'));
        Route::post('task-management/filtered-list', 'TaskManagementController@getPagedList');
        Route::post('task-management/organization-dashboard', 'TaskManagementController@organizationTasksDashboard')->middleware('checkAuthorization:'.config('rights.organizationTasks'));
        Route::get('task-management/organization-dashboard/{lang}', 'TaskManagementController@downloadTasksPdf');
        Route::get('task-management/committee-dashboard/{committee_id}/{lang}', 'TaskManagementController@downloadCommitteeTasksPdf');
        Route::post('task-management/tasks-statistics/{lang}', 'TaskManagementController@downloadTasksStatisticsPdf');
        Route::post('committee-management/{committee_id}/organization-dashboard', 'TaskManagementController@getOrganizationCommitteeTasksDashboard')->middleware('checkAuthorization:'.config('rights.allCommitteesTasks'));
    });
});