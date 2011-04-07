<?
class NoCache extends \core\Model
{
    static protected $no_cache= array('name');
    
    static function classname()
    {
        return __CLASS__;
    }
}

?>