<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use imonroe\crps\Subject;
use imonroe\crps\SubjectType;

class RemovingSubjectTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      // Add the new fields to the subjects table
      Schema::table('subjects', function (Blueprint $table) {
        $table->integer('parent_id')->default(-1);
        $table->text('description')->nullable();
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
