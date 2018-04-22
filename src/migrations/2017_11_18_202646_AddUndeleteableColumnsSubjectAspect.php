<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUndeleteableColumnsSubjectAspect extends Migration
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
                $table->integer('editable')->default(1);
            });
        }

        if (Schema::hasTable('subjects')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->integer('editable')->default(1);
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
            $table->dropColumn('editable');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('editable');
        });
    }
}
