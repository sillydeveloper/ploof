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

namespace core;

class Meta 
{
   /** 
    *  Returns the classname without the namespace. 
    *
    *  @param object|string  $obj    Object or classname from which to retrieve name 
    *  @return string
    */
    public static function classname_only($obj)
    {
        if (!is_object($obj) && !is_string($obj)) {
            return false;
        }
        
        $class = explode('\\', (is_string($obj) ? $obj : get_class($obj)));
        return $class[count($class) - 1];
    }

    public function convert_controller_to_object_name($name)
    {
        return substr($name, 0, strlen($name)-1);
    }

   /** 
    * Get the namespace
    */
    public static function namespace_only($classname)
    {
	    return preg_replace("/(\\\\[A-Za-z0-9]*)$/", "", $classname);
    }
    
    /**
     * Include $file if it is found in the path,
     *  and return whether or not it was included.
     */
    public static function include_if_found($file)
    {
        $includables = explode(PATH_SEPARATOR, get_include_path());
        foreach ($includables as $path) 
        {
            if (substr($path, -1) == DIRECTORY_SEPARATOR) 
                $fullpath = $path.$file;
            else
                $fullpath = $path.DIRECTORY_SEPARATOR.$file;

            if (file_exists($fullpath)) 
            {
                include_once $fullpath;
                return true;
            }
        }
        return false;
    }

}

?>
