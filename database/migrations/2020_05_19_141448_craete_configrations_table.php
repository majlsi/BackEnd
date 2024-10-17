<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CraeteConfigrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configrations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('introduction_video_url');
            $table->string('support_email');
            $table->string('mjlsi_system_before_meeting_video_url')->nullable();
            $table->string('explain_create_meeting_video_url')->nullable();
            $table->string('manage_board_meeting_video_url')->nullable();
            $table->string('manage_board_meeting_extra_video_url')->nullable();
            $table->string('introduction_arabic_pdf_url')->nullable();
            $table->string('introduction_english_pdf_url')->nullable();
            $table->string('third_pdf_url')->nullable();
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
        Schema::dropIfExists('configrations');
    }
}
