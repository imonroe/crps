<?php


namespace imonroe\crps;
use imonroe\crps\Aspect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
/*
  Here's a parent class we can extend for various search providers.
*/
class AspectSearchProvider extends \imonroe\crps\SearchProvider {
  public function __construct(){
    $this->prepend_markup = '<h4 style="clear:both;"> Aspects </h4><p>Your query appears in aspects of these Subjects.</p>'.PHP_EOL;
    $this->results_limit = 99;
    $this->append_markup = '<hr style="clear:both;" />'.PHP_EOL;
  }

  public function query($query){
    $this->query = $query;
    /*
    Here, we do whatever query we need to do, and we format the results markup.
    e.g.:
    */
    $markup = '';

    $id = Auth::id();

    $sql = "
      SELECT subjects.id as subject_id, aspects.title as aspect_title, subjects.name as subject_title, subjects.description as subject_description
      FROM aspects
      INNER JOIN aspect_subject
      ON aspects.id = aspect_subject.aspect_id
      INNER JOIN subjects
      ON aspect_subject.subject_id = subjects.id
      WHERE aspects.user = :user
      AND (
	 	     aspects.aspect_data LIKE :query1
		     OR aspects.title LIKE :query2
      )";

    $results_array = DB::select($sql, ['user' => $id, 'query1' => '%'.$query.'%', 'query2' => '%'.$query.'%']);
    // Extract the unique subject ids.
    $subject_ids_array = array_unique( array_column($results_array, 'subject_id') );
    // This is our final results array.
    $results = array();

    foreach ($subject_ids_array as $subject_id){
      // Alright, this gets a little complicated.

      // we're going to be running through all the records we got in the $results_array,
      // for each subject, so we need to track what's relevant to this iteration of the loop.
      $relevant_aspects = array();

      // Run through the $results_array, and if we find a subject ID that matches, populate
      // some temporary variables.
      foreach ($results_array as $potentially_relevant){
        if ($potentially_relevant->subject_id = $subject_id){
          // most of this is repetitive, so we don't care that we are overrideing it each time.
          $sid = $potentially_relevant->subject_id;
          $stitle = $potentially_relevant->subject_title;
          $atitle = $potentially_relevant->aspect_title;
          $sdesc = $potentially_relevant->subject_description;
          $relevant_aspects[] = $atitle;
        }
      }
      // Move our temporary variables into our master array.
      $results[] = array(
        'subject_id' => $subject_id,
        'subject_title' => $stitle,
        'subject_description' => $sdesc,
        'aspects' => $relevant_aspects,
      );
    }

    // We have collected all the data we need.
    $this->results = $results;

    if ( count($this->results) > 0 ){
      $markup .= '<ul>'.PHP_EOL;
      foreach ($this->results as $subject){
        $markup .= '<li>'.PHP_EOL;
        $markup .= '<a href="/subject/' . $subject['subject_id'] . '">' . $subject['subject_title'] .'</a>'.PHP_EOL;
        $markup .= '<br />'.$subject['subject_description'].PHP_EOL;
        foreach ($subject['aspects'] as $aspect_title){
          $markup .= '<br /> -->'.$aspect_title . PHP_EOL;
        }
        $markup .=  '</li>'.PHP_EOL;
      }
      $markup .= '</ul>'.PHP_EOL;
      $this->results_markup = $markup;
      return $this->prepend_markup . $this->results_markup . $this->append_markup . PHP_EOL;
    } else {
      return $markup;
    }

  }
}
