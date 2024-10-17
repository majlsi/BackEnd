<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteMeetingTypeRouteFromRightsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('
            UPDATE
                role_rights
            SET
                role_rights.deleted_at = now()
            WHERE 
                role_rights.right_id IN (16,17,18,39)
        ');
        DB::statement('
            UPDATE
                rights
            SET
                rights.deleted_at = now()
            WHERE 
                rights.id IN (16,17,18,39)
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rights', function (Blueprint $table) {
            //
        });
    }
}
