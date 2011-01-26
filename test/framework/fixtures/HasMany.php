<?
class HasMany extends \core\Model
{
    protected $has_many= array('BelongsTo');
    
    static function classname()
    {
        return __CLASS__;
    }
}

?>