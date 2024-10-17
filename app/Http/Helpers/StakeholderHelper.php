<?php

namespace Helpers;


class StakeholderHelper
{


    public function __construct()
    {
    }

    public static function prepareStakeholderDataOnCreate($data)
    {
        $shareholderData = [];
        if (isset($data['identity_number'])) {
            $shareholderData["identity_number"] = $data["identity_number"];
        }

        if (isset($data["share"])) {
            $shareholderData["share"] = $data["share"];
        }

        if (isset($data["date_of_birth"])) {
            $shareholderData["date_of_birth"] = $data["date_of_birth"];
        }

        return $shareholderData;
    }

    public static function mapListOfStakeholders($stakeholders)
    {
        $list = [];

        foreach ($stakeholders as $stakeholder) {
            $mappedData = [];
            $mappedData["id"] = $stakeholder["id"];
            $mappedData["user_id"] = $stakeholder->user_id;
            $mappedData["name"] = $stakeholder->name;
            $mappedData["name_ar"] = $stakeholder->name_ar;
            $mappedData["email"] = $stakeholder->email;
            $mappedData["identity_number"] = $stakeholder->identity_number;
            $mappedData["share"] = $stakeholder->share;
            $mappedData["date_of_birth"] = $stakeholder->date_of_birth;
            $mappedData["is_active"] = $stakeholder->is_active;
            array_push($list, $mappedData);
        }

        return $list;
    }

    public static function prepareStakeholderDataOnUpdate($data)
    {
        $shareholderData = [];
        if (isset($data["date_of_birth"])) {
            $shareholderData["date_of_birth"] = $data["date_of_birth"];
        }
        if (isset($data["share"])) {
            $shareholderData["share"] = $data["share"];
        }
        if (isset($data["identity_number"])) {
            $shareholderData["identity_number"] = $data["identity_number"];
        }

        return $shareholderData;
    }
}
