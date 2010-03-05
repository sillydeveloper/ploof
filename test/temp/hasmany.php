<?
class hasmany extends \core\Model
{
    protected $has_many= array('belongsto');
    static function classname()
    {
        return __CLASS__;
    }
}
?>