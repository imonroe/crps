<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use imonroe\crps\Aspect;
use imonroe\crps\Ana;

// ---------  Begin Custom Aspects -------------- //

/*
Hey, you're going to see a line at the end of this file that looks like:

// ------------------------------------------- //

DO NOT REMOVE OR MODIFY THAT LINE.  IT IS THE FIND AND REPLACE TOKEN THAT ADDS NEW CUSTOM ASPECT CODE.
IF YOU REMOVE OR CHANGE IT, YOU WILL BREAK THE APP.
*/

/*  -- Basic Aspect Types -- */

class DefaultAspect extends Aspect{
	public function __construct(){
		parent::__construct();
		// e.g., $this->keep_history = false; 
	}
	public function notes_schema(){
		$schema = json_decode(parent::notes_schema(), true);
		// e.g., $schema['webpage_url'] = '';
		return json_encode($schema);
	}

	public function create_form($subject_id, $aspect_type_id=null){
		return parent::create_form($subject_id, $aspect_type_id);
	}

	public function edit_form($id){
		return parent::edit_form($id);
	}

	public function display_aspect(){
		$output = parent::display_aspect();
		return $output;
	}
	public function parse(){
		$output = parent::parse();
		return $output;
	}
}

class FileUploadAspect extends Aspect{
	public function create_form($subject_id, $aspect_type_id=null){
		$form = \Form::open(['url' => '/aspect/create', 'method' => 'post', 'files' => true]);
		$form .= \Form::hidden('subject_id', $subject_id);
		$form .= \Form::hidden('aspect_type', $aspect_type_id );
		$form .= '<p>';
		$form .= \Form::label('title', 'Title: ');
		$form .= \Form::text('title');
		$form .= '</p>';
		$form .= '<p>';
		$form .= \Form::label('aspect_data', 'Description: ');
		$form .= \Form::textarea('aspect_data');
		$form .= '</p>';
		$form .= '<p>';
		$form .= \Form::label('file_upload', 'File Upload: ');
		$form .= \Form::file('file_upload');
		$form .= '</p>';
		$form .= '<p>';
		$form .= \Form::label('aspect_source', 'Source: ');
		$form .= \Form::text('aspect_source');
		$form .= '</p>';
		$form .= $this->notes_fields();
		$form .= '<p>' . \Form::submit('Submit', ['class' => 'btn btn-primary']) . '</p>';
		$form .= \Form::close();
        return $form;
	}
	public function edit_form($id){
		$form = \Form::open(['url' => '/aspect/'.$this->id.'/edit', 'method' => 'post', 'files' => true]);
		$form .= \Form::hidden('subject_id', $subject_id);
		$form .= \Form::hidden('aspect_type', $aspect_type_id );
		$form .= '<p>';
		$form .= \Form::label('title', 'Title: ');
		$form .= \Form::text('title');
		$form .= '</p>';
		$form .= '<p>';
		$form .= \Form::label('aspect_data', 'Description: ');
		$form .= \Form::textarea('aspect_data');
		$form .= '</p>';
		$form .= '<p>';
		$form .= \Form::label('file_upload', 'File Upload: ');
		$form .= \Form::file('file_upload');
		$form .= '</p>';
		$form .= '<p>';
		$form .= \Form::label('aspect_source', 'Source: ');
		$form .= \Form::text('aspect_source');
		$form .= '</p>';

		$form .= $this->notes_fields();

		$form .= '<p>' . \Form::submit('Submit', ['class' => 'btn btn-primary']) . '</p>';
		$form .= \Form::close();
        return $form;
	}
	public function display_aspect(){
		$output = '<div class="aspect_type-'.$this->aspect_type()->id.'">';
		$output .= '<h4>'.$this->title.'</h4>';
		$output .= '<p>Description: '.$this->aspect_data.'</p>'.PHP_EOL;
		$output .= '<p><a href="'.$this->aspect_source.'">'.$this->title.'</a></p>';
		$output .= '</div>';
		return $output;
	}
	public function parse(){}
}

