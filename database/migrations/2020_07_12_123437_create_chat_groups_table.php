<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('chat_group_name_ar')->nullable();
            $table->string('chat_group_name_en')->nullable();
            $table->integer('chat_room_id')->unsigned()->nullable();
            $table->integer('creator_id')->unsigned();
            $table->foreign('creator_id')->references('id')->on('users');
            $table->integer('organization_id')->unsigned();
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->integer('chat_group_logo_id')->unsigned()->nullable();
            $table->foreign('chat_group_logo_id')->references('id')->on('images');
            $table->integer('meeting_id')->unsigned()->nullable();
            $table->foreign('meeting_id')->references('id')->on('meetings');
            $table->integer('committee_id')->unsigned()->nullable();
            $table->foreign('committee_id')->references('id')->on('committees');
            $table->string('last_message_text')->nullable();
            $table->dateTime('last_message_date')->nullable();
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
        Schema::dropIfExists('chat_groups');
    }
}
