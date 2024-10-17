<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeColInRegistrationNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->integer('logo_id')->unsigned()->nullable()->change();
            $table->string('organization_code')->nullable()->change();

        });
        DB::statement('ALTER TABLE `organizations` CHANGE `organization_number_of_users` `organization_number_of_users` DOUBLE NULL;');
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