class ImageAspect extends FileUploadAspect{

	public function notes_schema(){
		$settings = json_decode(parent::notes_schema(), true);
		$settings['width'] = '';
		$settings['height'] = '';
		$settings['css_class'] = '';
		return json_encode($settings);
	}

	public function create_form($subject_id, $aspect_type_id=null){
		return parent::create_form($subject_id, $this->aspect_type);
	}
	public function edit_form($id){
		return parent::edit_form($id);
	}

	public function css_size(){
		$css_string = 'width:'.$this->width.';';
		if (!is_null($this->height)){
			$css_string .= 'height:'.$this->height.';';
		}
		return $css_string;
	}

	public function css_class(){
		return (!is_null($this->css_class)) ? 'class="'.$this->css_class.'" ' : '';
	}

	public function display_aspect(){
		$css_size = '';
		$settings = (array) json_decode($this->aspect_notes);
		if ( !empty($settings['width']) ){
			$css_size = 'style="width:'.$settings['width'].';';
			if ( !empty($settings['height']) ){
				$css_size .= ' height:'.$settings['height'].';';
			}
			$css_size .= '"';
		}
		if ( !empty($settings['css_class']) ){
			$css_size .= ' class="'.$settings['css_class'].'"';
		}
		$output = '<h4>'.$this->title.'</h4>';
		$output .= '<img src="'.$this->aspect_source.'" '.$css_size.' />';
		$output .= '<div class="image_caption">'.$this->aspect_data.'</div>';
		$output .= '<p><a href="'.$this->aspect_source.'">Uploaded File</a></p>';

		return $output;
	}
	public function parse(){}
}

class UnformattedTextAspect extends Aspect{
	public function create_form($subject_id, $aspect_type_id=null){
		$form = \Form::open(['url' => '/aspect/create', 'method' => 'post', 'files' => true]);
		$form .= \Form::hidden('subject_id', $subject_id);
		$form .= \Form::hidden('aspect_type', $aspect_type_id );
		$form .= '<p>';
		$form .= \Form::label('title', 'Title: ');
		$form .= \Form::text('title');
		$form .= '</p>';
		$form .= '<p>';
		$form .= \Form::label('aspect_data', 'Text: ');
		$form .= '<br />';
		$form .= \Form::textarea('aspect_data', null, ['style' => 'width:100%;']);
		$form .= '</p>';
		$form .= \Form::hidden('file_upload');
		$form .= '<p>';
		$form .= \Form::label('aspect_source', 'Source: ');
		$form .= \Form::text('aspect_source');
		$form .= '</p>';
		$form .= $this->notes_fields();
		$form .= '<p>' . \Form::submit('Submit', ['class' => 'btn btn-primary']) . '</p>';
		$form .= \Form::close();
        return $form;
	}
	public function edit_form($id){
		return parent::edit_form($id);
	}
	public function display_aspect(){
		$output = parent::display_aspect();
		return $output;
	}
	public function parse(){}
}  

