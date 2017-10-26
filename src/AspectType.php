<?php

namespace imonroe\crps;

use Illuminate\Database\Eloquent\Model;
use imonroe\crps\Aspect;

class AspectType extends Hyn\Tenancy\Abstracts\TenantModel
{
    public function aspects()
    {
        return Aspect::where('aspect_type', '=', $this->id);
    }

    /*
    This function generates an array that can be used as a drop-down list of
    AspectTypes to use in forms, etc.
    */
    public static function get_options_array($format='')
    {
        $all_types = AspectType::all();
        //$output = array('-1' => 'None');
        if ($format == 'json'){
          // We want the output in JSON format
          foreach ($all_types as $t) {
              if ($t->is_viewable) {
                  $output[] = [
                    'value' => $t->id,
                    'label' => $t->aspect_name,
                  ];
              }
          }
          $output = json_encode($output);
        } else {
          // We want the output as a PHP array.
          foreach ($all_types as $t) {
              if ($t->is_viewable) {
                  $output[$t->id] = $t->aspect_name;
              }
          }
        }
        return $output;
    }

    public static function get_jump_menu($subject_id)
    {
        // Here, we will create a jump menu to take us to the right form to create a new aspect from a type.
        $output = '';
        $options_array = self::get_options_array();
        $jump_array = array();
        // unset the "none" option, if it exists.
        $empty_key = '-1';
        if (array_key_exists($empty_key, $options_array)) {
            unset($options_array[$empty_key]);
        }
        // create the menu.
        $output .= '<select id="aspect_type_jump_menu">'.PHP_EOL;
        $output .= '<option value="#"> - Add A New Aspect - </option>';
        foreach ($options_array as $aspect_type_id => $option) {
            $output .= '<option value="/aspect/create/'.$subject_id.'/type/'.$aspect_type_id.'">'.$option.'</option>'.PHP_EOL;
        }
        $output .= '</select>'.PHP_EOL.PHP_EOL;

        $output_array = ['markup' => $output];

        // create the javascript jump behavior
        $output = '<script type="text/javascript">'.PHP_EOL;
        $output .= '$(function(){ $("#aspect_type_jump_menu").change(function(){ window.location.href = $("#aspect_type_jump_menu").val(); }); });'.PHP_EOL;
        $output .= '</script>'.PHP_EOL;

        $output_array['scripts'] = $output;

        return $output_array;
    }
}
