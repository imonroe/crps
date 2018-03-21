<?php
namespace imonroe\crps;

class ApplicationPreferencesRegistry {
    
    /**
     * Ok, so what should this array look like?
     * 
     * preference => some_preference_name
     * preferece_label => 'Some Preference Label'
     * field_type => textfield, dropdown, checkbox, textarea
     * default_value => some_value
     * options => ['value' => 'Value label']
     * 
     * 
     */

    public $available_preferences;

    public function __construct()
    {
        $this->available_preferences = array();
    }

    public function register_preference(Array $pref = array())
    {
        
        if (!empty($pref)){
            $this->available_preferences[ $pref['preference'] ] = $pref;
            return true;
        } else {
            return false;
        }

    }

    public function get_available_preferences($format='array')
    {
        switch($format){
            case 'array':
                return $this->available_preferences;
                break;
            case 'json':
                return json_encode($this->available_preferences);
                break;
            default: 
                return false;
                break;
        }
    }

}
