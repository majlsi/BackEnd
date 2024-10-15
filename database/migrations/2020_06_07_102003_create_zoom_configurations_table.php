<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZoomConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zoom_configurations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('zoom_api_key');
            $table->string('zoom_api_secret');
            $table->bigInteger('zoom_exp_minutes');
            $table->integer('zoom_scheduled_meeting_id');
            $table->boolean('zoom_host_video')->default(0);
            $table->boolean('zoom_participant_video')->default(0);
            $table->boolean('zoom_cn_meeting')->default(0);
            $table->boolean('zoom_in_meeting')->default(0);
            $table->boolean('zoom_join_before_host')->default(0);
            $table->boolean('zoom_mute_upon_entry')->default(0); 
            $table->boolean('zoom_water_mark')->default(0);
            $table->boolean('zoom_use_pmi')->default(0);
            $table->string('zoom_audio');
            $table->integer('zoom_approval_type');
            $table->string('zoom_auto_recording');
            $table->boolean('zoom_meeting_authentication')->default(0);
            $table->string('zoom_enforce_login_domains');
            $table->string('zoom_alternative_hosts');
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
        Schema::dropIfExists('zoom_configurations');
    }
}
