<?
namespace core;

class Model
{
    protected $pk= PRIMARY_KEY;
    protected $fields= null;
    protected $field_types= null;
    protected $has_many= null;
    protected $has_one= null;
    protected $has_and_belongs_to_many= null; 
    
    protected $db_connector_preamble= "";
    
    function __construct($id=null)
    {
        if ($id)
        {
            $sql= "select * from ".classname_only(static::classname())." where ".$this->pk."=".$id;
            $qry= DB::query($sql);
            while($row= \mysqli_fetch_assoc($qry))
            {   
                foreach($row as $k=>$v)
                {
                    $this->fields[$k] = $v;
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
        while($row= \mysqli_fetch_assoc($qry))
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
     */
    function get_join_table($field_name)
    {
        $my_class = classname_only(static::classname());
        return ($field_name > $my_class) ? $my_class.PLOOF_SEPARATOR.$field_name : $field_name.PLOOF_SEPARATOR.$my_class;
    }
    
    /**
     * If a field_name is foreign, then refresh the data it points to.
     * Used to make sure cached data is up-to-date d.
     */
    function refresh($field_name)
    {   
        if ($this->is_belongs_to($field_name))
        {
            $lookup_id= $this->fields[$field_name."_".PRIMARY_KEY];
            $lookup_field= PRIMARY_KEY;
            
            $results= $field_name::find($lookup_field."='".$lookup_id."'");
        }
        
        if ($this->is_has_many($field_name))
        {
            $lookup_id= $this->fields[PRIMARY_KEY];
            $lookup_field= classname_only(static::classname())."_".PRIMARY_KEY;
            
            $results= $field_name::find($lookup_field."='".$lookup_id."'");
        }
        
        if ($this->is_has_one($field_name))
        {
            $lookup_id= $this->fields[PRIMARY_KEY];
            $lookup_field= classname_only(static::classname())."_".PRIMARY_KEY;
            
            $results= $field_name::find($lookup_field."='".$lookup_id."' limit 1");
        }
        
        if ($this->is_habtm($field_name))
        {   
            // joiner handles everything for habtm since it has to load
            //  and know about extra fields in the join table.
            $joiner= new Joiner($this, classname_only(static::classname()), $field_name);            
        }
        else
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
                $this->refresh($field_name);
        }
        if ($this->is_belongs_to($field_name))
        {
            // return the joiner's get, because there is only one:
            return $this->fields[$field_name]->get();
        }
        return $this->fields[$field_name];
    }
    
    /**
     * Generic __set. All $obj->property= $val calls come through here.
     */
    function __set($field_name, $value)
    {
        $this->fields[$field_name]= $value;
    }
    
    /**
     * Auto populate the fields from an array
     */
    function populate_from($arr)
    {
        foreach($arr as $key=>$value)
        {
            if (array_key_exists($key, $this->fields) and $this->is_foreign($key) === false)
            {
                $this->fields[$key]= $value;
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
        if (is_array($results))
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
            DB::query($query);
        }
        else
        {
            $query= "select ".PRIMARY_KEY." from ".$classname." where ".$query;
            $result= DB::query($query);
        }
        
        $results= array();
        
        if (!$result)
            return false;

        // to instance, we need full path:
        $c= static::classname();
        while($ids= \mysqli_fetch_assoc($result))
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
    function store()
    {
        $existing= ($this->fields[PRIMARY_KEY] != null);
        
        if (array_key_exists("created_on", $this->fields) and !$this->fields["created_on"])
            $this->fields["created_on"]= date("Y-m-d H:i:s", time());
        
        if (array_key_exists("updated_on", $this->fields))
            $this->fields["updated_on"]= date("Y-m-d H:i:s", time());
                
        if ($existing)
        {
            $sql= "update ".classname_only(static::classname())." set ";
            
            $field_query= array();
            foreach($this->fields as $k=>$v)
            {
                if ($k == PRIMARY_KEY) continue;
                if ($this->is_foreign($k)) continue;
                $field_query[]= $k."='".$v."'";
            }
            $sql.= implode(",", $field_query)."where ".PRIMARY_KEY."='".$this->fields[PRIMARY_KEY]."'";
        }
        else
        {
            unset($this->fields[PRIMARY_KEY]);
            $sql= "insert into ".classname_only(static::classname())."(".PRIMARY_KEY.", ".implode(",", array_keys($this->fields)).") values(null, '".implode("','",array_values($this->fields))."')";
        }
        // TODO update children
        DB::query($sql);

        if (!$existing)
        {
            $this->fields[PRIMARY_KEY]= mysql_insert_id();
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