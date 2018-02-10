<?php

namespace imonroe\crps\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserPreferencesController{

    public $current_user;
    public $available_preferences;


    public function __construct()
    {
        $this->current_user = Auth::user(); 
        $this->available_preferences = app()->make('ApplicationPreferencesRegistry');
    }

    public function get_default_prefs()
    {
        $output = array();
        $app_prefs = $this->available_preferences->get_available_preferences();
        foreach ($app_prefs as $pref){
            $output[ $pref['preference'] ] = $pref['default_value']; 
        }
        return $output;
    }

    public function get_user_prefs()
    {
        $prefs_json = $this->current_user->settings;
        $prefs_array = json_decode($prefs_json, true);
        if (empty($prefs_array)){
            // we haven't saved any preferences yet, so return the default values.
            $prefs_array = $this->get_default_prefs();
        }
        return $prefs_array;
    }

    public function get_preference_form()
    {

        /**
         * $form .= \BootForm::text('title', 'Title');
         * $form .= \BootForm::textarea('aspect_data', 'Aspect Data');
         * $form .= \BootForm::checkbox('hidden', 'Hidden?');
         * $form .= \BootForm::select('aspect_type', array());
         */
        
        $app_prefs = $this->available_preferences->get_available_preferences();
        $user_prefs = $this->get_user_prefs();
        
        $form = \BootForm::horizontal(['url' => '/user/prefs', 'method' => 'post']);
        
        foreach ($app_prefs as $pref){
            switch ($pref['field_type']){
                case 'text':
                    $form .= \BootForm::text($pref['preference'], $pref['preference_label'], $user_prefs[$pref['preference']] );
                    break;
                case 'textarea':
                    $form .= \BootForm::textarea($pref['preference'], $pref['preference_label'], $user_prefs[$pref['preference']]  );
                    break;
                case 'checkbox':
                    $form .= \BootForm::checkbox($pref['preference'], $pref['preference_label'], "true", $user_prefs[$pref['preference']] );
                    break;
                case 'select':
                    $form .= \BootForm::select('aspect_type', ['option_1' => 'Option 1', 'option_2' => 'Option 2'], $user_prefs[$pref['preference']] );
                    break;
                default:
                    break;
            }
        }
        
        $form .= \BootForm::submit('Submit', ['class' => 'btn btn-primary']);
        $form .= \BootForm::close();
        return $form;

    }


    public function update_user_preferences(Request $request){
        $input = $request->all();
        $defaults = $this->get_default_prefs();
        $user_prefs = array();
        foreach ($defaults as $pref_id => $pref_value){
            if ( !empty( $input[$pref_id] ) ){
                // we have some input;
               $user_prefs[$pref_id] = $input[$pref_id];
            } else {
                // no input specified;
                $user_prefs[$pref_id] = $defaults[$pref_id];
            }
        }
        $request->user()->settings = json_encode($user_prefs);
        $request->user()->save();
        $request->session()->flash('message', 'Preferences saved.');
        return redirect('/settings#/profile');
    }

}



