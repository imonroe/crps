<?php
namespace imonroe\crps;

class SearchRegistry
{

    public $search_providers;

    public function __construct()
    {
        $this->search_providers = array();
    }

    public function register_search_class($object, int $weight = 99)
    {
      // Add the provider to the array.
        $this->search_providers[] = array(
        'class' => $object,
        'weight' => $weight,
        );
      // Sort the array by weight.
        usort($this->search_providers, function ($a, $b) {
            if ($a['weight'] == ['weight']) {
                return 0;
            }
            return ($a['weight'] < $b['weight']) ? -1 : 1;
        });
    }

    public function search($query)
    {
        $output = '';
        foreach ($this->search_providers as $provider) {
            $output .= $provider['class']->query($query);
        }
        return $output;
    }
}


/*
  Here's a parent class we can extend for various search providers.
*/
class SearchProvider
{
    public $query;
    public $prepend_markup;
    public $results_markup;
    public $results;
    public $append_markup;
    public $results_limit;

    public function __construct()
    {
        $this->results_limit = 25;
    }

    public function query($query)
    {
        $this->query = $query;
      /*
      Here, we do whatever query we need to do, and we format the results markup.
      e.g.:
      $markup = '';
      $this->results = Subject::where('name', 'LIKE', '%'.$query.'%')->limit($this->results_limit)->get();
      if ($this->results->count() > 0){
        $markup .= '<h3 style="clear:both;"> Subjects </h3>'.PHP_EOL;
        $markup .= '<ul>'.PHP_EOL;
        foreach ($this->results as $subject){
        $markup .= '<li> <a href="/subject/' . $subject->id . '">' . $subject->name .'</a> </li>'.PHP_EOL;
        }
        $markup .= '</ul>'.PHP_EOL;
      }
      $this->results_markup = $markup;
      return $this->prepend_markup . $this->results_markup . $this->append_markup . PHP_EOL;
      */
    }
}
