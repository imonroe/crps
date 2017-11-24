<?php

namespace imonroe\crps;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Subject extends Model
{
    protected $table = 'subjects';

    // Make sure we use a global scope, to ensure we only see our
    // own data.
    // https://laravel.com/docs/5.5/eloquent#collections
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new UserScope);
    }

    public function aspects()
    {
        return $this->belongsToMany(Aspect::class);
    }

    /*
      We'll need to know what this Subject's subject_type is.
    */
    public function subject_type(){
      // Subjects with no type have the value set at -1
      // So we'll return a SubjectType object if the id is greater than 0
      // Otherwise, we'll return false
      if ($this->subject_type > 0){
        return SubjectType::where('id', '=', $this->subject_type)->first();
      } else {
        return false;
      }
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

    public function get_jump_menu_json()
    {
        $output_array = AspectType::get_options_array('json');
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

    public function children()
    {
        if (Subject::where('parent_id', '=', $this->id)->count() > 0) {
            return Subject::where('parent_id', '=', $this->id)->orderBy('name')->get();
        } else {
            return false;
        }
    }

    public function has_parent()
    {
        if ($this->parent_id > 0) {
            // we need to return an int here, not an object.
            return $this->parent_id;
        } else {
            return false;
        }
    }

    /*
    * Returns an array representation of all subjects in the database as a tree
    *
    * parameters:
    *
    * $filter_id [int or false] : if you want to filter a subject out of the list (for instance, a subject
    *   may not be its own parent), include the id of the subject you wish to omit.
    * $include_root bool : if TRUE, an additional element will be added to represent the root
    *   subject level, e.g., a subject with no parent.
    *
    * @returns array
    *
    * returned array looks like:
        array:3 [▼
          0 => array:2 [▼
            "value" => "-1"
            "label" => "No Parent"
          ]
          1 => array:2 [▼
            "value" => "3"
            "label" => "Configuration"
          ]
          2 => array:3 [▼
            "value" => "4"
            "label" => "Testing Subjects"
            "children" => array:1 [▼
              0 => array:2 [▼
                "value" => "2"
                "label" => "Let's test aspect types"
              ]
            ]
          ]
        ]
    */
    public static function codex_array( $filter_id=false, $include_root=false ){
      $codex = array();

      if ($include_root){
        $no_parent_option = array('value' => '-1', 'label' => 'No Parent');
        $codex[] = $no_parent_option;
      }

      $root_subjects = Subject::where('parent_id', '=', -1)->get();
      foreach ($root_subjects as $s){
        $rs_dir = $s->directory_array( $filter_id );
        if (!empty($rs_dir)){
          $codex[] = $s->directory_array( $filter_id );
        }
      }

      return $codex;
    }

    /*

      Returns an array of itself and its children, called recursively.
      This function is used by codex_array() to build the full list of subjects.

    */
    public function directory_array( $filter_id=false ){
      // we want an array that looks like:
      // $array['value' => (string)$this->id, 'label' => $this->name, 'children' => array() ];
      //dd( (int)$filter_id);
      //dd($this->id);
      if ( $filter_id == $this->id ){
        return null;
      } else {
        $output = array();
        $output['value'] = (string)$this->id;
        $output['label'] = $this->name;
        $children = $this->children();
        if ($children){
          $child_array = array();
          foreach ($children as $child){
            $child_dir = $child->directory_array($filter_id);
            if (!is_null($child_dir)){
                $child_array[] = $child->directory_array($filter_id);
            }
          }
          if (!empty($child_array)){
            $output['children'] = $child_array;
          }
        }
        return $output;
      }
    }

    /*
      Returns a single-dimensional array of subject ids, including this one, as well as its parents.
    */
    public function parent_subjectids_array(){
      $output = array();
      $parent_id = $this->parent_id;
      if ( $parent_id > 0 ){
        $parent_subject = Subject::where('id', '=', $parent_id)->first();
        //dd($parent_subject);
        //$output[] = $parent_subject->id;
        $parent_array = $parent_subject->parent_subjectids_array();
        $output = array_merge($output, $parent_array);
      }
      $output[] = (string)$this->id;
      return $output;
    }


}
