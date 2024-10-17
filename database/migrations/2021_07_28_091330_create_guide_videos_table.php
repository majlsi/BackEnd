<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuideVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guide_videos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('video_name_ar');
            $table->string('video_name_en')->nullable();
            $table->string('video_description_ar',1000);
            $table->string('video_description_en',1000)->nullable();
            $table->string('video_url');
            $table->integer('video_order')->unsigned();
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
        Schema::dropIfExists('guide_videos');
    }
}
