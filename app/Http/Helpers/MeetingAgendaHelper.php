<?php

namespace Helpers;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class MeetingAgendaHelper
{


    public function __construct( )
    {
    }

    public function prepareMeetingAgendaData($data){
        
        if(isset($data['agenda_presenters'])){
            unset($data['agenda_presenters']);
        }

        if(isset($data['agenda_attachments'])){
            unset($data['agenda_attachments']);
        }

        if(isset($data['attachments'])){
            unset($data['attachments']);
        }

        if(isset($data['agendaTempId'])){
            unset($data['agendaTempId']);
        }

        return $data;
    }

    public function filterMeetingAgendaComments($meetingParticipant,$meetingAgendas,$meetingOrganiserIds){
            foreach ($meetingAgendas as $key => $meetingAgenda) {
                    $agendaUserComments = array_filter($meetingAgenda['agenda_user_comments'], function($comment, $indx) use($meetingParticipant){
                        if($comment['user_id'] === $meetingParticipant->id){
                            return $comment;
                        }
                    },ARRAY_FILTER_USE_BOTH);
                    $meetingAgendas[$key]['agenda_user_comments'] = $agendaUserComments;
         
            }
            return $meetingAgendas;
    }
}
