<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataToCommitteesTable extends Migration
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
                ('لجنة المكافآت','Remuneration committee',0,'RC',1),
                ('لجنة الترشيحات','Nominations committee',0,'NC',1),
                ('لجنة المراجعة','Review committee',0,'RVC',1);
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
