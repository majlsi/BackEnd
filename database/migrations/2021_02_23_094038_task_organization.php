<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TaskOrganization extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('task_management', function (Blueprint $table) {
            //
            $table->integer('organization_id')->unsigned()->nullable();
            $table->foreign('organization_id')->references('id')->on('organizations');
        });
        DB::statement("UPDATE task_management
        INNER JOIN  meetings ON task_management.meeting_id = meetings.id
        SET 
        task_management.organization_id = meetings.organization_id 
        where task_management.id  !=  0;");
        DB::statement("UPDATE task_management
        INNER JOIN  users ON task_management.created_by = users.id
        SET 
        task_management.organization_id = users.organization_id 
        where task_management.id  !=  0;");
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('task_management', function (Blueprint $table) {
            //
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }
}
