<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            "INSERT INTO `agenda_participants`(`user_id`, `meeting_agenda_id`)
            SELECT `agenda_presenters`.`user_id`, `agenda_presenters`.`meeting_agenda_id` 
            FROM `agenda_presenters`
            WHERE `agenda_presenters`.`deleted_at` IS NULL"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('agenda_participants')->truncate();
    }
};
