<?php

namespace imonroe\crps;

use Illuminate\Database\Eloquent\Model;

class SubjectType extends Model
{
    //

    // Make sure we use a global scope, to ensure we only see our
    // own data.
    // https://laravel.com/docs/5.5/eloquent#collections
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new UserScope);
    }
    

    public static function directory()
    {
        $output = '<ul class="subject_type_directory" id="treeData">'.PHP_EOL;
        $top_level_types = SubjectType::where('parent_id', -1)->orderBy('type_name')->get();
        foreach ($top_level_types as $t) {
            $output .= $t->get_html(true);
        }
        $output .= '</ul>'.PHP_EOL;
        return $output;
    }

    public static function options_list()
    {
        $all_types = SubjectType::all();
        $output = array('-1' => 'None');
        foreach ($all_types as $t) {
            $output[$t->id] = $t->type_name;
        }
        return $output;
    }

    public function subjects()
    {
        //return $this->belongsToMany(Subject::class);
        return Subject::where('subject_type', '=', $this->id)->orderBy('name')->get();
    }

    // We want a recursive function here, so we can get additional subject that are of child subject types
    public function get_all_subjects()
    {
        $subjects = $this->subjects();
        $children = $this->children();
        if ($children) {
            foreach ($children as $child) {
                $sub_subjects = $child->get_all_subjects();
                $merged = $subjects->merge($sub_subjects);
                $subjects = $merged;
            }
        }
        $output = $subjects->sortBy('name');
        return $output;
    }

    public function children()
    {
        if (SubjectType::where('parent_id', '=', $this->id)->count() > 0) {
            return SubjectType::where('parent_id', '=', $this->id)->orderBy('type_name')->get();
        } else {
            return false;
        }
    }

    public function has_parent()
    {
        if ($this->parent_id > 0) {
            // we need to return an int here, not an object.
            return $this->parent;
        } else {
            return false;
        }
    }

    public static function get_options_array()
    {
        $output = array();
        $unsorted = SubjectType::all();
        $all_types = $unsorted->sortBy('type_name');
        foreach ($all_types as $t) {
            $output[$t->id] = $t->type_name;
        }
        return $output;
    }

    public function get_html($with_links = false)
    {
        $output = '<li>';
        if ($with_links) {
            $output .= '<a href="/subject_type/'.$this->id.'">';
        }
        $output .= '<strong>'.$this->type_name.'</strong>';
        if ($with_links) {
            $output .= '</a>';
        }
        //$output .= '</li>'.PHP_EOL;
        $output .= PHP_EOL;

        if ($this->children()) {
            // Recurse.
            $output .= '<ul>'.PHP_EOL;
            foreach ($this->children() as $child) {
                $output .= $child->get_html($with_links);
            }
            $output .= '</ul>'.PHP_EOL;
        }
        return $output;
    }
}
