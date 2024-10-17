<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSectionsColsToMomTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mom_templates', function (Blueprint $table) {
            $table->string('sign_template_en',1000)->after('show_conclusion')->nullable();
            $table->string('sign_template_ar',1000)->after('show_conclusion')->nullable();

            $table->string('conclusion_template_en',1000)->after('show_conclusion')->nullable();
            $table->string('conclusion_template_ar',1000)->after('show_conclusion')->nullable();

            $table->string('agenda_template_en',1000)->after('show_conclusion')->nullable();
            $table->string('agenda_template_ar',1000)->after('show_conclusion')->nullable();

            $table->string('member_list_template_en',1000)->after('show_conclusion')->nullable();
            $table->string('member_list_template_ar',1000)->after('show_conclusion')->nullable();

            $table->string('member_list_introduction_template_en',1000)->after('show_conclusion')->nullable();
            $table->string('member_list_introduction_template_ar',1000)->after('show_conclusion')->nullable();

            $table->string('introduction_template_en',1000)->after('show_conclusion')->nullable();
            $table->string('introduction_template_ar',1000)->after('show_conclusion')->nullable();
           

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
