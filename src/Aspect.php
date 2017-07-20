<?php
namespace imonroe\crps;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use GrahamCampbell\Markdown\Facades\Markdown;
use Carbon\Carbon;

class Aspect extends Model
{
	protected $table = 'aspects';
	protected $fillable = ['aspect_type'];
	protected $keep_history = true; 

	/**
     * The attributes that should be cast to native types.
     * The aspect_notes field will hold an array of settings, notes, whatever metadata you want.
     * @var array
	*/

    protected $casts = [
        'aspect_notes' => 'array',
    ];


	public function __construct(){
		//$this->aspect_notes = $this->notes_schema();
	}

	/**
		If you want to save metadata fields for this array, just set up the schema 	
		you want to use here.  When the Aspect is saved, the array will be JSON-ified 
		and saved as aspect_notes

		Returns JSON.
	*/
	public function notes_schema(){
		return null;
	}

	public function isSubclass(){
		if ( is_subclass_of($this, 'Aspect') ){
			return true;
		} else {
			return false;
		}
    }

	// override the base Model function
	public function newFromBuilder($attributes = array(), $connection=NULL){
		$aspect_type = $attributes->aspect_type;
		if ((int)$aspect_type > 0){
			$instance = AspectFactory::make_from_aspect_type($aspect_type);
		} else {
			$instance = $this->newInstance([], true);
		}
		$instance->exists = true;
		$instance->setRawAttributes((array) $attributes, true);
		$instance->setConnection($connection ?: $this->getConnectionName());
		return $instance;
	}

