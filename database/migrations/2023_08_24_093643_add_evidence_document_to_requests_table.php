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
        Schema::table('requests', function (Blueprint $table) {
            //  
            $table->integer('evidence_document_id')->unsigned()->nullable();
            $table->string('evidence_document_url')->nullable();
            $table->foreign('evidence_document_id')->references('id')->on('files');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            //
            $table->dropForeign(['evidence_document_id']);
            $table->dropColumn('evidence_document_id');
        });
    }
};
