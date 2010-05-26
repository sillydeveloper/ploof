<?
namespace core;    

class Ploof
{
    static function debug($level, $msg)
    {
        if ($level <= DEBUG_LEVEL)
        {
            echo "<pre>";
            echo static::classname()."($level): ";
            print_r($msg);
            echo "</pre>";
        }
    }
    
    /**
     * Get this objects static classname (PHP5.3 only)
     */
    static function classname()
    {
        return __CLASS__;
    }
}

?>