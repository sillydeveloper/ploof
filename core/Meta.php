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

}

?>
