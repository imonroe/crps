<?php

namespace imonroe\crps;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Subject extends Model
{
    protected $table = 'subjects';

    public function subject_type()
    {
        return SubjectType::where('id', $this->subject_type)->first();
    }

    public function aspects()
    {
        return $this->belongsToMany(Aspect::class);
    }

    public function sorted_aspects()
    {
        $aspect_collection = $this->aspects()->get();
        //dd($aspect_collection);
        $sorted = $aspect_collection->sort(
            function ($a, $b) {
                // sort first by display_weight, then by created_at
                return strcmp($a->display_weight, $b->display_weight)
                    ?: strcmp($a->created_at, $b->created_at);
            }
        );
        return $sorted;
    }

    public function get_jump_menu()
    {
        $output_array = AspectType::get_jump_menu($this->id);
        return $output_array;
    }

    public static function find_by_name($subject_name)
    {
        Log::info('Got a subject name like: '.$subject_name);
        return Subject::where('name', $subject_name)->first();
    }

    public static function exists($potential_subject)
    {
        $output = false;
        if (is_string($potential_subject)) {
            if (!is_null(self::find_by_name($potential_subject))) {
                $output = true;
            }
        }
        return $output;
    }
}
