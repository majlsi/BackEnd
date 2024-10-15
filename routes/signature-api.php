<?php

Route::group(['prefix' => 'v1', 'middleware' => ['jwt.customAuth', 'userActivated', 'userDeleted', 'organizationExpirationLicense', 'organizationAccess', 'organizationCompletedProfile', 'throttle:1000000000,1', 'checkNumberOfFailedLoginAttempt']],
    function () {
        Route::group(['prefix' => 'admin'], function () {
            // circular decisions
            Route::get('signatures/user-signatures/{decesion_id}', 'SignatureController@getUserSignatures');
            Route::post('signatures/send-code/{decesion_id}/{lang}', 'SignatureController@sendDocumentSignatureCode');
            Route::post('signatures/verify-code/{decesion_id}', 'SignatureController@verifyCode');
            Route::get('signatures/document-pages/{decesion_id}/{lang}', 'SignatureController@getDocument');
            Route::post('signatures', 'SignatureController@saveSignature');

            Route::group(['middleware' => ['checkIsSigned']], function () {
                Route::put('signatures/sign/{decesion_id}/{document_field_id}', 'SignatureController@sign');
                Route::put('signatures/reject/{decesion_id}/{document_field_id}', 'SignatureController@reject');
            });
        });
    });
