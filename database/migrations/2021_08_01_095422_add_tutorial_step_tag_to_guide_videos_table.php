<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTutorialStepTagToGuideVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guide_videos', function (Blueprint $table) {
            $table->string('tutorial_step_tag')->after('video_icon_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guide_videos', function (Blueprint $table) {
            $table->dropColumn('tutorial_step_tag');
        });
    }
}