	/**
     * Create a new Eloquent Collection instance.
     *  This lets us say, for instance, any time you're retrieving Aspects in an eloquent collection, 
	 *  they will be recast in to the correct Aspect types before being returned.
	 *
     * @param  array  $models
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function newCollection(array $models = []){
        return new AspectCollection($models);
    }

	public function manual_load(){
		// We anticipate here that we have an empty model, with just the ID set.
		$raw_aspect_data = DB::select('select * from aspects where id = :id', ['id' => $this->id]);
		foreach ($raw_aspect_data as $a_data){
			$this->title = $a_data->title;
			$this->aspect_type = $a_data->aspect_type;
			$this->aspect_data = $a_data->aspect_data;
			$this->predicted_accuracy = $a_data->predicted_accuracy;
			$this->aspect_notes = $a_data->aspect_notes;
			$this->aspect_source = $a_data->aspect_source;
			$this->hidden = $a_data->hidden;
			$this->hash = $a_data->hash;
			$this->simhash = $a_data->simhash;
			$this->last_parsed = $a_data->last_parsed;
			$this->created_at = $a_data->created_at;
			$this->updated_at = $a_data->updated_at;
			$this->display_weight = $a_data->display_weight;
		}
	}

	public function save_snapshot(){
		$snapshot = $this->toJson();
		DB::table('aspect_history')->insert(
    		['aspect_id' => $this->id, 'snapshot' => $snapshot, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(),]
		);
	}

	public function get_history(){
		$history = array();
		$snapshots= DB::table('aspect_history')->where('aspect_id', '=', $this->id)->orderBy('created_at', 'desc')->get();
		foreach ($snapshots as $snapshot){
			$new_aspect = json_decode($snapshot->snapshot);
			$history[] = $new_aspect;
		}
		$collection = collect($history);
		return $collection;
	}

	public function update_aspect(){
		$this->exists = true;

		if (is_array($this->aspect_notes)){
			$settings = $this->aspect_notes;
		} else {
			$settings = (!is_null($this->aspect_notes)) ? json_decode($this->aspect_notes, true) : json_decode($this->notes_schema(), true);
		}
		$this->aspect_notes = $settings;
		$this->hash = $this->get_hash();
		if ($this->keep_history){
			$this->save_snapshot();
		}
		$this->save();
	}

    public function aspect_type(){
		return AspectType::where('id', '=', $this->aspect_type)->first();
	}

	public function notes_fields(){
		$schema = json_decode( $this->notes_schema(), true );
		$output = '';
		$settings_array = (!is_null($this->aspect_notes)) ? json_decode($this->aspect_notes, true) : $schema;
		if ( !empty($settings_array) ){
			$output .= '<fieldset class="small">'.PHP_EOL;
			$output .= '<legend>Settings</legend>'.PHP_EOL;
			foreach($settings_array as $name => $value){
				if (is_array($schema[$name])){
					// we want this to be a dropdown list
					$output .= \Form::label('settings_'.$name, $name.': ');
					$output .= \Form::select('settings_'.$name, $schema[$name], $value);
					$output .= '<br />';
				} else {
					// just a text field, please.
					$output .= \Form::label('settings_'.$name, $name.': ');
					$output .= \Form::text('settings_'.$name, $value);
					$output .= '<br />';
				}
			}
			$output .= '</fieldset>';
		}
		return $output;
	}

	public function create_form($subject_id, $aspect_type_id=null){
		$form = \Form::open(['url' => '/aspect/create', 'method' => 'post', 'files' => true]);
		$form .= \Form::hidden('subject_id', $subject_id);

		if (!is_null($aspect_type_id)){
			$form .= \Form::hidden( 'aspect_type', $aspect_type_id );
		} else {
			$form .= '<p>';
			$form .= \Form::label( 'aspect_type', 'Aspect Type: ' );
			$form .= \Form::select( 'aspect_type', AspectType::get_options_array() );
			$form .= ' (<a href="/aspect_type/create">Add a new Aspect Type</a>)';
			$form .= '</p>';
		}
		
		$form .= '<p>';
		$form .= \Form::label('title', 'Title: ');
		$form .= \Form::text('title');
		$form .= '</p>';

		$form .= '<p>';
		$form .= \Form::label( 'aspect_data', 'Aspect Data: ' );
		$form .= '<br />';
		$form .= \Form::textarea('aspect_data');
		$form .= '</p>';

		$form .= '<p>';
		$form .= \Form::label('predicted_accuracy', 'Predicted Accuracy: ');
		$form .= \Form::number('predicted_accuracy');
		$form .= '</p>';

		$form .= '<p>';
		$form .= \Form::label('aspect_source', 'Source: ');
		$form .= \Form::text('aspect_source');
		$form .= '</p>';

		$form .= '<p>';
		$form .= \Form::label('hidden', 'Hidden?: ');
		$form .= \Form::checkbox('hidden', '1');
		$form .= '</p>';

		$form .= '<p>';
		$form .= \Form::label('file_upload', 'File Upload: ');
		$form .= \Form::file('file_upload');
		$form .= '</p>';

		$form .= $this->notes_fields();

		$form .= '<p>' . \Form::submit('Submit', ['class' => 'btn btn-primary']) . '</p>';
		$form .= \Form::close();
        return $form;
	}

	public function edit_form($id){
		$aspect = Aspect::find($id);
        $form = '';
		$form .= \Form::open(['url' => '/aspect/'.$id.'/edit', 'method' => 'post']);
		$form .= \Form::hidden('aspect_id', $id);
		$form .= \Form::hidden( 'aspect_type', $aspect->aspect_type()->id );
		
		$form .= '<p>';
		$form .= \Form::label('title', 'Title: ');
		$form .= \Form::text('title', $aspect->title);
		$form .= '</p>';

		$form .= '<p>';
		$form .= \Form::label( 'aspect_data', 'Aspect Data: ' );
		$form .= '<br />';
		$form .= \Form::textarea('aspect_data', $aspect->aspect_data);
		$form .= '</p>';

		$form .= '<p>';
		$form .= \Form::label('predicted_accuracy', 'Predicted Accuracy: ');
		$form .= \Form::number('predicted_accuracy',  $aspect->predicted_accuracy);
		$form .= '</p>';

		$form .= '<p>';
		$form .= \Form::label('aspect_source', 'Source: ');
		$form .= \Form::text('aspect_source', $aspect->aspect_source);
		$form .= '</p>';

		$form .= '<p>';
		$form .= \Form::label('hidden', 'Hidden?: ');
		if ( $aspect->hidden){
			$form .= \Form::checkbox('hidden', '1', true);
		} else {
			$form .= \Form::checkbox('hidden', '1');
		}
		$form .= '</p>';

		$form .= '<p>';
		$form .= \Form::label('file_upload', 'File Upload: ');
		$form .= \Form::file('file_upload');
		$form .= '</p>';

		$form .= $aspect->notes_fields();

		$form .= '<p>' . \Form::submit('Submit', ['class' => 'btn btn-primary']) . '</p>';
		$form .= \Form::close();
        return $form;
	}

	public function subjects(){
		return $this->belongsToMany(Subject::class, 'aspect_subject', 'aspect_id', 'subject_id');
	}

	public function get_hash(){
		return md5($this->aspect_data);
	}

	public function display_aspect(){
		$output = '<div class="aspect_type-'.$this->aspect_type()->id.'">';
		if ($this->aspect_type()->markdown){
			$output .= Markdown::convertToHtml($this->aspect_data);
		} else {
			$output .= $this->aspect_data;
		}
		$output .= '</div>';
		return $output;
	}

	public function parse(){
		//
	}
	
	/* 
	   Below, we're going to prototype some pre- and post-save hooks that can be overridden by child
	   classes, such that we can do some manipulations on the data before we save it if we want.
	   They get called in the AspectController in the relevant places.
	*/

