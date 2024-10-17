<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChatColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('committees', function (Blueprint $table) {
            $table->string('last_message_text')->nullable()->after('chat_room_id');
            $table->dateTime('last_message_date')->nullable()->after('chat_room_id');
        });

        Schema::table('meetings', function (Blueprint $table) {
            $table->string('last_message_text')->nullable()->after('chat_room_id');
            $table->dateTime('last_message_date')->nullable()->after('chat_room_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('committees', function (Blueprint $table) {
            $table->dropColumn('last_message_text');
            $table->dropColumn('last_message_date');
        });

         Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn('last_message_text');
            $table->dropColumn('last_message_date');
        });
    }
}
