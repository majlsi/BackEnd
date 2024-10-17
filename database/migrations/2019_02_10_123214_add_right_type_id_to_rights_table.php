<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRightTypeIdToRightsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rights', function (Blueprint $table) {
            $table->integer('right_type_id')->unsigned()->nullable()->after('icon');
            $table->foreign('right_type_id')->references('id')->on('right_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rights', function (Blueprint $table) {
            $table->dropForeign('right_type_id');
            $table->dropIndex('right_type_id');
            $table->dropColumn('right_type_id');
        });
    }
}
