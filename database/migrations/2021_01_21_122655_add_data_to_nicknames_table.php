<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataToNicknamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            INSERT INTO nicknames (nickname_ar,nickname_en,is_system)
            VALUES
                ('عضو تنفيذي','Executive member',1),
                ('عضو غير تنفيذي','Non-executive member',1),
                ('عضو مستقل','Independent member',1);
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
