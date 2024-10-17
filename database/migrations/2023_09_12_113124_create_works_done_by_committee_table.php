<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('works_done_by_committee', function (Blueprint $table) {
            $table->increments('id');
            $table->string('work_done');
            $table->integer('committee_id')->unsigned()->nullable();
            $table->foreign('committee_id')->references('id')->on('committees');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('works_done_by_committee');
    }
};
