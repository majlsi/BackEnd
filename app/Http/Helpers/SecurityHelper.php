<?php

namespace Helpers;

use Services\MeetingGuestService;
use Services\UserService;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class SecurityHelper
{

    private $userService;
    private MeetingGuestService $meetingGuestService;


    public function __construct(UserService $userService, MeetingGuestService $meetingGuestService)
    {
        $this->userService = $userService;
        $this->meetingGuestService = $meetingGuestService;
    }

    public static function getHashedPassword($password)
    {
        $hashedPassword = hash('sha256', $password, FALSE);
        return $hashedPassword;
    }

    public static function check($value, $hashedValue)
    {
        if (strlen($hashedValue) === 0) {
            return false;
        }

        return (hash('sha256', $value) === $hashedValue);
    }


    public function getCurrentUser()
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            // allow meeting guests and normal users
            if (!$payload->get('user_id') && !$payload->get("meeting_guest_id")) {
                return null;
            }
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $ex) {
            return null;
        }
        // allow meeting guests and normal users
        if($payload->get("meeting_guest_id")){
            $guest = $this->meetingGuestService->getById($payload->get('meeting_guest_id'));
            $guest['meeting_guest_id'] = $guest->id;
            $guest->id = -1;
            return $guest;
        } else {
            return $this->userService->getById($payload->get('user_id'));
        }
    }

    public function generateRandomPassword()
    {
        $alphabet = '1234567890';
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }
}
