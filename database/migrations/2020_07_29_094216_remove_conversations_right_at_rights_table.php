<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveConversationsRightAtRightsTable extends Migration
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
                role_rights.right_id IN (76,77)
        ');
        DB::statement('
            UPDATE
                rights
            SET
                rights.deleted_at = now()
            WHERE 
                rights.id IN (76,77)
        ');
        DB::statement('
            UPDATE
                modules
            SET
                modules.deleted_at = now()
            WHERE 
                modules.id = 9
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
