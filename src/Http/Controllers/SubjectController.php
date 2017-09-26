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
        $directory = SubjectType::directory();
        return view('subject.index', ['title' => 'Data Store', 'directory' => $directory ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($subject_type_id = '')
    {
        $subject_types = SubjectType::get_options_array();
        $form = '';
        $form .= \Form::open(['url' => '/subject/create', 'method' => 'post']);
        $form .= '<p>';
        $form .= \Form::label('name', 'Subject Name: ');
        $form .= \Form::text('name');
        $form .= '</p>';
        $form .= '<p>';
        $form .= \Form::label('subject_type', 'Subject Type: ');
        $form .= \Form::select('subject_type', $subject_types, $subject_type_id);
        $form .= '</p>';
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
        return view('subject.show', ['subject'=>$subject]);
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
        $subject_types = SubjectType::get_options_array();
        $form = '<p>Your subject is: '.$subject->name .'</p>';
        $form = '';
        $form .= \Form::open(['url' => '/subject/'.$id.'/edit', 'method' => 'post']);
        $form .= '<p>';
        $form .= \Form::label('name', 'Subject Name: ');
        $form .= \Form::text('name', $subject->name);
        $form .= '</p>';
        $form .= '<p>';
        $form .= \Form::label('subject_type', 'Subject Type: ');
        $form .= \Form::select('subject_type', $subject_types, $subject->subject_type);
        $form .= '</p>';
        $form .= '<p>' . \Form::submit('Submit') . '</p>';
        $form .= \Form::close();
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
        $new_subject->subject_type = $request->input('subject_type');
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
        $subject_type = $new_subject->subject_type();
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


}
