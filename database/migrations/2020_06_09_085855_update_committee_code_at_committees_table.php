<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCommitteeCodeAtCommitteesTable extends Migration
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
                committees
            SET
                committees.committee_code = "MB"
            WHERE 
                committees.committee_code = "MG"
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('
            UPDATE
                committees
            SET
                committees.committee_code = "MG"
            WHERE 
                committees.committee_code = "MB"
        ');
    }
}
