<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStcEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stc_events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('event_id');
            $table->string('event_type');
            $table->dateTime('creation_date')->nullable();
            $table->string('tenant');
            $table->string('api_version')->nullable();
            $table->json('data')->nullable();
            $table->string('status')->nullable();
            $table->string('error')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stc_events');
    }
}
