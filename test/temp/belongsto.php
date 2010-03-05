<?
class belongsto extends \core\Model
{
    protected $belongs_to= array('hasmany');
    static function classname()
    {
        return __CLASS__;
    }
}
?>