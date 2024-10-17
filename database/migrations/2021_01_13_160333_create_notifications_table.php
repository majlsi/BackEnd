<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('notification_title_ar');
            $table->string('notification_title_en');
            $table->string('notification_body_ar');
            $table->string('notification_body_en');
            $table->string('notification_icon');
            $table->string('notification_url');
            $table->string('notification_model_type');
            $table->bigInteger('notification_model_id')->unsigned();
            $table->timestamp('notification_date');
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
        Schema::dropIfExists('notifications');
    }
}
