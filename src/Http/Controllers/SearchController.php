<?php

namespace imonroe\crps\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use imonroe\crps\Aspect;
use imonroe\crps\AspectFactory;
use imonroe\crps\AspectType;
use imonroe\crps\Subject;

class SearchController extends Controller
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

    /*
    *   The basic web search pulls results from DuckDuckGo to try to provide an abstract on a search subject.
    */
    public function web_search($query)
    {
        $duckduck_api_key = env('MASHAPE_API_KEY');
        $duckduck_api_endpoint = env('DUCKDUCK_API_ENDPOINT');
        $query_construction = $duckduck_api_endpoint.'?format=json&no_html=1&no_redirect=1&q='.urlencode($query).'&skip_disambig=1';
        $opts = array('Accept: application/json', 'X-Mashape-Key: '.$duckduck_api_key);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $query_construction);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
        //curl_setopt($curl, CURLOPT_USERAGENT, $app['user-agent']);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $opts);
        $results = curl_exec($curl);
        curl_close($curl);
        $output = json_decode($results, true);
        return $output;
    }

    public function index(Request $request)
    {
      // make a basic search page.
    }

    /*
    *   The controller function to get all the pieces and generate the view.
    */
    public function show_search_results(Request $request)
    {
        $query = $request->input('query');
        $search_registry = app()->make('SearchRegistry');
        $search_results = $search_registry->search($query);
        return view(
            'search.results',
            ['title'=>'Search Results for: '.$query, 'results_markup' => $search_results, ]
        );
    }

    public function get_subject_results($query)
    {
        return Subject::where('name', 'LIKE', '%'.$query.'%')->get();
    }
}
