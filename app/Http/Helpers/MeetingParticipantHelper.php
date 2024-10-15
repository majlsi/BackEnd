<?php

namespace Helpers;


class MeetingParticipantHelper
{


    public function __construct()
    {
    }

    public function prepareMeetingParticipants($participants, $guests)
    {
        foreach ($guests as &$guest) {
            $guest["isGuest"] = true;
            $guest["meeting_guest_id"] = $guest["id"];
            unset($guest["id"]);
        }
        $participants = array_merge($participants->toArray(), $guests);
        return $participants;
    }
}
