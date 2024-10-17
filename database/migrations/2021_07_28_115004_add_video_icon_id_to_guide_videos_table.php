<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVideoIconIdToGuideVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guide_videos', function (Blueprint $table) {
            $table->integer('video_icon_id')->unsigned()->nullable()->after('video_url');
            $table->foreign('video_icon_id')->references('id')->on('video_icons');        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guide_videos', function (Blueprint $table) {
            $table->dropForeign('video_icon_id');
            $table->dropIndex('video_icon_id');
            $table->dropColumn('video_icon_id');
        });
    }
}
