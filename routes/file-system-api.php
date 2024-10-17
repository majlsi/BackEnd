<?php

Route::group(['prefix' => 'v1', 'middleware' => ['jwt.customAuth', 'userActivated', 'userDeleted', 'organizationExpirationLicense', 'organizationAccess', 'organizationCompletedProfile', 'throttle:1000000000,1', 'checkNumberOfFailedLoginAttempt','checkAuthorization:'.config('rights.files')]],
    function () {
    Route::post('directories/my-directories', 'DirectoryController@MyDirectories');
    Route::post('directories/shared', 'DirectoryController@getShared');
    Route::post('directories/recent', 'DirectoryController@getRecent');

    Route::get('directories/{directoryId}', 'DirectoryController@show')->middleware('storageAccessCheck:'. config('storageRights.read'));
    Route::post('directories', 'DirectoryController@store');
    Route::post('directories/{directoryId}', 'DirectoryController@addOnDirectory')->middleware('storageAccessCheck:'. config('storageRights.update'));
    Route::put('directories/{directoryId}', 'DirectoryController@update')->middleware('storageAccessCheck:'. config('storageRights.update'));
    Route::delete('directories/{directoryId}', 'DirectoryController@destroy')->middleware('storageAccessCheck:'. config('storageRights.delete'));
    Route::get('directories/details/{directoryId}', 'DirectoryController@getDetails')->middleware('storageAccessCheck:'. config('storageRights.read'));
    Route::put('directories/removeAccess/{directoryId}/{storageAccessId}', 'DirectoryController@removeStorageAccess')->middleware('storageAccessCheck:'. config('storageRights.share'));
    Route::put('directories/shareFolder/{directoryId}', 'DirectoryController@shareFolder')->middleware('storageAccessCheck:'. config('storageRights.share'));
    Route::put('directories/rename/{directoryId}', 'DirectoryController@rename')->middleware('storageAccessCheck:'. config('storageRights.update'));
    Route::get('directories/download/{directoryId}', 'DirectoryController@download')->middleware('storageAccessCheck:'. config('storageRights.read'));
    Route::post('directories/details/{directoryId}/directories', 'DirectoryController@getDetailsDirectories')->middleware('storageAccessCheck:'. config('storageRights.read'));
    Route::post('directories/details/{directoryId}/files', 'DirectoryController@getDetailsFiles')->middleware('storageAccessCheck:'. config('storageRights.read'));



    Route::post('files/my-files', 'FileController@myFiles');
    Route::post('files/my-new-files', 'FileController@newFiles');
    Route::post('files/shared', 'FileController@getShared');
    Route::post('files/recent', 'FileController@getRecent');
    Route::post('files/new-shared', 'FileController@getSharedRecent');

    Route::get('files/{fileId}', 'FileController@show')->middleware('storageAccessCheck:'. config('storageRights.read'));
    Route::get('files/download/{fileId}', 'FileController@download')->middleware('storageAccessCheck:'. config('storageRights.read'));
    Route::post('files/add-files/{directoryId?}', 'FileController@addFiles')->middleware('storageAccessCheck:'. config('storageRights.update'))->middleware('storageLimit');

    // Route::post('files/{directoryId?}', 'FileController@store')->middleware('storageAccessCheck:'. config('storageRights.update'));
    Route::put('files/{fileId}', 'FileController@update')->middleware('storageAccessCheck:'. config('storageRights.update'));
    Route::delete('files/{fileId}', 'FileController@destroy')->middleware('storageAccessCheck:'. config('storageRights.delete'));
    Route::put('files/removeAccess/{fileId}/{storageAccessId}', 'FileController@removeStorageAccess')->middleware('storageAccessCheck:'. config('storageRights.share'));
    Route::put('files/shareFile/{fileId}', 'FileController@shareFile')->middleware('storageAccessCheck:'. config('storageRights.share'));
    Route::put('files/rename/{fileId}', 'FileController@rename')->middleware('storageAccessCheck:'. config('storageRights.update'));


});
