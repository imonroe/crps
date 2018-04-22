<?php
/*
Quick reference for flash messages:
$request->session()->flash('message', 'Task was successful!');
$request->session()->flash('error', 'Something went wrong!');
*/

namespace imonroe\crps\Http\Controllers;
use App\Http\Controllers\Controller;
use imonroe\crps\Http\Controllers\SearchController;
use imonroe\crps\Subject;
use imonroe\crps\SubjectType;
use imonroe\crps\Aspect;
use imonroe\crps\AspectType;
use imonroe\crps\AspectFactory;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use GrahamCampbell\Markdown\Facades\Markdown;
use Validator;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $codex_array = self::get_codex_array();
        return view('subject.index', ['title' => 'Codex', 'codex_array' => $codex_array ]);
    }

    /**
     * Show the form for creatin, g a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($subject_type_id=null)
    {

        $subject_types = htmlspecialchars(json_encode(SubjectType::codex_array(false, true)));
        if (!empty($subject_types) && $subject_type_id > -1){
          // we'll need a little information about the subject type.
          $st = SubjectType::find($subject_type_id);
          $currently_selected_type = htmlspecialchars( json_encode( $st->parent_subject_type_ids_array() ) );
        } else {
          $currently_selected_type = htmlspecialchars(json_encode(array("")));
        }

        $form = '';
        $form = \BootForm::horizontal(['url' => '/subject/create', 'method' => 'post']);

        $form .= '<div class="form-group "><label for="subject_type" class="control-label col-sm-2 col-md-3">Subject Type</label>';
        $form .= '<div class="col-sm-2 col-md-3">';
        $form .= '<subject-type-cascader :menu="'.$subject_types.'" :currently-selected="'.$currently_selected_type.'"></subject-cascader>';
        $form .= '</div></div>';

        $form .= \BootForm::text('name', 'Subject Name');
        $form .= \BootForm::textarea('description', 'Description');
        $form .= \BootForm::submit('Submit', ['class' => 'btn btn-primary']);
        $form .= \BootForm::close();
        return view('forms.basic', ['form' => $form, 'title'=>'Create a new Subject']);
    }

    public function create_from_search($query)
    {
        $subject_type = SubjectType::where('type_name', '=', 'Unsorted')->first();
        $searcher = new SearchController;
        $abstract = $searcher->web_search($query);
        $subject_name = (!empty($abstract['Heading'])) ? $abstract['Heading'] : null;
        $abstract_summary = (!empty($abstract['AbstractText'])) ? $abstract['AbstractText'] : null;
        $image_url = (!empty($abstract['Image'])) ? $abstract['Image'] : null;

        if ($subject_name) {
            $new_subject = new Subject;
            $new_subject->name = $subject_name;
            $new_subject->subject_type = $subject_type->id;
            $new_subject->save();

            if ($abstract_summary) {
                $aspect_type = AspectType::where('aspect_name', '=', 'Markdown Text')->first();
                $abstract = AspectFactory::make_from_aspect_type($aspect_type->id);
                $abstract->aspect_data = $abstract_summary;
                $abstract->aspect_source = 'Autocreated from DuckDuckGo results';
                $abstract->save();
                $new_subject->aspects()->attach($abstract->id);
            }

            if ($image_url) {
                $aspect_type = AspectType::where('aspect_name', '=', 'Image')->first();
                $abstract = AspectFactory::make_from_aspect_type($aspect_type->id);
                $abstract->aspect_data = $subject_name;
                $abstract->aspect_source = $image_url;
                $abstract->save();
                $new_subject->aspects()->attach($abstract->id);
            }
            return redirect('/subject/'.$new_subject->id);
        } else {
            return redirect('/errors/404');
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Validator::make($request->all(), [
          'name' => Rule::unique('subjects')->where(function ($query) {
              return $query->where( 'user', Auth::id() );
          }),
        ])->validate();

        $new_subject = new Subject;
        $new_subject->name = $request->input('name');
        $new_subject->description = $request->input('description');
        $new_subject->subject_type = !empty($request->input('subject_type')) ? $request->input('subject_type') : '-1';
        $new_subject->user = Auth::id();
        $new_subject->save();
        $request->session()->flash('message', 'Subject saved.');
        return redirect('/subject/'.$new_subject->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $subject = Subject::findOrFail($id);

        // We want a little information about the parent, if it's available.
        /*
        $parent_id = $subject->parent_id;
        $parent_name = '';
        if ($parent_id > 0){
            $parent_subject = Subject::findOrFail($parent_id);
            $parent_name = $parent_subject->name;
        }
        $parent = ['parent_id' => $parent_id, 'parent_name'=>$parent_name];

        $codex = $subject->directory_array();
        if (!empty($codex['children'])){
          $menu = $codex['children'];
        } else {
          $menu = false;
        }
        */

        // If we have a description, we'll treat it like Markdown and pass it to the template.
        $description = '<p>' . $subject->description . '</p>';

        return view('subject.show', ['subject'=>$subject, 'description' => $description,  ] );
    }

  	public function coldreader_homepage()
  	{
  		$subject = Subject::where('name', '=', 'Dashboard')->first();
      return $this->show($subject->id);
       //return view('subject.show', ['subject'=>$subject]);
  	}

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $subject = Subject::findOrFail($id);
        // We need subject type information.
        $subject_types = htmlspecialchars(json_encode(SubjectType::codex_array(false, true)));
        if ( $subject->subject_type > 0 ){
          $st = SubjectType::find( $subject->subject_type );
          $currently_selected_type = htmlspecialchars( json_encode( $st->parent_subject_type_ids_array() ) );
        } else {
          $currently_selected_type = htmlspecialchars(json_encode(array("")));
        }

        $form = '';
        $form .= \BootForm::open(['url' => '/subject/'.$id.'/edit', 'method' => 'post']);
        $form .= \BootForm::text('name', 'Subject Name', $subject->name);
        $form .= \BootForm::label('subject_type_label', 'Subject Type');
        $form .= '<subject-type-cascader :menu="'.$subject_types.'" :currently-selected="'.$currently_selected_type.'"></subject-type-cascader>'.PHP_EOL;
        $form .= \BootForm::textarea('description', 'Subject Description', $subject->description);
        $form .= '<p>Created at: '.$subject->created_at.'<br />Updated at:'.$subject->updated_at.'</p>';
        $form .= \BootForm::submit('Submit') . '</p>';
        $form .= \BootForm::close();
        return view('forms.basic', ['form' => $form, 'title'=>'Edit '.$subject->name]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $new_subject = Subject::findOrFail($id);
        $new_subject->name = $request->input('name');
        //$new_subject->parent_id = $request->input('parent_id');
        $new_subject->subject_type = $request->input('subject_type');
        $new_subject->description = $request->input('description');
        $new_subject->save();
        $request->session()->flash('message', 'Subject updated.');
        return redirect('/subject/'.$new_subject->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $new_subject = Subject::findOrFail($id);
        $subject_type = $new_subject->subject_type();
        $subject_type_id = ( $subject_type ) ? $subject_type->id : '-1'; 
        //dd($new_subject->aspects()->get());
        foreach ($new_subject->aspects()->get() as $a){
          $a->delete();
        }
        $new_subject->delete();
        $request->session()->flash('message', 'Subject deleted.');
        return redirect('/subject_type/'.$subject_type_id);
    }

    public static function autocomplete(Request $request)
    {
        $term = '%'.$request->input('term').'%';
        $a_json = array();
        $a_json_row = array();
        $candidates = Subject::where('name', 'like', $term)->orderBy('name')->get();
        foreach ($candidates as $row){
            $a_json_row["id"] = $row->id;
            $a_json_row["value"] = $row->name;
            $a_json_row["label"] = $row->name;
            array_push($a_json, $a_json_row);
        }
        return response()->json($a_json);
    }

    public static function get_codex_array($subject_id = null){
      if ($subject_id){
        $subject = Subject::findOrFail($subject_id);
        $output = $subject->directory_array();
      } else {
        $output = Subject::codex_array();
      }
      return $output;
    }

}
