<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('approval_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('approval_status_name_ar');
            $table->string('approval_status_name_en');
            $table->timestamps();
            $table->softDeletes();
        });
        $statuses = [
            [
                'approval_status_name_ar' => 'جديدة',
                'approval_status_name_en' => 'New',
            ],
            [
                'approval_status_name_ar' => 'بانتظار التوقيع',
                'approval_status_name_en' => 'Awaiting signature',
            ],
            [
                'approval_status_name_ar' => 'مكتمل',
                'approval_status_name_en' => 'Completed',
            ],
            [
                'approval_status_name_ar' => 'تم التوقيع',
                'approval_status_name_en' => 'Accepted',
            ],
            [
                'approval_status_name_ar' => 'تم الرفض',
                'approval_status_name_en' => 'Rejected',
            ]
        ];

        DB::table("approval_statuses")->insert($statuses);

        Schema::create('approvals', function (Blueprint $table) {
            $table->increments('id');
            $table->string("approval_title")->nullable();
            $table->integer('committee_id')->unsigned()->nullable();
            $table->foreign('committee_id')->references('id')->on('committees');
            $table->integer('created_by')->unsigned()->nullable();
            $table->foreign('created_by')->references('id')->on('users');
            $table->integer('status_id')->unsigned()->nullable();
            $table->foreign('status_id')->references('id')->on('approval_statuses');
            $table->string('attachment_url');
            $table->string('attachment_name');
            $table->integer('organization_id')->unsigned()->nullable();
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->integer('file_id')->unsigned()->nullable();
            $table->foreign('file_id')->references('id')->on('files');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('approval_members', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('approval_id')->unsigned()->nullable();
            $table->foreign('approval_id')->references('id')->on('approvals');
            $table->integer('member_id')->unsigned()->nullable();
            $table->foreign('member_id')->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_members');
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('approval_statuses');
    }
};
