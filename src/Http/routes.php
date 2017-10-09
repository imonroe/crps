<?php


use Illuminate\Http\Request;
use imonroe\crps\Aspect;
use imonroe\crps\AspectType;

Route::namespace('imonroe\crps\Http\Controllers')->group(
    function () {
        Route::middleware(['web'])->group(
            function () {
                // Subject routes:
                Route::get('/subject/autocomplete', 'SubjectController@autocomplete'); // subject autocompleter
                Route::post(
                    '/subject/aspect_sorter', function (Request $request) {
                        $posted_data = $request->all();
                        $aspect_ids = $posted_data['aspect_id'];
                        // There's got to be a better way to do this than performing all these queries, right?
                        foreach ($aspect_ids as $key => $value){
                            $aspect = Aspect::find($value);
                            $aspect->display_weight = (int) $key + 100;
                            $aspect->update_aspect();
                        }
                    }
                );

                Route::get('/subject', 'SubjectController@index');  // subject index
                Route::get('/codex', 'SubjectController@index');  //codex alias

                Route::get('/subject/create', 'SubjectController@create'); // new subject form
                Route::get('/subject/create/{subject_type_id}', 'SubjectController@create'); // new subject form
                Route::get('/subject/create_from_search/{query}', 'SubjectController@create_from_search'); // new subject form
                Route::post('/subject/create', 'SubjectController@store');  // new subject form handler
                Route::get('/subject/{id}', 'SubjectController@show');  // single subject view
                Route::get('/subject/{id}/edit', 'SubjectController@edit');  // edit subject form
                Route::post('/subject/{id}/edit', 'SubjectController@update');  // edit subject form handler
                Route::get('/subject/{id}/delete', 'SubjectController@destroy');  // delete subject form handler
                Route::get('/subjects/codex', 'SubjectController@get_codex_array');
                Route::get('/subjects/codex/{subject_id}', 'SubjectController@get_codex_array');

                // Subject type routes:
                Route::get('/subject_type', 'SubjectTypeController@index');  // aspect_type index
                Route::get('/subject_type/create', 'SubjectTypeController@create'); // new aspect_type form
                Route::post('/subject_type/create', 'SubjectTypeController@store');  // new aspect_type form handler
                Route::get('/subject_type/{id}', 'SubjectTypeController@show');  // single aspect_type view
                Route::get('/subject_type/{id}/edit', 'SubjectTypeController@edit');  // edit aspect_type form
                Route::post('/subject_type/{id}/edit', 'SubjectTypeController@update');  // edit aspect_type form handler
                Route::get('/subject_type/{id}/delete', 'SubjectTypeController@destroy');  // delete aspect_type form handler

                // Aspect routes:
                Route::get('/aspect/create/{subject_id}', 'AspectController@create'); // new aspect form
                Route::get('/aspect/create/{subject_id}/type/{aspect_type_id}', 'AspectController@create_with_type'); // new aspect form
                Route::post('/aspect/create', 'AspectController@store'); // new aspect form handler
                Route::get('/aspect/{id}/edit', 'AspectController@edit');  // edit aspect form
                Route::post('/aspect/{id}/edit', 'AspectController@update');  // edit aspect form handler
                Route::get('/aspect/{id}/delete', 'AspectController@destroy');  // delete aspect form handler

        				Route::post('/aspect/{id}/fold', function ($id, Request $request) {
        					$aspect = Aspect::find($id);
        					if ( $aspect->folded ){
        						$aspect->folded = 0;
        					} else {
        						$aspect->folded = 1;
        					}
        					$aspect->update_aspect();
        					//dd($aspect);
        				});

                // Aspect type routes:
                Route::get('/aspect_type', 'AspectTypeController@index');  // aspect_type index
                Route::get('/aspect_type/create', 'AspectTypeController@create'); // new aspect_type form
                Route::post('/aspect_type/create', 'AspectTypeController@store');  // new aspect_type form handler
                Route::get('/aspect_type/{id}', 'AspectTypeController@show');  // single aspect_type view
                Route::get('/aspect_type/{id}/edit', 'AspectTypeController@edit');  // edit aspect_type form
                Route::post('/aspect_type/{id}/edit', 'AspectTypeController@update');  // edit aspect_type form handler
                Route::get('/aspect_type/{id}/delete', 'AspectTypeController@destroy');  // delete aspect_type form handler
                Route::post('/aspect_type/add_aspect_jump_menu', function (){
                  $output = AspectType::get_options_array('json');
                  //dd($output);
                  return $output;
                });

                // Search routes
                Route::get('/search', 'SearchController@index');
                Route::post('/search', 'SearchController@basic_search_handler');
            }
        );
    }
);
