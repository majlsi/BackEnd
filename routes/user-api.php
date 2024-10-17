<?php

Route::group(
    ['prefix' => 'v1', 'middleware' => ['jwt.customAuth', 'userActivated', 'userDeleted', 'organizationExpirationLicense', 'organizationAccess', 'organizationCompletedProfile', 'throttle:1000000000,1', 'checkNumberOfFailedLoginAttempt']],
    function () {
        //users
        Route::post('users/filtered-list', 'UserController@getPagedList')->middleware('checkAuthorization:' . config('rights.usersFilter'));
        Route::get('users', 'UserController@index');
        Route::post('users', 'UserController@store')->middleware('checkAuthorization:' . config('rights.addUser'));
        Route::get('users/{user_id}', 'UserController@show')->middleware('checkAuthorization:' . config('rights.editUser'));
        Route::put('users/{user_id}', 'UserController@update')->middleware('checkAuthorization:' . config('rights.editUser'));
        Route::delete('users/{user_id}', 'UserController@destroy')->middleware('checkAuthorization:' . config('rights.deleteUser'));

        //stakeholders
        Route::post('stakeholders', 'StakeholderController@store')->middleware('checkAuthorization:' . config('rights.addShareholder'));
        Route::post('stakeholders/filtered-list', 'StakeholderController@getPagedList')->middleware('checkAuthorization:' . config('rights.shareholdersFilter'));
        Route::delete('stakeholders/{stakeholder_id}', 'StakeholderController@destroy')->middleware('checkAuthorization:' . config('rights.deleteShareholder'));
        Route::get('stakeholders/{stakeholder_id}', 'StakeholderController@show')->middleware('checkAuthorization:' . config('rights.editShareholder'));
        Route::put('stakeholders/{stakeholder_id}', 'StakeholderController@update')->middleware('checkAuthorization:' . config('rights.editShareholder'));
        Route::post('stakeholders/total-shares', 'StakeholderController@getTotalShares')->middleware('checkAuthorization:' . config('rights.editShareholder'));
        Route::post('stakeholders/download-blank-excel-template', 'StakeholderController@downloadBlankExcelTemplate')->middleware('checkAuthorization:' . config('rights.addShareholder'));
        Route::post('stakeholders/valid-stakeholders-from-excel', 'StakeholderController@validateStakeholdersFromExcel')->middleware('checkAuthorization:' . config('rights.addShareholder'));
        Route::post('stakeholders/add-bulk-stakeholders-from-excel', 'StakeholderController@bulkInsertStakeholdersFromExcel')->middleware('checkAuthorization:' . config('rights.addShareholder'));

        // Guest Url
        Route::get('guests/authenticate-guest', 'Auth\AuthenticateController@AuthenticateGuest');
        Route::put('guests/update-guest', 'UserController@UpdateGuest');
        Route::get('guests/get-guest', 'UserController@GetGuestInfo');
        //variable
        Route::get('get-block-user-feature-variable','UserController@getBlockUserFeatureVariable');

        Route::group(['prefix' => 'admin'], function () {
            Route::post('users/activation', 'UserController@activeDeactiveUser');

            Route::post('stakeholders/activation', 'StakeholderController@activateDeactivateStakeholder');
            Route::get('organization-users', 'UserController@getOrganizationUsers');
            Route::post('organization-users', 'UserController@getMatchedOrganizationUsers');
            Route::post('organization-users/filtered-list', 'UserController@getOrganizationUsersPagedList');
            Route::post('organization-users-stakeholders', 'UserController@getOrganizationUsersWithStakeholders');
            Route::post('added-users-check', 'UserController@checkIfOrganizationCanAddUsers');

            Route::post('organization-users-committees', 'UserController@searchOrganizationUsersAndCommittees');
            Route::post('users/block', 'UserController@blockUnblockedUser');
            Route::get('users/export-unActive-users-excel', 'UserController@exportUnActiveUsers');

        });
    }
);
