<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrpsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Main Tables

        if (!(Schema::hasTable('aspects')) ) {
            Schema::create(
                'aspects', function (Blueprint $t) {
                    $t->increments('id');
                    $t->integer('aspect_type');
                    $t->text('title')->nullable();
                    $t->longText('aspect_data')->nullable();
                    $t->mediumText('aspect_notes')->nullable();
                    $t->text('aspect_source')->nullable();
                    $t->integer('hidden')->nullable();
                    $t->integer('folded')->nullable();
                    $t->integer('display_weight')->nullable()->default(100);
                    $t->dateTime('last_parsed')->nullable();
                    $t->timestamps();
                }
            );
        }

        if (!(Schema::hasTable('aspect_subject')) ) {
            Schema::create(
                'aspect_subject', function (Blueprint $t) {
                    $t->increments('id');
                    $t->integer('aspect_id');
                    $t->integer('subject_id');
                    $t->timestamps();
                }
            );
        }

        if (!(Schema::hasTable('aspect_types')) ) {
            Schema::create(
                'aspect_types', function (Blueprint $t) {
                    $t->increments('id');
                    $t->string('aspect_name');
                    $t->text('aspect_description')->nullable();
                    $t->integer('is_viewable')->nullable();
                    $t->timestamps();
                }
            );
        }

        if (!(Schema::hasTable('subjects')) ) {
            Schema::create(
                'subjects', function (Blueprint $t) {
                    $t->increments('id');
                    $t->string('name');
                    $t->integer('parent_id')->default(-1);
                    $t->text('description')->nullable();
                    $t->timestamps();
                }
            );
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('subjects');
        Schema::drop('aspects');
        Schema::drop('aspect_subjects');
        Schema::drop('aspect_types');
    }

}
