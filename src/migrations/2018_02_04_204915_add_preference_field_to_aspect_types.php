<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPreferenceFieldToAspectTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('aspect_types', 'preference_name')) {
            Schema::table('aspect_types', function (Blueprint $table) {
                $table->text('preference_name')->nullable()->default(null);
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
        if (Schema::hasColumn('aspect_types', 'preference_name')) {
            Schema::table('aspect_types', function (Blueprint $table) {
                $table->dropColumn('preference_name');
            });
        }
    }
}
