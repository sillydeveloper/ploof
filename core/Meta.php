<?php
namespace core;

class Meta 
{
   /** 
    * Eliminate any namespacing, just return the classname.
    */
    public static function classname_only($classname)
    {
	return preg_replace("/([A-Za-z0-9]*\\\\)/", "", $classname);
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
