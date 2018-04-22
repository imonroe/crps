<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserfieldsForCrps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (Schema::hasTable('aspects')) {
            Schema::table('aspects', function (Blueprint $table) {
                $table->integer('user');
            });
        }

        if (Schema::hasTable('subjects')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->integer('user');
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

        Schema::table('aspects', function (Blueprint $table) {
            $table->dropColumn('user');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('user');
        });
    }
}
