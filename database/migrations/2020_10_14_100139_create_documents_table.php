<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('document_subject_ar')->after('organization_id');
            $table->string('document_subject_en')->nullable()->after('document_subject_ar');
            $table->string('document_description_ar',3000)->after('document_subject_en');
            $table->string('document_description_en',3000)->nullable()->after('document_description_ar');
            $table->integer('committee_id')->unsigned()->after('document_name');
            $table->foreign('committee_id')->references('id')->on('committees');
            $table->integer('document_status_id')->unsigned()->after('committee_id');
            $table->foreign('document_status_id')->references('id')->on('document_statuses');
            $table->timestamp('review_start_date')->nullable()->after('document_status_id');
            $table->timestamp('review_end_date')->nullable()->after('review_start_date');
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
