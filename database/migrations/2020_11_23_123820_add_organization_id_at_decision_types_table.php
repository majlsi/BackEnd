<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrganizationIdAtDecisionTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('decision_types', function (Blueprint $table) {
            $table->integer('organization_id')->unsigned()->nullable()->after('decision_type_name_en');
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->boolean('is_system')->after('organization_id')->default(0);
        });
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
