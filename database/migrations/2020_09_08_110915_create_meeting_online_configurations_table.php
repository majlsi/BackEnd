<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeetingOnlineConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meeting_online_configurations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('online_meeting_app_id')->unsigned()->nullable();
            $table->foreign('online_meeting_app_id')->references('id')->on('online_meeting_apps');
            $table->string('microsoft_azure_app_id')->nullable();
            $table->string('microsoft_azure_tenant_id')->nullable();
            $table->string('microsoft_azure_client_secret')->nullable();
            $table->string('microsoft_azure_user_id')->nullable();
            $table->string('zoom_api_key')->nullable();
            $table->string('zoom_api_secret')->nullable();
            $table->bigInteger('zoom_exp_minutes')->nullable();
            $table->integer('zoom_scheduled_meeting_id')->nullable();
            $table->boolean('zoom_host_video')->default(0);
            $table->boolean('zoom_participant_video')->default(0);
            $table->boolean('zoom_cn_meeting')->default(0);
            $table->boolean('zoom_in_meeting')->default(0);
            $table->boolean('zoom_join_before_host')->default(0);
            $table->boolean('zoom_mute_upon_entry')->default(0); 
            $table->boolean('zoom_water_mark')->default(0);
            $table->boolean('zoom_use_pmi')->default(0);
            $table->string('zoom_audio')->nullable();
            $table->integer('zoom_approval_type')->nullable();
            $table->string('zoom_auto_recording')->nullable();
            $table->boolean('zoom_meeting_authentication')->default(0);
            $table->string('zoom_enforce_login_domains')->nullable();
            $table->string('zoom_alternative_hosts')->nullable();
            $table->boolean('zoom_registrants_email_notification')->default(0);
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
        Schema::dropIfExists('meeting_online_configurations');
    }
}
