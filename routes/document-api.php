<?php

Route::group(['prefix' => 'v1', 'middleware' => ['jwt.customAuth', 'userActivated', 'userDeleted', 'organizationExpirationLicense', 'organizationAccess', 'organizationCompletedProfile', 'throttle:1000000000,1', 'checkNumberOfFailedLoginAttempt']],
    function () {
    Route::group(['prefix' => 'admin'], function () {
        // docuemnt annotations
        Route::resource('documents/{document_id}/annotations', 'DocumentAnnotationController');

        // review room
        Route::get('documents', 'DocumentController@index');
        Route::post('documents', 'DocumentController@store')->middleware('checkAuthorization:'.config('rights.addDocument'));
        Route::get('documents/{document_id}', 'DocumentController@show');
        Route::put('documents/{document_id}', 'DocumentController@update')->middleware('checkAuthorization:'.config('rights.editDocument'));
        Route::delete('documents/{document_id}', 'DocumentController@destroy')->middleware('checkAuthorization:'.config('rights.deleteDocument'));
        Route::post('documents/filtered-list', 'DocumentController@getpagedList')->middleware('checkAuthorization:'.config('rights.documentsFilter'));
        Route::post('documents/{document_id}/complete', 'DocumentController@completeDocument');
        Route::get('documents/{document_id}/slides', 'DocumentController@getDocumentSlides');
        Route::get('documents/{document_id}/download', 'DocumentController@downloadDocument');
    });
});