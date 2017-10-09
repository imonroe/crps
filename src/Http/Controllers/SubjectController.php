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
use imonroe\crps\Aspect;
use imonroe\crps\AspectType;
use imonroe\crps\AspectFactory;
use Illuminate\Http\Response;
use Laravel\Spark\Spark;
use Illuminate\Http\Request;

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
        //$subjects = Subject::all();
        //$directory = SubjectType::directory();
        $codex_array = self::get_codex_array();
        return view('subject.index', ['title' => 'Codex', 'codex_array' => $codex_array ]);
    }

    /**
     * Show the form for creatin, g a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $form = '';
        $form .= \BootForm::open(['url' => '/subject/create', 'method' => 'post']);

        $form .= \BootForm::text('name', 'Subject Name');


        $form .= '<p>' . \Form::submit('Submit') . '</p>';
        $form .= \Form::close();
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
        $this->validate(
            $request, [
            'name' => 'required|unique:subjects',
            ]
        );

        $new_subject = new Subject;
        $new_subject->name = $request->input('name');
        $new_subject->subject_type = !empty($request->input('subject_type')) ? $request->input('subject_type') : 0 ;
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

        return view('subject.show', ['subject'=>$subject, 'parent'=>$parent, 'codex' => $menu  ] );
    }

	public function coldreader_homepage()
	{
		$subject = Subject::where('name', '=', 'Coldreader Home Page')->first();
		 return view('subject.show', ['subject'=>$subject]);
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

        $currently_selected_array = $subject->parent_subjectids_array();
        array_pop($currently_selected_array);
        $currently_selected = htmlspecialchars( json_encode( $currently_selected_array ) );

        $menu = htmlspecialchars( json_encode( Subject::codex_array( $id, true) ) );

        $form = '';
        $form .= \BootForm::open(['url' => '/subject/'.$id.'/edit', 'method' => 'post']);
        $form .= \BootForm::text('name', 'Subject Name', $subject->name);
        $form .= \BootForm::label('parent_id_label', 'Parent Subject');
        $form .= '<subject-cascader :menu="'.$menu.'" :currently-selected="'.$currently_selected.'"></subject-cascader>';
        $form .= '';
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
        $new_subject->parent_id = $request->input('parent_id');
        $new_subject->description = $request->input('description');
        $new_subject->save();
        $request->session()->flash('message', 'Subject updated.');
        //return view('subject.show', ['subject'=> $new_subject]);
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
        foreach ($new_subject->aspects() as $a){
          $a->delete();
        }
        $new_subject->delete();
        $request->session()->flash('message', 'Subject deleted.');
        return redirect('/subject_type/'.$subject_type->id);
    }

    public static function autocomplete(Request $request)
    {
        $term = '%'.$request->input('term').'%';
        //dd($term);
        $a_json = array();
        $a_json_row = array();
        //$query = 'SELECT * from subjects where name LIKE :terms ORDER BY name ASC';
        //$params = ['terms' => $term];
        //$candidates = \DB::select($query , $params);
        //dd($candidates);
        $candidates = Subject::where('name', 'like', $term)->orderBy('name')->get();
        //$candidates = \DB::table('subjects')->where('name', 'like', $term)->orderBy('name')->get();
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
