<?php
namespace imonroe\crps;

use imonroe\crps\Subject;

/*
  Here's a parent class we can extend for various search providers.
*/
class SubjectSearchProvider extends \imonroe\crps\SearchProvider
{
    public function __construct()
    {
        $this->prepend_markup = '<h4 style="clear:both;"> Subjects </h4>'.PHP_EOL;
        $this->results_limit = 25;
        $this->append_markup = '<hr style="clear:both;" />'.PHP_EOL;
    }

    public function query($query)
    {
        $this->query = $query;
      /*
      Here, we do whatever query we need to do, and we format the results markup.
      e.g.:
      */
        $markup = '';
        $this->results = Subject::where('name', 'LIKE', '%'.$this->query.'%')->limit($this->results_limit)->get();
        if ($this->results->count() > 0) {
            $markup .= '<ul>'.PHP_EOL;
            foreach ($this->results as $subject) {
                $markup .= '<li> <a href="/subject/' . $subject->id . '">' . $subject->name .'</a> </li>'.PHP_EOL;
            }
            $markup .= '</ul>'.PHP_EOL;
            $this->results_markup = $markup;
            return $this->prepend_markup . $this->results_markup . $this->append_markup . PHP_EOL;
        } else {
            return $markup;
        }
    }
}
