<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubjectType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!(Schema::hasTable('subject_types'))) {
            Schema::create(
                'subject_types',
                function (Blueprint $t) {
                    $t->increments('id');
                    $t->string('type_name');
                    $t->text('type_description')->nullable();
                    $t->integer('parent_id')->nullable();
                    $t->integer('user');
                    $t->timestamps();
                }
            );
        }


        if (Schema::hasTable('subjects')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->integer('subject_type')->default(-1);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // We are done with the subject_type table
        Schema::drop('subject_types');

        if (Schema::hasTable('subjects')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->dropColumn('subject_type');
            });
        }
    }
}
