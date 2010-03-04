<?
namespace core;

class Model
{
    protected $fields= null;
    protected $field_types= null;
    protected $has_many= null;
    protected $has_one= null;
    protected $has_and_belongs_to_many= null; 
    
    protected $validates= null;
    
    protected $db_connector_preamble= '';
    
    protected $getter_override= null;
    
    function __construct($id=null)
    {
        if ($id)
        {
            $sql= "select * from ".classname_only(static::classname())." where ".PRIMARY_KEY."=".$id;
            
            $this->debug(5, "Loading:". $sql);
            
            $qry= DB::query($sql);
            $this->debug(5, $qry);
            
            while($row= DB::fetch_assoc($qry))            
            {   
                $this->debug(5, $row);
                foreach($row as $k=>$v)
                {
                    $this->fields[$k] = stripslashes($v);
                }
            }
            $this->set_field_types();
        }
        elseif (count($this->fields) < 1)
        {
            $this->set_field_types(true);
        }   
    }
    
    private function set_field_types($nullify= false)
    {
        $qry= DB::query("show columns from ".classname_only(static::classname()));
        while($row= DB::fetch_assoc($qry))
        {
            if ($nullify)
                $this->fields[$row["Field"]]= null;
                
            $this->field_types[$row["Field"]]= preg_replace("/\(([0-9])*\)/", "", $row["Type"]);
        }
    }
    
    public function get_fields()
    {
        return $this->fields;
    }
    
    public function is_numeric($field)
    {
        $numeric= array("tinyint","bigint","int", "float");
        return (array_search($this->get_field_type($field), $numeric) !== false);
    }
    
    public function get_field_type($field)
    {
        return $this->field_types[$field];
    }
    
    /**
     *  Whether or not a field has _any_ foreign key characteristics (has_many, has_one, habtm) 
     */
    function is_foreign($field_name)
    {
        return ($this->is_has_many($field_name) || $this->is_has_one($field_name) 
            || $this->is_habtm($field_name) || $this->is_belongs_to($field_name));
    }
    
    /**
     * Whether or not a field is a belongs_to relationship
     */
    function is_belongs_to($field_name)
    {
        if (count($this->belongs_to) > 0 and array_search($field_name, $this->belongs_to) !== false)
            return true;
        return false;
    }
    
    /**
     *  Whether or not a field is a has_many relationship
     */
    function is_has_many($field_name)
    {
        if (count($this->has_many) > 0 and array_search($field_name, $this->has_many) !== false)
            return true;
        return false;
    }
    
    /**
     * Whether or not a field is a has_one relationship
     */
    function is_has_one($field_name)
    {
        if (count($this->has_one) > 0 and array_search($field_name, $this->has_one) !== false)
            return true;
        return false;
        
    }
    
    /**
     * Whether or not a field is a habtm relationship
     */
    function is_habtm($field_name)
    {
        if (count($this->has_and_belongs_to_many) > 0 and array_search($field_name, $this->has_and_belongs_to_many) !== false)
            return true;
        return false;        
    }
    
    /**
     * Generate the join table name for field_name
     * TODO: Change this method name to get_habtm_join_table
     */
    function get_join_table($field_name)
    {
        $my_class = classname_only(static::classname());
        return ($field_name > $my_class) ? $my_class.PLOOF_SEPARATOR.$field_name : $field_name.PLOOF_SEPARATOR.$my_class;
    }
    
    /**
     * If a field_name is foreign, then refresh the data it points to.
     * Used to make sure cached data is up-to-date.
     */
    function refresh($field_name, $order= null, $limit= null)
    {   
        // Check to see if we need to override the geter by calling a different
        //  method:
        if ($this->getter_override and array_key_exists($field_name, $this->getter_override))
        {
            $method= $this->getter_override[$field_name];
            $this->debug(5, "Calling override ".$method." for refresh of ".$field_name);
            $results= $this->$method($order, $limit);
        }
        elseif($this->is_habtm($field_name))
        {   
            // joiner handles everything for habtm since it has to load
            //  and know about extra fields in the join table.
            $joiner= new Joiner($this, classname_only(static::classname()), $field_name);
        }
        else 
        {
            if ($this->is_belongs_to($field_name))
            {
                $lookup_id= $this->fields[$field_name.PK_SEPERATOR.PRIMARY_KEY];
                $lookup_field= PRIMARY_KEY;
            
                $results= $field_name::find($lookup_field."='".$lookup_id."'");
            }
        
            if ($this->is_has_many($field_name))
            {
                $lookup_id= $this->fields[PRIMARY_KEY];
                $lookup_field= classname_only(static::classname()).PK_SEPERATOR.PRIMARY_KEY;
            
                $sql= $lookup_field."='".$lookup_id."' ".$order." ".$limit;
            
                $this->debug(5, $field_name." sql=".$sql);
            
                $results= $field_name::find($sql);
            }
        
            if ($this->is_has_one($field_name))
            {
                $lookup_id= $this->fields[PRIMARY_KEY];
                $lookup_field= classname_only(static::classname()).PK_SEPERATOR.PRIMARY_KEY;
            
                $results= $field_name::find($lookup_field."='".$lookup_id."' limit 1");
            }
        }
        
        if (!$joiner)
        {
            $joiner= new Joiner();
            $joiner->set_objects($results);
            $joiner->set_parent($this, classname_only(static::classname()));
            $joiner->set_child_class($field_name);            
        }
        
        $this->fields[$field_name]= $joiner;
    }
    
