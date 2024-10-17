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
        Schema::table('approval_members', function (Blueprint $table) {
            $table->string('signature_x_upper_left')->nullable();
            $table->string('signature_y_upper_left')->nullable();
            $table->integer('signature_page_number')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_members', function (Blueprint $table) {
            $table->string('signature_x_upper_left')->nullable();
            $table->string('signature_y_upper_left')->nullable();
            $table->integer('signature_page_number')->unsigned()->nullable();
        });
    }
};
