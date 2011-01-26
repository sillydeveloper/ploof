<?
class BelongsTo extends \core\Model
{
    protected $belongs_to= array('HasMany');
    
    static function classname()
    {
        return __CLASS__;
    }
}

?>