<?

class Math
{

   /**
    * Returns a sum of $closure_function applied to all $elements
    * 
    * @param mixed $elements            array of objects or values
    * @param mixed $closure_function    annonymous function that accepts single parameter and return numeric value 
    */
    public function sum_closure($elements, $closure_function)
    {
	$sum = 0;
	foreach ($elements as $element) {
	    $sum += $closure_function($element);
	}
	return $sum;
    }

}

?>
