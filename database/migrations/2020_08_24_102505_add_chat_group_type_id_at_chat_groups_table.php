<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChatGroupTypeIdAtChatGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_groups', function (Blueprint $table) {
            $table->integer('chat_group_type_id')->unsigned()->nullable();
            $table->foreign('chat_group_type_id')->references('id')->on('chat_group_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chat_groups', function (Blueprint $table) {
            $table->dropForeign('chat_group_type_id');
            $table->dropIndex('chat_group_type_id');
            $table->dropColumn('chat_group_type_id');
        });
    }
}
