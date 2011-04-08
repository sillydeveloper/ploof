<?
namespace core;

class HasManyWrapper
{
    protected $parent_object;
    protected $parent_class;
    protected $child_class;
    protected $lookup_id;
    protected $lookup_field;
    
    function __construct($parent_object, $child_class)
    {   
        $this->parent_object= $parent_object;
        $this->parent_class= $parent_object::cname();
        $this->child_class= $child_class;
        $f= $parent_object->get_fields();
        $this->lookup_id= $f[PRIMARY_KEY];
        $this->lookup_field= $parent_object::cname().PK_SEPARATOR.PRIMARY_KEY;                
    }
    
    /**
     * Find and return an object. 
     */
    function find_object($query)
    {
        $query[$this->lookup_field]= $this->lookup_id;
        $c= $this->child_class;
        $results= $c::find_object($query);

        if ($results)
            return $results;
            
        return false;
    }
    
    /**
     * Find and return an array of objects.
     */
    function find($query=null)
    {
        $returns= array();
        $query[$this->lookup_field]= $this->lookup_id;
        $c= $this->child_class;
        $results= $c::find($query);
        return $results;
    }
     
     /** 
      * Shortcut for Meta::classname_only(static::classname())
      */
     static function cname()
     {
         return Meta::classname_only(static::classname());
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