	public function pre_save(Request $request){ return false; }
	public function post_save(Request $request){ return false; }
	public function pre_update(Request $request){ return false; }
	public function post_update(Request $request){ return false; }
	public function pre_delete(Request $request){ return false; }


} // End of base Aspect Class.

////////////////////////////////////////////////////////////////////////////////////////////////////
/*
	Below, you'll find supplementary classes to make Aspects work correctly within Eloquent ORM
	We want a custom Collction object that will correctly instantiate custom aspect classes
	We also want a factory to pump out aspects of various types.
*/
class AspectCollection extends \Illuminate\Database\Eloquent\Collection {
	public function __construct($items){
        parent::__construct($items);
        $this->recastAll();
    }

    private function recastAll(){
		$new_collection_array = array();
		foreach ($this->items as $m){
			$new_object = AspectFactory::make($m->id);
			$new_collection_array[] = $new_object;
		}
		$this->items = $new_collection_array;
    }
}
/////////////////////////////////////////////////////////////////////////////////////////////////////
class AspectFactory{
	/* This function takes an aspect_id, creates the proper custom aspect class, and loads it from the DB. */
	public static function make($aspect_id){
		// Lets try to get an aspect_type name to use for a class.
		$new_classname = 'App\DefaultAspect';  // <--- if we don't have a custom type, we'll use this as our default.
		$new_type_name = DB::select('SELECT aspect_name FROM aspect_types where id IN (SELECT aspect_type FROM aspects where id = :id) LIMIT 1', ['id' => $aspect_id]); 
		$mutated_aspect_type = $new_type_name[0]->aspect_name;
		$classname = Ana::code_safe_name($mutated_aspect_type);
		$classname = 'App\\' . $classname . 'Aspect';
		if ( class_exists( $classname ) ){
			$new_classname = $classname;
		}
		$finder = new $new_classname();
		$finder->id = $aspect_id;
		$finder->manual_load();
		return $finder;
	}

	/* This function creates a new, empty custom aspect by the aspect_type_id */
	public static function make_from_aspect_type($aspect_type_id){
		$new_classname = 'App\DefaultAspect';  // <--- if we don't have a custom type, we'll use this as our default.
		$new_type_name = DB::select('SELECT aspect_name FROM aspect_types where id = :id LIMIT 1', ['id' => $aspect_type_id]); 
		$mutated_aspect_type = $new_type_name[0]->aspect_name;
		$classname = Ana::code_safe_name($mutated_aspect_type);
		$classname = 'App\\' . $classname . 'Aspect';

		if ( class_exists( $classname ) ){
			$new_classname = $classname;
		}
		$finder = new $new_classname();
		$finder->aspect_type = $aspect_type_id;
		return $finder;
	}
}
/////////////////////////////////////////////////////////////////////////////////////////////////////