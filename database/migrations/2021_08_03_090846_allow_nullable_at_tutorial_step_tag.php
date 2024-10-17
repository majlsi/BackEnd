<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AllowNullableAtTutorialStepTag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guide_videos', function (Blueprint $table) {
            $table->string('tutorial_step_tag')->nullable()->change();
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
            $table->string('tutorial_step_tag')->change();
        });
    }
}