    /**
     * Generic __get. All $obj->property calls come through here.
     */
    function __get($field_name)
    {
        // lazy loading part.
        if ($this->is_foreign($field_name))
        {
            if (is_object($this->fields[$field_name]) == false)
            {
                $this->debug(5, "Refreshing ".$field_name);
                $this->refresh($field_name);
            }
            else
            {
                $this->debug(5, "No refresh for ".$field_name);
            }
        }
        
        if (is_object($this->fields[$field_name]) and ($this->is_belongs_to($field_name) or $this->is_has_one($field_name)))
        {
            $this->debug(5, "Found is_belongs_to or is_has_one, returning get() for ".$field_name);
            // return the joiner's get, because there is only one:
            return $this->fields[$field_name]->get();
        }

        if ($this->field_types[$field_name] == "datetime")
            return format_date($this->fields[$field_name]);
            
        return $this->fields[$field_name];
    }
    
    /**
     * Generic __set. All $obj->property= $val calls come through here.
     *  Note that Controller handles the weatherstripping of user input.
     */
    function __set($field_name, $value)
    {
        if ($this->is_foreign($field_name))
        {
            if (is_object($this->fields[$field_name]) == false)
            {
                $this->debug(5, "Refreshing ".$field_name." for __set");
                $this->refresh($field_name);
            }
            else
                $this->debug(5, $field_name." already loaded for __set");

            // now add to joiner:
            $this->fields[$field_name]->add_object($value);
        }
        else
            $this->fields[$field_name]= $value;
    }
    
    /**
     *  Find and overwrite, or create and store, an object of $classname
     *      with $values_array that has a relationship to $this. 
     *      Use store() on an object, save() on a relationship.
     */
    function save($classname, $values_array)
    {
        if ($this->is_belongs_to($classname))
        {
            $this->debug(5, 'Saving '.$classname.' (belongs_to) with:');
            $this->debug(5, $values_array);
                        
            // Question: Do we need a refresh check here? No because $this->$classname calls
            //  the getter (which should be an object of the type described by the belongs_to)
            if (is_object($this->$classname))
            {
                $this->debug(5, $classname.' exists, id '.$this->$classname->id);
                // this is a belongs to, so only the first is item is touched:
                $this->$classname->populate_from($values_array[$classname]);
                $this->$classname->store();
            }
            else
            {
                $object= new $classname();
                $object->fields[PRIMARY_KEY]= null;
                $object->populate_from($values_array[$classname]);
                $object->store();
                
                $lookup_field= $classname.PK_SEPERATOR.PRIMARY_KEY;
                $this->$lookup_field= $object->id;
                $this->store();
                
                $this->debug(5, $classname.' does not exist; adding id '.$this->$classname->id);
            }
            $this->refresh($classname);
        } // end belongs_to
        if ($this->is_has_one($classname))
        {
            $this->debug(5, 'Saving '.$classname.' (has_one) with:');
            $this->debug(5, $values_array);
            
            if (is_object($this->$classname))
            {
                $this->debug(5, $classname.' exists, id '.$this->$classname->id);
                $this->$classname->populate_from($values_array[$classname]);
                $this->$classname->store();
            }
            else
            {
                $this_class= classname_only(static::classname());
                $lookup_field= $this_class.PK_SEPERATOR.PRIMARY_KEY;
                
                $object= new $classname();
                $object->fields[PRIMARY_KEY]= null;
                $object->$lookup_field= $this->fields[PRIMARY_KEY];
                $object->populate_from($values_array[$classname]);
                $object->store();
                
                $this->debug(5, $classname.' does not exist; adding id '.$this->$classname->id);
            }
        }    // end has_one
        if ($this->is_has_many($classname))
        {
            $this->debug(5, 'Saving '.$classname.' (has_many) with:');
            $this->debug(5, $values_array);
            
            if (is_object($this->$classname))
            {
                $this->debug(5, 'Currently '.count($this->$classname->find()).' exist; repopulating with new values...');
                $this->$classname->delete();
                foreach($values_array[$classname] as $property=>$index)
                {
                    $number_of_records= count($values_array[$classname][$property]);
                    break;
                }
                for($i=0; $i<$number_of_records; $i++)
                {
                    $this->$classname->add_array($values_array[$classname], $i);
                }
            }
        } // end has_many
    } // end save()
    
