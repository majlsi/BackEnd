<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSerialNumberToTaskManagementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('task_management', function (Blueprint $table) {
            $table->string('serial_number')->after('number_of_days');
            $table->integer('task_sequence')->unsigned()->after('serial_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('task_management', function (Blueprint $table) {
            $table->dropColumn('serial_number');
            $table->dropColumn('task_sequence');
        });
    }
}
