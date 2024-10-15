<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMainCommitteeDataToCommitteesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            INSERT INTO committees (committee_name_ar,committee_name_en,committeee_members_count,committee_code,is_system)
            VALUES
                ('".config('mainCommitteeData.nameAr')."','".config('mainCommitteeData.nameEn')."',0,'".config('mainCommitteeData.code')."',1);
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
