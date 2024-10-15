<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Models\Organization;
use Models\DecisionType;

class AddDecisionTypesToOrganizations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $organizations = Organization::get();
        $decisionTypes = DecisionType::whereNull('organization_id')->get();
        foreach ($organizations as $key => $organization) {
            if (count($organization->decisionTypes) == 0) {
                $organizatioDecisionTypes = [];
                foreach ($decisionTypes as $key => $decisionType) {
                    $organizatioDecisionTypes[$key]['organization_id'] = $organization->id; 
                    $organizatioDecisionTypes[$key]['decision_type_name_en'] = $decisionType['decision_type_name_en'];
                    $organizatioDecisionTypes[$key]['decision_type_name_ar'] = $decisionType['decision_type_name_ar'];
                }
                $organization->decisionTypes()->createMany($organizatioDecisionTypes);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
