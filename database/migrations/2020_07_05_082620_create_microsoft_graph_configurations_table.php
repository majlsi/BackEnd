<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMicrosoftGraphConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('microsoft_team_configurations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organization_id')->unsigned()->nullable();
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->string('microsoft_azure_app_id');
            $table->string('microsoft_azure_tenant_id');
            $table->string('microsoft_azure_client_secret');
            $table->string('microsoft_azure_user_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('microsoft_team_configurations');
    }
}
