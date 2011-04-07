<?
class BelongsTo extends \core\Model
{
    static protected $belongs_to= array('HasMany');
    
    static function classname()
    {
        return __CLASS__;
    }
}

?>