<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use imonroe\crps\Subject;
use imonroe\crps\SubjectType;

class MigrateSubjectTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Let's convert existing SubjectTypes into Subjects.
        $subject_types = SubjectType::all();
        foreach ($subject_types as $st){
          $new_subject = new Subject;
          $new_subject->name = $st->type_name;
          $new_subject->subject_type = -1;
          $new_subject->parent_id = -1;
          $new_subject->description = $st->type_description;
          $new_subject->created_at = $st->created_at;
          $new_subject->updated_at = $st->updated_at;
          $new_subject->save();

          // Update any existing subjects that belong to this type,
          // and update their parent ID

          $subjects = $st->subjects();
          foreach ($subjects as $s){
            $s->parent_id = $new_subject->id;
            $s->save();
          }

        }
        // Now we can drop the subject_type column, since it's redundant.
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('subject_type');
        });

        // We are done with the subject_type table
        Schema::drop('subject_types');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        throw new Exception("Can't undo this migration. It was a breaking change.", 1);
    }
}
