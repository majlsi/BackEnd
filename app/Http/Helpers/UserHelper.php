<?php

namespace Helpers;

use Helpers\SecurityHelper;
use Services\UserService;

class UserHelper
{

    private $userService;
    private $securityHelper;
    public function __construct(UserService $userService,SecurityHelper $securityHelper)
    {
        $this->userService = $userService;
        $this->securityHelper = $securityHelper;
    }

    public static function prepareUserDataOnCreate($data, $userRole, $provider)
    {
        $userData = [];
        if (isset($data['password'])) {
            $userData["password"] = $data["password"];
        }

        if (isset($data["oauth_uid"])) {
            $userData["oauth_uid"] = $data["oauth_uid"];
        }

        if (isset($data["email"])) {
            $userData["email"] = $data["email"];
            $userData["username"] = $data["email"];
        }

        if (isset($data["name"])) {
            $userData["name"] = $data["name"];
        }

        if (isset($data["name_ar"])) {
            $userData["name_ar"] = $data["name_ar"];
        }

        if (isset($data["mobile"])) {
            $userData["username"] = $data["mobile"];
        }

        if ($userRole != null) {
            $userData["role_id"] = $userRole;
        }

        if (isset($data["language_id"])) {
            $userData["language_id"] = $data["language_id"];
        } else {
            $userData["language_id"] = config('languages.ar');
        }

        $userData["is_verified"] = 1;
        $userData['oauth_provider'] = $provider;

        return $userData;
    }

    public static function prepareUserDataOnUpdate($data, $user)
    {
        $userData = [];

        $userData["role_id"] = $user->role_id;

        if (isset($data['email'])) {
            $userData["email"] = $data["email"];
            $userData["username"] = $data["email"];
        }

        if (isset($data['name'])) {
            $userData["name"] = $data["name"];
        }

        if (isset($data["name_ar"])) {
            $userData["name_ar"] = $data["name_ar"];
        }

        if (isset($data['password'])) {
            $userData["password"] = $data["password"];
        }

        if (isset($data['organization_id'])) {
            $userData["organization_id"] = $data["organization_id"];
        } else if ($user->organization_id) {
            $userData["organization_id"] = $user->organization_id;
        } else {
            $userData["organization_id"] = null;
        }

        if (isset($data["profile_image_id"])) {
            $userData["profile_image_id"] = $data["profile_image_id"];
        } else {
            $userData["profile_image_id"] = null;
        }

        if (isset($data['profile_image'])) {
            $userData["profile_image"] = $data["profile_image"];
        }

        if (isset($data['main_page_id'])) {
            $userData["main_page_id"] = $data["main_page_id"];
        } else {
            $userData["main_page_id"] = null;
        }

        if (isset($data['user_title_id'])) {
            $userData["user_title_id"] = $data["user_title_id"];
        }

        if (isset($data['job_title_id'])) {
            $userData["job_title_id"] = $data["job_title_id"];
        }

        if (isset($data['nickname_id'])) {
            $userData["nickname_id"] = $data["nickname_id"];
        }

        if (isset($data["language_id"])) {
            $userData["language_id"] = $data["language_id"];
        }

        if (isset($data['user_phone'])) {
            $userData["user_phone"] = $data["user_phone"];
        }

        if (isset($data["disclosure_url"])) {
            $userData["disclosure_url"] = $data["disclosure_url"];
        }

        return $userData;
    }

    public static function prepareMultipleUserDataOnCreate($usersData, $provider, $organizationId)
    {
        $userData = [];
        foreach ($usersData as $key => $data) {

            if (isset($data["oauth_uid"])) {
                $userData[$key]["oauth_uid"] = $data["oauth_uid"];
            }

            if (isset($data["email"])) {
                $userData[$key]["email"] = $data["email"];
                $userData[$key]["username"] = $data["email"];
            }

            if (isset($data["name"])) {
                $userData[$key]["name"] = $data["name"];
            }

            if (isset($data["name_ar"])) {
                $userData[$key]["name_ar"] = $data["name_ar"];
            }

            if (isset($data["role_id"])) {
                $userData[$key]["role_id"] = $data["role_id"];
            }

            if (isset($data["user_phone"])) {
                $userData[$key]["user_phone"] = $data["user_phone"];
            }

            if (isset($data["language_id"])) {
                $userData[$key]["language_id"] = $data["language_id"];
            } else {
                $userData[$key]["language_id"] = config('languages.ar');
            }

            $userData[$key]["is_verified"] = 1;
            $userData[$key]['oauth_provider'] = $provider;
            $userData[$key]['organization_id'] = $organizationId;
            $userData[$key]["password"] = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
        }

        return $userData;
    }

    public static function prepareUpdateStakeholderUser($data)
    {
        $userData = [];
        if (isset($data['user_phone'])) {
            $userData["user_phone"] = $data["user_phone"];
        }
        if (isset($data['email'])) {
            $userData["email"] = $data["email"];
        }
        if (isset($data['name'])) {
            $userData["name"] = $data["name"];
        }
        if (isset($data['name_ar'])) {
            $userData["name_ar"] = $data["name_ar"];
        }
        if (isset($data['language_id'])) {
            $userData["language_id"] = $data["language_id"];
        }
        return $userData;
    }

    public function mapDataFromExcel($list, $lang, $organizationId, $roleId)
    {
        $users = [];
        foreach($list as &$user) {
            $data['user'] = [];
            $data['stakeholder'] = [];
            $data['user']['email'] = $user['email'];
            $data['user']['username'] = $user['email'];
            if($lang == config('languages.ar')){
                $data['user']['name_ar'] = $user['name'];
            }else{
                $data['user']['name'] = $user['name'];
            }
            $data['user']['language_id'] = $lang;
            $data['user']['organization_id'] = $organizationId;
            $data['user']['role_id'] = $roleId;
            $data['user']['is_stakeholder'] = true;
            $data['user']['password'] = $this->securityHelper->generateRandomPassword();

            // stakeholder data
            $date = str_replace('/', '-', $user['date_of_birth']);
            $data['stakeholder']['date_of_birth'] = date("Y-m-d", strtotime($date));
            $data['stakeholder']['identity_number'] = $user['identity_number'];
            $data['stakeholder']['share'] = $user['share'];
            array_push($users, $data);
        }
        return $users;
    }
}
