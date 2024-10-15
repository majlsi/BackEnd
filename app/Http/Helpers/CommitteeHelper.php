<?php

namespace Helpers;

use Services\CommitteeService;

class CommitteeHelper
{
    private $committeeService;

    public function __construct(CommitteeService $committeeService)
    {
        $this->committeeService = $committeeService;
    }

    public function prepareCommitteData($data)
    {
        $committee = [];

        if (isset($data['committee_name_ar'])) {
            $committee['committee_name_ar'] = $data['committee_name_ar'];
        }
        if (isset($data['committee_name_en'])) {
            $committee['committee_name_en'] = $data['committee_name_en'];
        }
        if (isset($data['organization_id'])) {
            $committee['organization_id'] = $data['organization_id'];
        }
        if (isset($data['committee_head']) && isset($data['committee_head']['id'])) {
            $committee['committee_head_id'] = $data['committee_head']['id'];
        }
        if (isset($data['committee_organiser']) && isset($data['committee_organiser']['id'])) {
            $committee['committee_organiser_id'] = $data['committee_organiser']['id'];
        }
        if(isset($data['member_users']))
        {
                $committee['committeee_members_count'] = count($data['member_users']);
                $committee['member_users'] = $data['member_users'];
        }
        if (isset($data['committee_code'])) {
            $committee['committee_code'] = $data['committee_code'];
        }
        if (isset($data['committee_start_date'])) {
            $committee['committee_start_date'] = $data['committee_start_date'];
        } else {
            $committee['committee_start_date'] = null;   
        }
        if (isset($data['committee_expired_date'])) {
            $committee['committee_expired_date'] = $data['committee_expired_date'];
        } else {
            $committee['committee_expired_date'] = null;
        }
        if (isset($data['governance_regulation_url'])) {
            $committee['governance_regulation_url'] = $data['governance_regulation_url'];
        }
        if (isset($data['has_recommendation_section'])) {
            $committee['has_recommendation_section'] = $data['has_recommendation_section'];
        }
        if(config('customSetting.addCommitteeNewFields'))
        {
            if (isset($data['decision_number'])) {
                $committee['decision_number'] = $data['decision_number'];
            }
            if (isset($data['decision_date'])) {
                $committee['decision_date'] = $data['decision_date'];
            }
            if (isset($data['committee_responsible']) && isset($data['committee_responsible']['id'])) {
                $committee['decision_responsible_user_id'] = $data['committee_responsible']['id'];
            }
            if (isset($data['committee_status_id'])) {
                $committee['committee_status_id'] = $data['committee_status_id'];
            }
            if (isset($data['decision_document_url'])) {
                $committee['decision_document_url'] = $data['decision_document_url'];
            }
            if (isset($data['committee_type_id'])) {
                $committee['committee_type_id'] = $data['committee_type_id'];
            }
            if (isset($data['committee_reason'])) {
                $committee['committee_reason'] = $data['committee_reason'];
            }
        }
        if(config('customSetting.committeeHasNatureFeature'))
        {
            if (isset($data['committee_nature_id'])) {
                $committee['committee_nature_id'] = $data['committee_nature_id'];
            }
        }
        return $committee;
    }

    public function prepareCommiteesOrganizationAdmin($organizationId,$systemCommittees)
    {
        foreach ($systemCommittees as $key => $systemCommittee) {
            $systemCommittees[$key]['organization_id'] = $organizationId;
            $systemCommittees[$key]['is_system'] = 0;
        }
        return $systemCommittees;
    }

    public function prepareCommitteeUpdateData($oldCommittee, $updateCommitteeRequest)
    {
        if (isset($updateCommitteeRequest['committee_name_ar'])) {
            $oldCommittee['committee_name_ar'] = $updateCommitteeRequest['committee_name_ar'];
        }
        if (isset($updateCommitteeRequest['committee_name_en'])) {
            $oldCommittee['committee_name_en'] = $updateCommitteeRequest['committee_name_en'];
        }
        if (isset($updateCommitteeRequest['committee_head_id'])) {
            $oldCommittee['committee_head_id'] = $updateCommitteeRequest['committee_head_id'];
        }
        if (isset($updateCommitteeRequest['committee_organiser_id'])) {
            $oldCommittee['committee_organiser_id'] = $updateCommitteeRequest['committee_organiser_id'];
        }
        if (isset($updateCommitteeRequest['committee_code'])) {
            $oldCommittee['committee_code'] = $updateCommitteeRequest['committee_code'];
        }
        if (isset($updateCommitteeRequest['committee_start_date'])) {
            $oldCommittee['committee_start_date'] = $updateCommitteeRequest['committee_start_date'];
        } else {
            $oldCommittee['committee_start_date'] = null;
        }
        if (isset($updateCommitteeRequest['committee_expired_date'])) {
            $oldCommittee['committee_expired_date'] = $updateCommitteeRequest['committee_expired_date'];
        } else {
            $oldCommittee['committee_expired_date'] = null;
        }
        if (isset($updateCommitteeRequest['governance_regulation_url'])) {
            $oldCommittee['governance_regulation_url'] = $updateCommitteeRequest['governance_regulation_url'];
        }
        if (isset($updateCommitteeRequest['has_recommendation_section'])) {
            $oldCommittee['has_recommendation_section'] = $updateCommitteeRequest['has_recommendation_section'];
        }
        if (config('customSetting.addCommitteeNewFields')) {
            if (isset($updateCommitteeRequest['decision_number'])) {
                $oldCommittee['decision_number'] = $updateCommitteeRequest['decision_number'];
            }
            if (isset($updateCommitteeRequest['decision_date'])) {
                $oldCommittee['decision_date'] = $updateCommitteeRequest['decision_date'];
            }
            if (isset($updateCommitteeRequest['decision_responsible_user_id'])) {
                $oldCommittee['decision_responsible_user_id'] = $updateCommitteeRequest['decision_responsible_user_id'];
            }
            if (isset($updateCommitteeRequest['decision_document_url'])) {
                $oldCommittee['decision_document_url'] = $updateCommitteeRequest['decision_document_url'];
            }
            if (isset($updateCommitteeRequest['committee_type_id'])) {
                $oldCommittee['committee_type_id'] = $updateCommitteeRequest['committee_type_id'];
            }
            if (isset($updateCommitteeRequest['committee_reason'])) {
                $oldCommittee['committee_reason'] = $updateCommitteeRequest['committee_reason'];
            }
        }

        return $oldCommittee;
    }
}