class WebpageAspect extends Aspect{
	public function create_form($subject_id, $aspect_type_id=null){
		$form = \Form::open(['url' => '/aspect/create', 'method' => 'post', 'files' => true]);
		$form .= \Form::hidden('subject_id', $subject_id);
		$form .= \Form::hidden('aspect_type', $aspect_type_id );
		$form .= \Form::hidden('aspect_data');
		$form .= \Form::hidden('hidden', '0');
		$form .= \Form::hidden('file_upload');
		$form .= '<p>';
		$form .= \Form::label('title', 'Title: ');
		$form .= \Form::text('title');
		$form .= '</p>';
		$form .= '<p>';
		$form .= \Form::label('aspect_source', 'URL: ');
		$form .= \Form::text('aspect_source');
		$form .= '</p>';
		$form .= $this->notes_fields();
		$form .= '<p>' .\Form::submit('Submit', ['class' => 'btn btn-primary']) . '</p>';
		$form .= \Form::close();
        return $form;
	}
	public function edit_form($id){
		$current_aspect = Aspect::find($id);
		$form = \Form::open(['url' => '/aspect/'.$id.'/edit', 'method' => 'post', 'files' => false]);
		$form .= \Form::hidden('subject_id', $current_aspect->subjects()->first()->id);
		$form .= \Form::hidden('aspect_type', $current_aspect->aspect_type()->id );
		$form .= \Form::hidden('aspect_data', $current_aspect->aspect_data);
		$form .= \Form::hidden('hidden', $current_aspect->hidden);
		$form .= \Form::hidden('file_upload');
		$form .= '<p>';
		$form .= \Form::label('title', 'Title: ');
		$form .= \Form::text('title', $current_aspect->title);
		$form .= '</p>';
		$form .= '<p>';
		$form .= \Form::label('aspect_source', 'URL: ');
		$form .= \Form::text('aspect_source', $current_aspect->aspect_source);
		$form .= '</p>';
		$form .= $this->notes_fields();
		$form .= '<p>' . \Form::submit('Submit', ['class' => 'btn btn-primary']) . '</p>';
		$form .= \Form::close();
        return $form;
	}
	public function display_aspect(){
		$output = '<div class="aspect_type-'.$this->aspect_type()->id.'">';
		$output .= '<p><strong>URL: </strong>';
		$output .= '<a href="'.$this->aspect_source.'" target="_blank">'.$this->aspect_source.'</a>';
		$output .= '</p></div>';
		return $output;
	}
	public function parse(){}
}  // End of the WebpageAspectclass.

class LamdaFunctionAspect extends Aspect{
	function __construct(){
		parent::__construct();
		$this->keep_history = false;
	}

	public function notes_schema(){
		return parent::notes_schema();
	}

	public function create_form($subject_id, $aspect_type_id=null){
		$output = $this->display_aspect() . '<hr />';
		$form = \Form::open(['url' => '/aspect/create', 'method' => 'post', 'files' => true]); 
		$form .= \Form::hidden('subject_id', $subject_id);
		$form .= \Form::hidden('aspect_type', $aspect_type_id );
		$form .= \Form::hidden('title', '');
		$form .= \Form::hidden('aspect_source', null);
		$form .= \Form::hidden('aspect_data');
		$form .= \Form::hidden('hidden', '1');
		$form .= \Form::hidden('file_upload');
		$form .= $this->notes_fields();
		$form .= '<p>' .\Form::submit('Submit', ['class' => 'btn btn-primary']) . '</p>';
		$form .= \Form::close();
		$output .= $form;
        return $output;
	}
	public function edit_form($id){
		$output = $this->display_aspect() . '<hr />';
		$form = \Form::open(['url' => '/aspect/'.$this->id.'/edit', 'method' => 'post', 'files' => false]); 
		$form .= \Form::hidden('subject_id', $this->subject_id);
		$form .= \Form::hidden('aspect_type', $this->aspect_type );
		$form .= \Form::hidden('title', $this->title);
		$form .= \Form::hidden('aspect_source',$this->aspect_source);
		$form .= \Form::hidden('aspect_data', $this->aspect_data);
		$form .= \Form::hidden('hidden', $this->hidden);
		$form .= \Form::hidden('file_upload');
		$form .= $this->notes_fields();
		$form .= '<p>' .\Form::submit('Submit', ['class' => 'btn btn-primary']) . '</p>';
		$form .= \Form::close();
		$output .= $form;
        return $output;
	}
	public function display_aspect(){
		$output = $this->lambda_function();
		return $output;
	}
	public function parse(){}

	public function lambda_function(){
		return 'lambda_function output';
	}
}  // End of the LamdaFunctionAspectclass.

/*  -- End Basic Aspect Types -- */

/*  -- Begin App-specific Aspects -- */



// ---------------------------------------------- //


// ---------- End Custom Aspects ---------------- //