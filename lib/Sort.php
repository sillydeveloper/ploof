<?php

class Sort
{

    /**
    * Sorts an array of objects, in place, using a user-defined sortkey function
    *     
    * @param array $objects Array of objects to be sorted in place
    * @param string $direction 'D' for descending, otherwise defaults to ascending sort
    * @param function $sortkey_function annonymous function that takes object as sole parameter and returns sortkey
    * 
    * Sortkey can return either a numeric or string, but must be the same for all objects in this sort
    */
    public function by_method(&$objects, $direction='A', $sortkey_function)
    {
        // Build array of pairs ('sortkey', 'object')
        $meta_array = array();
        foreach ($objects as $obj) $meta_array[] = array('sortkey'=>$sortkey_function($obj), 'object'=>$obj);
        
        // Sort array of pairs
        if ($direction == 'D') 
        {
            usort($meta_array, function($a, $b)
                { 
                    return ($a['sortkey']==$b['sortkey'] ? 0 : ($a['sortkey']<$b['sortkey'] ? 1 : -1)); 
                } );
        }
        else 
        {
            usort($meta_array, function($a, $b)
                { 
                    return ($a['sortkey']==$b['sortkey'] ? 0 : ($a['sortkey']<$b['sortkey'] ? -1 : 1)); 
                } );
        }
        
        // Flatten array of pairs back into original array of object
        $objects = array();
        foreach ($meta_array as $meta_obj) $objects[] = $meta_obj['object'];
    }

}
