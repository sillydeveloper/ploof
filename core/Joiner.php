<?
namespace core;

/**
 * Provides helper methods to joined objects.
 */
class Joiner extends Ploof
{
    protected $objects= array();
    protected $child_class;
    protected $parent;
    protected $parent_class;
    
    protected $habtm_rows;
    protected $is_habtm= false;
    
    /**
     * HABTM: $parent_object, $parent_class, $child_class required
     * non-HABTM: Not required
     */
    function __construct($parent_object=null, $parent_class=null, $child_class=null)
    {
        // for habtm:
        if ($parent_object and $parent_class and $child_class)
        {
            $this->is_habtm= true;
            
            $lookup_id= $parent->id;
            $lookup_field= $parent_class.PK_SEPARATOR.PRIMARY_KEY;  
            
            $foreign_object= $child_class;
            $foreign_key= $child_class.PK_SEPARATOR.PRIMARY_KEY;
            
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
            $this->is_habtm= false;
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
        
        if ($this->is_habtm)
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
    function add_array($arr, $index=0)
    {
        if ($this->is_habtm == false)
        {
            $child_class= $this->child_class;
            $parent_id_field= $this->parent_class.PK_SEPARATOR.PRIMARY_KEY;
            
            $obj= new $child_class();
            $obj->populate_from($arr, $index);
            $obj->$parent_id_field= $this->parent->id;
            $obj->store();
        
            $this->objects[]= $obj;
        }
        else
            $this->add_habtm($obj, $arr);

		return $obj;
    }
    
    /**
     * Add an object to this collection by specifying an object.
     * Additional columns can be stored with the $arr column=>value array
     * if this is a HABTM relationship.
     * This will auto assign the object to the parent's key.
     */
    function add_object($obj, $arr=null)
    {
        if ($this->is_habtm == false)
        {    
            $parent_id_field= $this->parent_class.PK_SEPARATOR.PRIMARY_KEY;
            $obj->$parent_id_field= $this->parent->id;
            
            $obj->store();

            $this->objects[]= $obj;
        }
        else
            $this->add_habtm($obj, $arr);

		return $obj;
    }
    
    private function add_habtm($obj, $arr)
    {
        // TODO
    }
    
    /**
     * Returns an array of found objects. Pass in an array like array("column"=>"value"), e.g. array("id"=>2). 
     *  Pass null to return all.
     */
    function find($where = null, $exclude = null)
    {
        $results= array();
        // return all:
        if ($where == null and $exclude == null)
        {
            if ($this->is_habtm == false)
                return $this->objects;    
        }
        if ($this->is_habtm == false and $this->objects)
        {
            foreach($this->objects as $k=>$o)
            {
                // Must match all values in where
                $found_match = 0;
                if ($where !== null)
                {
                    foreach($where as $search_field=>$search_value)
                    {
                        $obj_value = $o->$search_field;
                        if (($search_value === null and $obj_value === null) 
                            or ($obj_value == $search_value)
                            or ($search_value and $obj_value and strcmp($search_value, $obj_value) == 0))
                        {
                            $found_match++;
                        }
                    } // end foreach where
                }

                // Must not have all values in exclude
                $excluded = false;
                if ($exclude !== null)
                {
                    $found_exclude = 0;
                    foreach($exclude as $search_field=>$search_value)
                    {
                        $obj_value = $o->$search_field;
                        if (($search_value === null and $obj_value === null) 
                            or ($obj_value == $search_value)
                            or ($search_value and $obj_value and strcmp($search_value, $obj_value) == 0))
                        {
                            $found_exclude++;
                        }
                    } // end foreach exclude
                    if ($found_exclude == count($excluded)) $excluded = true;
                }

                if ($found_match == count($where) and !$excluded)
                {
                    $results[]= $o;
                }
                    
            } // end foreach object
            
            if (count($results) < 1 and $this->parent->requires_a($this->child_class))
            {
                $c= $this->child_class;
                $obj= new $c();
                foreach($where as $k=>$v) $obj->$k= $v;
                $obj= $this->add_object($obj);
                $obj->store();
                $results[]= $obj;
            }
        } // end if habtm
        
        return $results;
    }
    
    /**
     * Returns the first object found. Pass in an array like array("column"=>"value"), e.g. array("id"=>2)
     */
    function find_object($where)
    {
        $search= $this->find($where);
        $obj= array_pop($search);

        if (!$obj->id and $this->parent->requires_a($this->child_class))
        {
            $c= $this->child_class;
            $obj= new $c();
            foreach($where as $k=>$v) $obj->$k= $v;
            $obj= $this->add_object($obj);
            $obj->store();            
        }
        
        return $obj;
    }
    
    /**
     * Shorthand for belongs to
     */
    function get()
    {
        if (count($this->objects) < 1 and $this->parent->requires_a($this->child_class))
        {
            $c= $this->child_class;
            $obj= new $c();
            $this->add_object($obj);
        }
            
		$obj= $this->objects[0];
		
        return $obj;
    }
    
    /**
     * Remove items
     */ 
    function delete($id_array=null)
    {
        if ($this->objects)
        {
            foreach($this->objects as $k=>$o)
            {
                if (!$id_array or array_search($o->id, $id_array) !== false)
                {
                    $o->delete();
                    unset($this->objects[$k]);
                }
            }
        }
    }
    
    /**
    * Delete array of objects from joiner/cache and from database
    * 
    * @param array $objects Array of object to delete from joiner/cache and database (not assoc array)
    */
    function delete_objects($objects)
    {
        if (!is_array($objects)) return;
        $ids = array();
        foreach ($objects as $obj) 
        {
            if (!is_object($obj)) continue;
            $ids[] = $obj->id;
        }
        $this->delete($ids);
    }
        
}
?>
