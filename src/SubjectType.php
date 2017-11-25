<?php

namespace imonroe\crps;

use Illuminate\Database\Eloquent\Model;

class SubjectType extends Model
{
    //

    public static $subject_type_icon = '';
    public static $subject_icon = '<i class="fa fa-file" aria-hidden="true"></i>';

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
        //  DEPRECATED
        $output = '<ul class="subject_type_directory" id="treeData">'.PHP_EOL;
        $top_level_types = SubjectType::where('parent_id', -1)->orderBy('type_name')->get();
        foreach ($top_level_types as $t) {
            $output .= $t->get_html(true);
        }
        $output .= '</ul>'.PHP_EOL;
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

    public static function options_list()
    {
        // DUPLICATE OF ABOVE?
        $all_types = SubjectType::all();
        $output = array('-1' => 'None');
        foreach ($all_types as $t) {
            $output[$t->id] = $t->type_name;
        }
        return $output;
    }



    public function get_html($with_links = false)
    {
        // DEPRECATED

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

    /*
    * Returns an array representation of all subject types in the database as a tree
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
    public static function codex_array( $filter_id=false, $include_root=false, $include_subjects=false ){
      $codex = array();
      // Do we want to include the option of having no subject type?
      if ($include_root){
        $no_parent_option = array('value' => '-1', 'label' => 'No Subject Type');
        $codex[] = $no_parent_option;
      }

      $root_subject_types = SubjectType::where('parent_id', '=', -1)->get();
      foreach ($root_subject_types as $s){
        $rs_dir = $s->directory_array( $filter_id, $include_subjects );
        if (!empty($rs_dir)){
          $codex[] = $s->directory_array( $filter_id, $include_subjects );
        }
      }

      if ($include_subjects){
        $subjects = Subject::where('subject_type', '=', -1)->get();
        foreach ($subjects as $subject){
          $s = [
            'value' => (string)$subject->id,
            'label' => self::$subject_icon . $subject->name,
          ];
          $codex[] = $s;
        }
      }

      return $codex;
    }

    /*

      Returns an array of itself and its children, called recursively.
      This function is used by codex_array() to build the full list of subjects.

    */
    public function directory_array( $filter_id=false, $include_subjects=false ){
      // we want an array that looks like:
      // $array['value' => (string)$this->id, 'label' => $this->name, 'children' => array() ];

      if ( $filter_id == $this->id ){
        return null;
      } else {
        $output = array();
        $st_output = array();

        $st_output['value'] = (string)$this->id;
        $st_output['label'] = $this->subject_type_icon . $this->type_name;

        $children = $this->children();
        if ($children){
          $child_array = array();
          foreach ($children as $child){
            $child_dir = $child->directory_array($filter_id, $include_subjects);
            if (!is_null($child_dir)){
                $child_array[] = $child->directory_array($filter_id, $include_subjects);
            }
          }
          if (!empty($child_array)){
            $st_output['children'] = $child_array;
          }
        }

        if ($include_subjects){
          $output[] = $st_output;
          $subjects = $this->subjects();
          foreach ($subjects as $subject){
            $s = [
              'value' => (string)$subject->id,
              'label' => $this->subject_icon . $subject->name
            ];
            $output[] = $s;
          }
          return $output;
        } else {
          return $st_output;
        }
      }
    }

    /*
      Returns a single-dimensional array of subject type ids, including this one, as well as its parents.
    */
    public function parent_subject_type_ids_array(){
      $output = array();
      $parent_id = $this->parent_id;
      if ( $parent_id > 0 ){
        $parent_subject_type = SubjectType::where('id', '=', $parent_id)->first();
        //dd($parent_subject);
        //$output[] = $parent_subject->id;
        $parent_array = $parent_subject_type->parent_subject_type_ids_array();
        $output = array_merge($output, $parent_array);
      }
      $output[] = (string)$this->id;
      return $output;
    }


}
