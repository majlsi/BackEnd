<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableNicknames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nicknames', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nickname_ar');
            $table->string('nickname_en')->nullable();
            $table->integer('organization_id')->unsigned();
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->timestamps();
            $table->softDeletes();           
        });
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
