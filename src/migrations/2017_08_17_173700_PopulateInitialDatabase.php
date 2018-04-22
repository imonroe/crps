<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PopulateInitialDatabase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      // Create the basic Aspect Types
        DB::table('aspect_types')->insert([
        ['aspect_name' => 'File Upload', 'aspect_description' => 'An uploaded file', 'is_viewable' => 1 ],
        ['aspect_name' => 'Image', 'aspect_description' => 'An image file', 'is_viewable' => 1 ],
        ['aspect_name' => 'Unformatted Text', 'aspect_description' => 'An unformatted blob of text', 'is_viewable' => 1 ],
        ['aspect_name' => 'Markdown Text', 'aspect_description' => 'Markdown-formatted text', 'is_viewable' => 1 ],
        ['aspect_name' => 'Formatted Text', 'aspect_description' => 'HTML-formatted text', 'is_viewable' => 1 ],
        ['aspect_name' => 'Lambda Function', 'aspect_description' => 'An aspect that performs some sort of calculation or function, rather than storing data', 'is_viewable' => 0 ],
        ]);
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
