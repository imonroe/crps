<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\User;
use imonroe\crps\Subject;

class AddHiddenSubjectField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('subjects', 'hidden')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->boolean('hidden')->default(false);
            });

            // Let's create the CachedAspects subject, and set it to hidden.
            $all_users = User::all();
            foreach ($all_users as $user) {
                $cache = new Subject;
                $cache->name = 'CachedAspects';
                $cache->subject_type = -1;
                $cache->user = $user->id;
                $cache->editable = 0;
                $cache->hidden = 1;
                $cache->description = 'A hidden subject for holding cached data from APIs, etc.';
                $cache->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('subjects', 'hidden')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->dropColumn('hidden');
            });
        }
    }
}
