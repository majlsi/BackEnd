<?php

Route::group(['prefix' => 'v1','middleware' => ['stcWebhookSecurity']],
    function () {
    Route::post('webhook/register', 'StcEventController@webhook');

});