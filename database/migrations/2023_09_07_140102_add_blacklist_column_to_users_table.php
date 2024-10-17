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
        Schema::table('users', function (Blueprint $table) {
            //
            $table->boolean('is_blocked')->after('organization_id')->default(false);
            $table->integer('blacklist_file_id')->after('is_blocked')->unsigned()->nullable();
            $table->text('blacklist_reason')->after('blacklist_file_id')->nullable(); 
            $table->foreign('blacklist_file_id')->references('id')->on('files');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('is_blocked');
            $table->dropForeign(['blacklist_file_id']);
            $table->dropColumn('blacklist_reason');
        });
    }
};
