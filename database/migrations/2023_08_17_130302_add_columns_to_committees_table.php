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
        Schema::table('committees', function (Blueprint $table) {
            $table->integer('decision_number')->after('last_message_text')->nullable();
            $table->dateTime('decision_date')->after('decision_number')->nullable();
            $table->integer('decision_responsible_user_id')->unsigned()->nullable();
            $table->integer('committee_status_id')->unsigned()->nullable();
            $table->integer('decision_document_id')->unsigned()->nullable();
            $table->string('decision_document_url')->nullable();
            $table->integer('committee_type_id')->unsigned()->nullable();
            $table->string('committee_reason',1000)->nullable();
            $table->foreign('decision_document_id')->references('id')->on('files');
            $table->foreign('committee_status_id')->references('id')->on('committee_statuses');
            $table->foreign('decision_responsible_user_id')->references('id')->on('users');
            $table->foreign('committee_type_id')->references('id')->on('committee_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('committees', function (Blueprint $table) {
            $table->dropForeign(['committee_status_id']);
            $table->dropForeign(['decision_responsible_user_id']);
            $table->dropForeign(['committee_type_id']);
    
            $table->dropColumn('decision_number');
            $table->dropColumn('decision_date');
            $table->dropColumn('decision_responsible_user_id');
            $table->dropColumn('committee_status_id');
            $table->dropColumn('decision_document_url');
            $table->dropColumn('committee_type_id');
            $table->dropColumn('committee_reason');
        });
    }
};
