<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAspectSizeColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('aspects', 'size')) {
            Schema::table('aspects', function (Blueprint $table) {
                $table->integer('size')->default(1);
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
        if (Schema::hasColumn('aspects', 'size')) {
            Schema::table('aspects', function (Blueprint $table) {
                $table->dropColumn('size');
            });
        }
    }
}
