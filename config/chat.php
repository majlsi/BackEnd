<?php

    return [
        'apiBaseURL' => env('CHAT_API_BASE_URL', 'http://localhost/mjlsi/Code/Chat/public/api/v1/'),
        'chatAppId' => env('CHAT_APP_ID', 1),
        'committeeChatName' => env('CHAT_COMMITTEE_NAMR', 'committee_'),
        'meetingChatName' => env('CHAT_MEETING_NAMR', 'meeting_'),
        'groupChatName' => env('CHAT_GROUP_NAMR', 'chat_group_'),
    ];
