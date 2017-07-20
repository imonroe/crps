<?php

namespace imonroe\crps\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use imonroe\crps\SubjectType;
use imonroe\crps\Subject;

class SubjectTypeController extends Controller
{
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
		$form .= \Form::open(['url' => '/subject_type/create', 'method' => 'post']);
		$form .= '<p>';
		$form .= \Form::label('type_name', 'Subject Type Name: ');
		$form .= \Form::text('type_name');
		$form .= '</p>';

		$form .= '<p>';
		$form .= \Form::label('type_description', 'Subject Type Description: ');
		$form .= \Form::text('type_description');
		$form .= '</p>';

		$form .= '<p>';
		$form .= \Form::label('aspect_group', 'Aspect Group: ');
		$form .= \Form::select('subject_type', ['1' => 'default']);
		$form .= '</p>';

		$form .= '<p>';
		$form .= \Form::label('parent_id', 'Parent Subject Type: ');
		$form .= \Form::select('parent_id', SubjectType::options_list());
		$form .= '</p>';

		$form .= '<p>' . \Form::submit('Submit') . '</p>';
		$form .= \Form::close();
        return view('forms.basic', ['form' => $form, 'title'=>'Create a new Subject Type']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
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
		$type->aspect_group = (int) $request->input('aspect_group');
		$type->parent_id = (int) $request->input('parent_id');
		$type->save();
		$request->session()->flash('message', 'Subject Type saved.');
		return redirect('/subject_type/'.$type->id);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $type = SubjectType::find($id);
		$parent_type = SubjectType::find($type->parent_id);
		$children = $type->children();
		$parent_type_name = !empty($parent_type->type_name) ? $parent_type->type_name : 'None';
		$parent_type_id = !empty($parent_type->id) ? $parent_type->id : false;
		$all_subjects = $type->get_all_subjects();

		$page = Paginator::resolveCurrentPage('page') ?: 1;
		$perPage = 25;
		$paginate = new LengthAwarePaginator($all_subjects->forPage($page, $perPage), $all_subjects->count(), $perPage, $page, ['path'=>url('/subject_type/'.$id)]);

		return view('subject_type.show', ['type' => $type, 
										  'title'=>'Subject Type: '.$type->type_name, 
										  'parent_name' => $parent_type_name,
										  'parent_type_id' => $parent_type_id,
										  'children' => $children,
										  'subjects' => $paginate, 
										 ]); 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
		$subject_type = SubjectType::findOrFail($id);
        $form = '';
		$form .= \Form::open(['url' => '/subject_type/'.$id.'/edit', 'method' => 'post']);
		$form .= '<p>';
		$form .= \Form::label('type_name', 'Subject Type Name: ');
		$form .= \Form::text('type_name', $subject_type->type_name);
		$form .= '</p>';

		$form .= '<p>';
		$form .= \Form::label('type_description', 'Subject Type Description: ');
		$form .= \Form::text('type_description', $subject_type->type_description);
		$form .= '</p>';

		$form .= '<p>';
		$form .= \Form::label('aspect_group', 'Aspect Group: ');
		$form .= \Form::select('subject_type', ['1' => 'default']);
		$form .= '</p>';

		// You can't be your own grandpa.
		$parents_options = SubjectType::options_list();
		if ( !empty($parents_options[$id]) ){
			unset($parents_options[$id]);
		}

		$form .= '<p>';
		$form .= \Form::label('parent_id', 'Parent Subject Type: ');
		$form .= \Form::select('parent_id', $parents_options, $subject_type->parent_id);
		$form .= '</p>';

		$form .= '<p>' . \Form::submit('Submit') . '</p>';
		$form .= \Form::close();
        return view('forms.basic', ['form' => $form, 'title'=>'Edit the '.$subject_type->type_name.' Subject Type']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $type = SubjectType::findOrFail($id); 
		$type->type_name = $request->input('type_name');
		$type->type_description = $request->input('type_description');
		$type->aspect_group = (int) $request->input('aspect_group');
		$type->parent_id = (int) $request->input('parent_id');
		$type->save();
		$request->session()->flash('message', 'Subject Type updated.');
		return redirect('/subject_type/'.$type->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
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