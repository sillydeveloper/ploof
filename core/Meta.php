<?php
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
    function include_if_found($file)
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
