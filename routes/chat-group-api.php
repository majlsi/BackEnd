<?php

Route::group(['prefix' => 'v1', 'middleware' => ['jwt.customAuth', 'userActivated', 'userDeleted', 'organizationExpirationLicense', 'organizationAccess', 'organizationCompletedProfile', 'throttle:1000000000,1', 'checkNumberOfFailedLoginAttempt']],
    function () {
    Route::group(['prefix' => 'admin'], function () {
        // chat groups
        Route::post('chat-groups', 'ChatGroupController@store');
        Route::post('chat-groups/{chat_group_id}/filtered-list', 'ChatGroupController@getChatHistory');
        Route::post('chat-groups/{chat_group_id}/send', 'ChatGroupController@sendMessageInChat');
        Route::post('chat-groups/{chat_group_id}/send-attachemnt', 'ChatGroupController@sendAttachmentInChat');
        Route::get('chat-groups/{chat_group_id}', 'ChatGroupController@show');
        Route::delete('chat-groups/{chat_group_id}/chat-group-users/{user_id}', 'ChatGroupController@deleteChatGroupUser');
        Route::put('chat-groups/{chat_group_id}', 'ChatGroupController@update');
        Route::post('chat-groups/individual', 'ChatGroupController@createIndividualChat');
        Route::put('chat-groups/individual/{chat_group_id}', 'ChatGroupController@updateIndividualChat');
        Route::post('chat-groups/filtered-list', 'ChatGroupController@getPagedList');
        Route::post('chat-groups/groups/filtered-list', 'ChatGroupController@getChatGroupsPagedList');
        Route::post('chat-groups/individuals/filtered-list', 'ChatGroupController@getIndividualsChatPagedList');
        Route::post('chat-groups/{chat_group_id}/add-users', 'ChatGroupController@addUsersToChatGroup');
        Route::post('chat-groups/{chat_group_id}/attachments/filtered-list', 'ChatGroupController@getChatAttachments');
        Route::post('meetings/{meeting_id}/chat-groups', 'ChatGroupController@createChatGroupForMeeting');
        Route::post('committees/{committee_id}/chat-groups', 'ChatGroupController@createChatGroupForCommittee');
        Route::put('meetings/{meeting_id}/chat-groups/{chat_group_id}', 'ChatGroupController@updateChatGroupForMeeting');
        Route::put('committees/{committee_id}/chat-groups/{chat_group_id}', 'ChatGroupController@updateChatGroupForCommittee');
    
        // chat rooms
        Route::get('chat-rooms/{id}', 'ChatRoomController@show');
        Route::post('chat-rooms/filtered-list', 'ChatRoomController@getPagedList');
        Route::post('meetings/chat-rooms/filtered-list', 'ChatRoomController@getMeetingsChatsPagedList');
        Route::post('committees/chat-rooms/filtered-list', 'ChatRoomController@getCommitteesChatsPagedList');
        Route::post('meetings/{id}/chat-rooms/filtered-list', 'ChatRoomController@getMeetingChatHistory');
        Route::post('committees/{id}/chat-rooms/filtered-list', 'ChatRoomController@getCommitteeChatHistory');
        Route::post('meetings/{id}/chat-rooms', 'ChatRoomController@createMeetingChat');
        Route::post('committees/{id}/chat-rooms', 'ChatRoomController@createCommitteeChat');
        Route::post('chat-rooms/all', 'ChatRoomController@getRooms');
        Route::post('chat-rooms/meetings/{id}/send', 'ChatRoomController@sendMessageInMeeting');
        Route::post('chat-rooms/committees/{id}/send', 'ChatRoomController@sendMessageInCommittee');
        Route::get('meetings/{id}/meeting-users', 'MeetingController@getMeetingUsers');
        Route::get('committees/{id}/committee-users', 'CommitteeController@getCommitteeUsers');
        Route::post('chat/upload-file', 'UploadController@uploadFileToChat');
        Route::post('chat-rooms/meetings/{id}/send-attachemnt', 'ChatRoomController@sendAttachmentInMeeting');
        Route::post('chat-rooms/committees/{id}/send-attachemnt', 'ChatRoomController@sendAttachmentInCommittee');
        Route::post('chat-rooms/meetings/{id}/attachments/filtered-list', 'ChatRoomController@getMeetingChatAttachments');
        Route::post('chat-rooms/committees/{id}/attachments/filtered-list', 'ChatRoomController@getCommitteeChatAttachments');
    });
});