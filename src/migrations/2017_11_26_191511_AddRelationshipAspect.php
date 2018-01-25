<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRelationshipAspect extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $aspect_type = new AspectType;
        $aspect_type->aspect_name = 'Relationship';
        $aspect_type->aspect_description = 'Describes a relationship between two Subjects';
        $aspect_type->is_viewable = 1;
        $aspect_type->save();
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
