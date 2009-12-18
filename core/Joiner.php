<?
namespace core;

/**
 * 
 */
class Joiner
{
    protected $objects= array();
    protected $child_class;
    protected $parent;
    protected $parent_class;
    
    protected $habtm_rows;
    protected $is_habtm;
    
    /**
     * HABTM: $parent_object, $parent_class, $child_class required
     * non-HABTM: Not required
     */
    function __construct($parent_object=null, $parent_class=null, $child_class=null)
    {
        // for habtm:
        if ($parent_object and $parent_class and $child_class)
        {
            $this->habtm= true;
            
            $lookup_id= $parent->id;
            $lookup_field= $parent_class.PK_SEPERATOR.PRIMARY_KEY;  
            
            $foreign_object= $child_class;
            $foreign_key= $child_class.PK_SEPERATOR.PRIMARY_KEY;
            
            $qry= DB::query("select * from ".$parent->get_join_table($field_name)." where ".$lookup_field."='".$lookup_id."'");
            
            $field_list= array();
            $objects = array();
            
            while($row= DB::fetch_assoc($qry))
            {
                foreach($row as $k=>$v)
                {
                    if ($k == $foreign_key)
                    {
                        $child_obj= new $child_class($v);
                        $field_list["_object"]= $child_obj;
                    }
                    $field_list[$k]= $v;
                }
                $this->habtm_rows[]= $field_list;
            }
        }
        else
            $this->habtm= false;
    }
    
    /**
     * non-HABTM: Set the child class name for when we dynamically create new children:
     * This will probably only be used by core/Model
     */
    function set_child_class($child)
    {
        $this->child_class= $child;
    }
    
    /**
     * non-HABTM: Set up the parent object and classname
     * This will probably only be used by core/Model
     */
    function set_parent($obj, $classname)
    {
        $this->parent= $obj;
        $this->parent_class= $classname;
    }
    
    /**
     * non-HABTM: the protected $objects field stores the related objects.
     * This will probably only be used by core/Model
     */
    function set_objects($obj)
    {
        $this->objects= $obj;
        
        if ($this->habtm_table)
        {
            foreach($this->objects as $obj)
            {
                $this->add_habtm($obj);
            }
        }
    }
    
    /**
     * Add an object to this collection by specifying it in an array of the form:
     * array("column"=>"value")
     * This will auto assign the object to the parent's key.
     */
    function add_array($arr)
    {
        if ($this->habtm == false)
        {
            $child_class= $this->child_class;
            $parent_id_field= $this->parent_class.PK_SEPERATOR.PRIMARY_KEY;
        
            $obj= new $child_class();
            $obj->populate_from($arr);
            $obj->$parent_id_field= $this->parent->id;
            $obj->store();
        
            $this->objects[]= $obj;
        }
        else
            $this->add_habtm($obj, $arr);
    }
    
    /**
     * Add an object to this collection by specifying an object.
     * Additional columns can be stored with the $arr column=>value array
     * if this is a HABTM relationship.
     * This will auto assign the object to the parent's key.
     */
    function add_object($obj, $arr=null)
    {
        if ($this->habtm == false)
        {    
            $parent_id_field= $this->parent_class.PK_SEPERATOR.PRIMARY_KEY;
            $obj->$parent_id_field= $this->parent->id;
            
            $obj->store();
            $this->objects[]= $obj;
        }
        else
            $this->add_habtm($obj, $arr);
    }
    
    private function add_habtm($obj, $arr)
    {
        // TODO
    }
    
    /**
     * Returns an array of found objects. Pass in an array like array("column"=>"value"), e.g. array("id"=>2). 
     *  Pass null to return all.
     */
    function find($where= null)
    {
        $results= array();
        // return all:
        if ($where == null)
        {
            if ($this->habtm == false)
                return $this->objects;    
        }
        
        foreach($where as $search_field=>$search_value)
        {
            if ($this->habtm == false)
            {
                foreach($this->objects as $k=>$o)
                {
                    if (strstr($search_value,$o->$search_field) !== false)
                    {
                        $results[]= $o;
                    }
                }
            } // end if habtm
        } // end foreach
        
        return $results;
    }
    
    /**
     * Returns the first object found. Pass in an array like array("column"=>"value"), e.g. array("id"=>2)
     */
    function find_object($where)
    {
        foreach($where as $search_field=>$search_value)
        {
            if ($this->habtm == false)
            {
                if (count($this->objects) > 0)
                {
                    foreach($this->objects as $k=>$o)
                    {
                        if (strstr($search_value,$o->$search_field) !== false)
                        {
                            return $o;
                        }
                    }
                }
            } // end if habtm
        } // end foreach
        return false; // not found
    }
    
    /**
     * Shorthand for belongs to
     */
    function get()
    {
        return $this->objects[0];
    }
    
}
?>