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
        Schema::create('requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('request_type_id')->unsigned(); 
            $table->json('request_body');
            $table->integer('created_by')->unsigned();
            $table->integer('approved_by')->unsigned()->nullable();
            $table->integer('rejected_by')->unsigned()->nullable();
            $table->boolean('is_approved')->nullable();
            $table->foreign('request_type_id')->references('id')->on('request_types');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->foreign('rejected_by')->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