    /**
     * Auto populate the fields from an array
     */
    function populate_from($arr, $index=0)
    {
        if (is_array($arr))
        {
            foreach($arr as $key=>$value)
            {   
                if (array_key_exists($key, $this->fields) and $this->is_foreign($key) === false)
                {
                    $this->fields[$key]= $value[$index];
                }
            }
        }
    }
    
    /**
     * Find and return an object. Anything after "select * from Foo where" can be in your query.
     */
    static function find_object($query)
    {
        $classname= classname_only(static::classname());
        $results= $classname::find($query);
        if ($results)
            return array_pop($results);
            
        return false;
    }
    
    /**
     * Find and return an array of objects. Anything after "select * from Foo where" can be in your query.
     */
    static function find($query=null)
    {
        $classname= classname_only(static::classname());
        
        if ($query === null or strlen($query) < 1)
        {
            $query= "select ".PRIMARY_KEY." from ".$classname;  
            $result= DB::query($query);
        }
        else
        {
            $query= "select ".PRIMARY_KEY." from ".$classname." where ".$query;
            self::debug(5, $query);
            $result= DB::query($query);
        }
        
        $results= array();
        
        if (!$result) // or DB::num_rows($result) < 1)
        { 
            return false;
        }

        // to instance, we need full path:
        $c= static::classname();
        while($ids= DB::fetch_assoc($result))
        {
            foreach($ids as $k=>$v)
            {
                $results[]= new $c($v);
            }
        }
        return $results;
    }
    
    /** 
     * Store this object back into the database.
     */
    function store($additional=null)
    {
        $existing= ($this->fields[PRIMARY_KEY] != null);
        
        if (array_key_exists("created_on", $this->fields) and !$this->fields["created_on"])
            $this->fields["created_on"]= date("Y-m-d H:i:s", time());
        
        if (array_key_exists("updated_on", $this->fields))
            $this->fields["updated_on"]= date("Y-m-d H:i:s", time());
        
        $field_query= array();
        // set it up:
                
        
        if ($existing)
        {
            $sql= "update ".classname_only(static::classname())." set ";
            
            $field_query= array();
            foreach($this->fields as $k=>$v)
            {
                if ($v === null or $v == '') $v= "NULL";
             
                if ($k == PRIMARY_KEY) continue;
                
                if ($this->is_numeric($k) or $v === "NULL" )
                    $field_query[]= $k."=".$v."";
                elseif ($this->is_belongs_to($k) and is_object($v))
                {
                    $field_query[]= $k."=".$v->get()->id; // remember: it's a joiner object
                }
                elseif ($this->is_foreign($k))
                {
                    continue;
                }
                else
                    $field_query[]= $k."='".$v."'";                    
            }
            $sql.= implode(",", $field_query)." where ".PRIMARY_KEY."='".$this->fields[PRIMARY_KEY]."'";
        }
        else
        {
            unset($this->fields[PRIMARY_KEY]);
            $field_query= array();
            foreach($this->fields as $k=>$v)
            {
                if ($v === null or $v == '') $v= "NULL";
                if ($this->is_numeric($k) or $v === "NULL" )
                    $field_query[$k]= $v;
                elseif ($this->is_belongs_to($k) and is_object($v))
                {
                    $field_query[$k]= $v->id;
                }
                elseif ($this->is_foreign($k))
                {
                    continue;
                }
                else
                    $field_query[$k]= '"'.$v.'"';
            }
            
            $sql= 'insert into '.classname_only(static::classname()).'('.PRIMARY_KEY.', '.implode(',', array_keys($field_query)).') values(null, '.implode(',',array_values($field_query)).')';
        }
        
        $this->debug(5, $sql);
        
        // TODO update children
        DB::query($sql);

        if (!$existing)
        {
            $this->fields[PRIMARY_KEY]= DB::insert_id();
            $this->debug(5, "Set pk to ".$this->fields[PRIMARY_KEY]);
        }
        
        // think of $additional as a trigger.
        // this allows you to use a primary key that is not named like the others;
        //  to use it, override store() like:
        //      function store() { parent::store(array(to=>from)); }
        // this is not recommended for long term use due to indexing and other possible problems,
        //  but can be used to migrate from an old table system.
        if ($additional)
        {
            foreach($additional as $to=>$from)
            {
                $sql= "update ".classname_only(static::classname())." set ".$to."=".$from." where ".$from."=".$this->fields[PRIMARY_KEY];
                $this->debug(5, "Performing additional trigger: ".$sql);
                DB::query($sql);
            }
        }
    } // end store
    
    function delete()
    {
        $sql= 'delete from '.classname_only(static::classname()).' where '.PRIMARY_KEY.'="'.$this->fields[PRIMARY_KEY].'"';
        $this->debug(5, "Deleting- ".$sql);
        DB::query($sql);
    } // end delete
    
    function json_validates()
    {
        return json_encode($this->validates);
    } // end json_validates
    
    static function debug($level, $msg)
    {
        if ($level <= DEBUG_LEVEL)
        {
            echo "<pre>";
            echo self::classname()."($level): ";
            print_r($msg);
            echo "</pre>";
        }
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