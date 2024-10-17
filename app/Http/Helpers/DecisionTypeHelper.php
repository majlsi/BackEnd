<?php

namespace Helpers;
use Carbon\Carbon;

class DecisionTypeHelper
{
    public function __construct(){   
    }

    public function prepareDecisionTypesForOrganizationAdmin($organizationId,$systemDecisionTypes){
        foreach ($systemDecisionTypes as $key => $systemDecisionType) {
            $systemDecisionTypes[$key]['organization_id'] = $organizationId;
            $systemDecisionTypes[$key]['is_system'] = 0;
        }
        return $systemDecisionTypes;
    }

    public function prepareDecisionTypeData($decisionType,$organizationId)
    {
        $decisionTypeData = [];

        if(isset($decisionType['decision_type_name_ar'])) {
            $decisionTypeData['decision_type_name_ar'] = trim($decisionType['decision_type_name_ar']);
        }
        if(isset($decisionType['decision_type_name_en'])) {
            $decisionTypeData['decision_type_name_en'] = trim($decisionType['decision_type_name_en']);
        }

        $decisionTypeData['organization_id'] = $organizationId;
        $decisionTypeData['is_system'] = 0;

        return $decisionTypeData;
    }
}