<?php

namespace Helpers;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;


class UserCommentHelper
{
    

    public function __construct()
    {
       
    }
    
    public static function prepareDataOnCreate($meetingId,$userId,$commentText,$isOrganizer){
        $userCommentData = [];
        $userCommentData['user_id'] = $userId;
        $userCommentData['meeting_agenda_id'] = $meetingId;
        $userCommentData['comment_text'] = $commentText;
        $userCommentData['is_organizer'] = $isOrganizer;
        return $userCommentData;
    }
}
