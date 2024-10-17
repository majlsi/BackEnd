<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnOrganizationsTypeIdAndApipUrlToOrganizations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('api_url',255)->after('organization_code')->nullable();
            $table->integer('organization_type_id')->unsigned()->after('organization_code')->nullable();
            $table->foreign('organization_type_id')->references('id')->on('organization_types'); 
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
