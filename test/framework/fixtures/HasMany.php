<?
class HasMany extends \core\Model
{
    static protected $has_many= array('BelongsTo');
    
    static function classname()
    {
        return __CLASS__;
    }
}

?>