<?php
// Copyright (c) 2010, ploof development team
// All rights reserved.
// 
// Redistribution and use in source and binary forms, with or without modification, are permitted provided 
// that the following conditions are met:
// 
// Redistributions of source code must retain the above copyright notice, this list of conditions and the 
// following disclaimer. 
// Redistributions in binary form must reproduce the above copyright notice, this list of 
// conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
// The names of its contributors may not be used to endorse or promote products derived from this software without 
// specific prior written permission.
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, 
// INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
// ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, 
// INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE 
// GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
// LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY 
// OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

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
