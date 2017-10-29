<?php
namespace imonroe\crps\Http\Controllers;
use App\Http\Controllers\Controller;
use Laravel\Spark\Spark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use imonroe\crps\Aspect;
use imonroe\crps\AspectFactory;
use imonroe\crps\AspectType;
use imonroe\crps\Subject;
use Illuminate\Support\Facades\Auth;

class AspectController extends Controller
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
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($subject_id)
    {
        $subject = Subject::find($subject_id);
        $back_to_subject = '<p style="float:right;"><a href="/subject/'.$subject_id.'" class="btn btn-default" > << Return to '.$subject->name.'</a></p><hr style="clear:both;" />';
        $aspect = new Aspect;
        $form = $back_to_subject . $aspect->create_form($subject_id);
        return view('forms.basic', ['form' => $form, 'title'=>'Create a new Aspect']);
    }

    // Sometimes we want to create a specific kind of custom aspect directly.
    public function create_with_type($subject_id, $aspect_type_id)
    {
        $subject = Subject::find($subject_id);
        $back_to_subject = '<p style="float:right; clear:both;"><a href="/subject/'.$subject_id.'" class="btn btn-default"> << Return to '.$subject->name.'</a></p><hr style="clear:both;" />';
        $custom_aspect_type = AspectFactory::make_from_aspect_type($aspect_type_id);
        $customform = $back_to_subject . $custom_aspect_type->create_form($subject_id, $aspect_type_id);
        return view('forms.basic', ['form' => $customform, 'title'=>'Create a new '.$custom_aspect_type->aspect_type()->aspect_name.' Aspect']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $aspect = AspectFactory::make_from_aspect_type($request->input('aspect_type'));
        $aspect->aspect_data = $request->input('aspect_data');

        // Handle any settings that might be specified.
        if (!is_null($aspect->notes_schema())) {
            $schema = json_decode($aspect->notes_schema(), true);
            $settings_array = array();
            foreach ($schema as $key => $setting){
                if ($request->exists('settings_'.$key) ) {
                    $settings_array[$key] = $request->input('settings_'.$key);
                }
            }
            if (!empty($settings_array)) {
                $aspect->aspect_notes = $settings_array;
            }
        }

        // Sometimes, we'll have a file attached.
        // In that case, we're going to store the file title in the aspect data, and the file path in the aspect_source fields.
        $file_upload = false;
        if ($request->hasFile('file_upload')) {
            $file_upload = true;
            $file = $request->file('file_upload');

            // support for per-user cloud Storage
            $user = $request->user();
            if (!empty($user->storage)){
              $filepath = $file->store(
                $user->storage, 's3'
              );
            } else {
              $filepath = $file->store('public');
            }
            $url = Storage::url($filepath);
            $new_data = $url;
        }

        if ($file_upload) {
            $aspect->aspect_source = $new_data;
        } else {
            $aspect->aspect_source = $request->input('aspect_source');
        }

        $aspect->hidden = $request->input('hidden');
        $aspect->title = (!empty($request->input('title'))) ? $request->input('title') : '';
        // a default display weight of 99 will always list the new aspect first.
        $aspect->display_weight = 99;
        // Who does this aspect belong to?
        $aspect->user = Auth::id();
        // fire the pre-save hook, if it's there.
        $aspect->pre_save($request);
        // Save the record to the database
        $aspect->save();
        // fire the post-save hook, if it's there.
        $aspect->post_save($request);
        // attach the aspect to the subject.
        $subject = Subject::find($request->input('subject_id'));
        $subject->aspects()->attach($aspect->id);
        // Let's get back to the subject at hand.
        $request->session()->flash('message', 'Aspect saved.');
        return redirect('/subject/'.$subject->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $aspect = Aspect::findOrFail($id);
        $subject =$aspect->subjects()->firstOrFail();
        $back_to_subject = '<p><a href="/subject/'.$subject->id.'" class="btn btn-default"> << Return to '.$subject->name.'</a></p>';
        $customform = $back_to_subject . $aspect->edit_form($id);
        return view('forms.basic', ['form' => $customform, 'title'=>'Edit this '.$aspect->aspect_type()->aspect_name.' Aspect']);
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
        $aspect = Aspect::findOrFail($id);

        //fire pre-update if available;
        $aspect->pre_update($request);

        $aspect->aspect_type = $request->input('aspect_type');
        $aspect->aspect_data = $request->input('aspect_data');

        $settings_array = (!is_null($aspect->aspect_notes)) ? json_decode($aspect->aspect_notes, true) : json_decode($aspect->notes_schema(), true);

        $request_array = $request->all();
        $schema_array = json_decode($aspect->notes_schema(), true);

        if (is_array($schema_array)) {
            foreach ($schema_array as $key => $setting){
                $setting = 'settings_'.$key;
                if (!empty($request_array[$setting]) ) {
                    $settings_array[$key] = $request_array[$setting];
                } else {
                    $settings_array[$key] = '';
                }
            }
        }
        $aspect->aspect_notes = $settings_array;
        $aspect->title = (!empty($request->input('title'))) ? $request->input('title') : '';

        // Sometimes, we'll have a file attached.
        // In that case, we're going to store the file title in the aspect data, and the file path in the aspect_source fields.
        $file_upload = false;
        if ($request->hasFile('file_upload')) {
            $file_upload = true;
            $file = $request->file('file_upload');
            $filepath = $file->store('public');
            $url = Storage::url($filepath);
            $new_data = $url;
        }

        if ($file_upload) {
            $aspect->aspect_source = $new_data;
        } else {
            $aspect->aspect_source = $request->input('aspect_source');
        }

        $aspect->hidden = $request->input('hidden');

        $aspect->update_aspect();

        //fire post_update if available;
        $aspect->post_update($request);

        $subject = $aspect->subjects()->first();
        $request->session()->flash('message', 'Aspect saved.');
        return redirect('/subject/'.$subject->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $garbage_aspect = Aspect::find($id);
        // fire the pre-delete hook, if available
        $garbage_aspect->pre_delete($request);

        $origin = $garbage_aspect->subjects()->first();
        $garbage_aspect->subjects()->detach();
        $garbage_aspect->delete();
        $request->session()->flash('message', 'Aspect deleted.');
        return redirect('/subject/'.$origin->id);
    }
}
