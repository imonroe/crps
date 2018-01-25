<?php

namespace imonroe\crps\Http\Controllers;
use App\Http\Controllers\Controller;
use Laravel\Spark\Spark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use imonroe\crps\SubjectType;
use imonroe\crps\Subject;

class SubjectTypeController extends Controller
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
        //
        $directory = SubjectType::directory();
        return view('subject_type.index', ['title' => 'Data Store', 'directory' => $directory ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $form = '';
        $form .= \BootForm::horizontal(['url' => '/subject_type/create', 'method' => 'post']);
        $form .= \BootForm::text('type_name', 'Subject Type Name');
        $form .= \BootForm::text('type_description', 'Subject Type Description');
        $form .= \BootForm::select('parent_id', 'Parent Subject Type: ', SubjectType::options_list());
        $form .= \BootForm::submit('Submit', ['class' => 'btn btn-primary']);
        $form .= \BootForm::close();
        return view('forms.basic', ['form' => $form, 'title'=>'Create a new Subject Type']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /*
        id	int(10) unsigned Auto Increment
        type_name	varchar(191)
        type_description	text NULL
        aspect_group	int(11) NULL
        parent_id	int(11) NULL
        */

        $type = new SubjectType;
        $type->type_name = $request->input('type_name');
        $type->type_description = $request->input('type_description');
        $type->parent_id = (int) $request->input('parent_id');
        // Who does this aspect belong to?
        $type->user = Auth::id();
        $type->save();
        $request->session()->flash('message', 'Subject Type saved.');
        return redirect('/subject_type/'.$type->id);

    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      /*
        I am going to need:
        $type->id
        type->type_name
        $type->type_description
        $unfolded_subject_types
        $codex
        $page

        Here, we account for the special case of a subject having no Subject Type.
        In that case, we have a type id of -1, so we'll just handle that manually.
      */
        if ( $id < 0 ){
          $type_name = 'Codex';
          $type_id = -1;
          $type_description = '';
          $all_subjects = Subject::where('subject_type', '=', '-1')->get();
          $unfolded_subject_types = array();
        } else {
          $type = SubjectType::find($id);
          $type_name = $type->type_name;
          $type_id = $type->id;
          $type_description = $type->type_description;
          $all_subjects = $type->get_all_subjects();
          $unfolded_subject_types = $type->parent_subject_type_ids_array();
          //$unfolded_subject_types[] = $type_id;
          if (count($unfolded_subject_types) > 1){
              array_pop($unfolded_subject_types);
          }
        }

        $codex = SubjectType::codex_array(false, true);
        $page = Paginator::resolveCurrentPage('page') ?: 1;
        $perPage = 25;
        $paginate = new LengthAwarePaginator($all_subjects->forPage($page, $perPage), $all_subjects->count(), $perPage, $page, ['path'=>url('/subject_type/'.$id)]);

        return view(
            'subject_type.show', [
                                  'title'=>'Subject Type: '.$type_name,
                                  'type_name' => $type_name,
                                  'type_id' => $type_id,
                                  'unfolded_subject_types' => $unfolded_subject_types,
                                  'type_description' => $type_description,
                                  'subjects' => $paginate,
                                  'codex' => $codex,
                                 ]
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $subject_type = SubjectType::findOrFail($id);
        $form = '';
        $form .= \BootForm::horizontal(['url' => '/subject_type/'.$id.'/edit', 'method' => 'post']);
        $form .= '<p>';
        $form .= \BootForm::label('type_name', 'Subject Type Name: ');
        $form .= \BootForm::text('type_name', $subject_type->type_name);
        $form .= '</p>';

        $form .= '<p>';
        $form .= \BootForm::label('type_description', 'Subject Type Description: ');
        $form .= \BootForm::text('type_description', $subject_type->type_description);
        $form .= '</p>';

        // You can't be your own grandpa.
        $parents_options = SubjectType::options_list();
        if (!empty($parents_options[$id]) ) {
            unset($parents_options[$id]);
        }

        $form .= '<p>';
        $form .= \BootForm::label('parent_id', 'Parent Subject Type: ');
        $form .= \BootForm::select('parent_id', $parents_options, $subject_type->parent_id);
        $form .= '</p>';

        $form .= '<p>' . \BootForm::submit('Submit') . '</p>';
        $form .= \BootForm::close();
        return view('forms.basic', ['form' => $form, 'title'=>'Edit the '.$subject_type->type_name.' Subject Type']);
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
        $type = SubjectType::findOrFail($id);
        $type->type_name = $request->input('type_name');
        $type->type_description = $request->input('type_description');
        $type->parent_id = (int) $request->input('parent_id');
        $type->save();
        $request->session()->flash('message', 'Subject Type updated.');
        return redirect('/subject_type/'.$type->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $type = SubjectType::findOrFail($id);
        $subjects_to_remove = $type->subjects();
        foreach ($subjects_to_remove as $subject){
            $aspects_to_remove = $subject->aspects();
            foreach ($aspects_to_remove as $aspect){
                $aspect->subjects()->detach();
                $aspect->delete();
            }
            $subject->delete();
        }
        $type->delete();
        $request->session()->flash('message', 'Subject Type deleted.');
        return redirect('/subject_type/');
    }
}
