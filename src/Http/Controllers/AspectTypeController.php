<?php
namespace imonroe\crps\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use imonroe\crps\Aspect;
use imonroe\crps\AspectType;
use Illuminate\Support\Facades\DB;

class AspectTypeController extends Controller
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
        $types = AspectType::all();
        return view('aspect_type.index', ['types' => $types, 'title' => 'Available Aspect Types']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $form = '';
        $form .= \Form::open(['url' => '/aspect_type/create', 'method' => 'post']);
        $form .= '<p>';
        $form .= \Form::label('aspect_name', 'Aspect Type Name: ');
        $form .= \Form::text('aspect_name');
        $form .= '</p>';

        $form .= '<p>';
        $form .= \Form::label('aspect_description', 'Aspect Type Description: ');
        $form .= \Form::text('aspect_description');
        $form .= '</p>';

        $form .= '<p>';
        $form .= \Form::label('is_viewable', 'Visible?: ');
        $form .= \Form::checkbox('is_viewable', '1');
        $form .= '</p>';

        $form .= '<p>' . \Form::submit('Submit') . '</p>';
        $form .= \Form::close();
        return view('forms.basic', ['form' => $form, 'title'=>'Create a new Aspect Type']);
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
        id  int(10) unsigned Auto Increment
        aspect_name varchar(191)
        aspect_description  text NULL
        is_viewable int(11) NULL
        created_at  timestamp NULL
        updated_at  timestamp NULL
        */

        $type = new AspectType;
        $type->aspect_name = $request->input('aspect_name');
        $type->aspect_description = $request->input('aspect_description');
        $type->is_viewable = $request->input('is_viewable');
        $type->save();
        $write_new_class = $this->create_custom_aspect_class($type->id);
        $message = 'Aspect Type saved.';
        if ($write_new_class) {
            $message .= '  Successfully created new Aspect Class.';
        } else {
            $message .= '  Something went wrong trying to create the new Aspect Class.';
        }
        $request->session()->flash('message', $message);
        return redirect('/aspect_type/');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $type = AspectType::find($id);
        return view('aspect_type.show', ['type' => $type]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $type = AspectType::find($id);
        $form = '';
        $form .= \Form::open(['url' => '/aspect_type/'.$type->id.'/edit', 'method' => 'post']);
        $form .= '<p>';
        $form .= \Form::label('aspect_name', 'Aspect Type Name: ');
        //$form .= \Form::text('aspect_name', $type->aspect_name);
        $form .= $type->aspect_name . '  <span class="small">(cannot be changed)</span>';
        $form .= '</p>';

        $form .= '<p>';
        $form .= \Form::label('aspect_description', 'Aspect Type Description: ');
        $form .= \Form::text('aspect_description', $type->aspect_description);
        $form .= '</p>';

        $form .= '<p>';
        $form .= \Form::label('is_viewable', 'Visible?: ');
        $form .= ($type->is_viewable = 1) ? \Form::checkbox('is_viewable', '1', true) : \Form::checkbox('is_viewable', '1');
        $form .= '</p>';

        $form .= '<p>' . \Form::submit('Submit') . '</p>';
        $form .= \Form::close();

        $form .= '<hr><a href="/aspect_type/'.$type->id.'/delete" class="confirmation">Delete this Aspect Type, and all the Aspects of this Type. (Cannot be undone.)</a>';
        return view('forms.basic', ['form' => $form, 'title'=>'Edit the '.$type->aspect_name.' Aspect Type']);
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
        $type = AspectType::find($id);
        $type->aspect_name = $request->input('aspect_name');
        $type->aspect_description = $request->input('aspect_description');
        $type->is_viewable = $request->input('is_viewable');
        $type->save();
        $request->session()->flash('message', 'Aspect Type updated.');
        return redirect('/aspect_type/');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $type = AspectType::find($id);
        $aspects_to_delete = Aspect::where('aspect_type', '=', $id);
        foreach ($aspects_to_delete as $d) {
            $d->delete();
        }
        $type->delete();
        //$request->session()->flash('message', 'Aspect Type Deleted.');
        return redirect('/aspect_type');
    }

    public function create_custom_aspect_class($aspect_type_id)
    {
        $aspect_classes_file = env('APP_FILE_ROOT').'/app/CustomAspects.php';
        $find_and_replace_token = '// ---------------------------------------------- //';

        $new_type = DB::select('SELECT aspect_name FROM aspect_types where id = :id LIMIT 1', ['id' => $aspect_type_id]);
        $aspect_type_name = $new_type[0]->aspect_name;
        $classname = \imonroe\ana\Ana::code_safe_name($aspect_type_name);
        $classname = $classname . 'Aspect';

        $output = "// default custom class created automatically.".PHP_EOL.PHP_EOL;
        $output .= 'class '.$classname.' extends Aspect{'.PHP_EOL;

        $output .= "\t".'public function notes_schema(){'.PHP_EOL;
        $output .= "\t"."\t".'return parent::notes_schema();'.PHP_EOL;
        $output .= "\t".'}'.PHP_EOL;

        $output .= "\t".'public function create_form($subject_id, $aspect_type_id=null){'.PHP_EOL;
        $output .= "\t"."\t".'return parent::create_form($subject_id, $this->aspect_type);'.PHP_EOL;
        $output .= "\t".'}'.PHP_EOL;

        $output .= "\t".'public function edit_form($id){'.PHP_EOL;
        $output .= "\t"."\t".'return parent::edit_form($id);'.PHP_EOL;
        $output .= "\t".'}'.PHP_EOL;

        $output .= "\t".'public function display_aspect(){'.PHP_EOL;
        $output .= "\t"."\t".'$output = parent::display_aspect();'.PHP_EOL;
        $output .= "\t"."\t".'return $output;'.PHP_EOL;
        $output .= "\t".'}'.PHP_EOL;
        $output .= "\t".'public function parse(){}'.PHP_EOL;

        $output .= '}  // End of the '.$classname.'class.'.PHP_EOL;
        $output .= PHP_EOL.PHP_EOL;
        $output .= $find_and_replace_token;

        $file_contents = file_get_contents($aspect_classes_file);
        $file_contents = str_replace($find_and_replace_token, $output, $file_contents);
        if (file_put_contents($aspect_classes_file, $file_contents)) {
            return true;
        } else {
            return false;
        }
    } // end create_custom_aspect_class
}
