<?
namespace core;

class Model extends Ploof
{
    static protected $db;
    
    // storage area for database field values 
    protected $fields= null;
    
    // storage area for database field types (datetime, int, etc)
    protected $field_types= null;   
    
    protected $has_many= null;
    protected $has_one= null;
    protected $belongs_to= null;
    protected $has_and_belongs_to_many= null; 
    
    // TODO: an array of sort functions; ex:
    //  $sort_by= array('name'=>function($a, $b) 
    //      { if ($a->name == $b->name) return 0; return ($a->name < $b->name) ? -1 : 1;) });
    protected $sort_by= null;
    
    // you can override relationships by naming a method
    //  get_[relatedclass]() or set
    //  $getter_override= array('[relatedclass]'=>'[method]')
    protected $getter_override= null;
    
    // array of classes that will be autocreated when asked for via find()
    protected $requires= null;
    
    // array of properties to NOT cache. 
    //  these will be ignored by __get() and store().
    protected $no_cache= array();
        
    function __construct($id= null, $db= null)
    {
        if ($db) static::set_db($db);
        
        if (static::$db)
        {
            if ($id)
            {
                $this->load($id);
                $this->set_field_types();
            }
            elseif (count($this->fields) < 1)
            {
                $this->set_field_types(true);
            }   
        }
    }
        
    public static function set_db($db)
    {
        static::$db= $db;
    }
    public static function get_db()
    {
        return static::$db;
    }    
    
    public function load($id)
    {
        if ($id and static::$db)
        {
            $data= static::$db->load(Meta::classname_only(static::classname()), $id);
            foreach($data as $key=>$value)
            {   
                $this->fields[$key] = stripslashes($value);
            }
        }
    }
    
    public function requires_a($classname)
    {
        if (!$this->requires) return false;
        return (array_search($classname, $this->requires) !== false);
    }
    
    function set_field_types($nullify= false)
    {
        if (static::$db)
        {
            $columns= static::$db->get_columns(Meta::classname_only(static::classname()));
            foreach($columns as $col_name=>$col_type)
            {
                if ($nullify)
                    $this->fields[$col_name]= null;
                
                $this->field_types[$col_name]= preg_replace("/\(([0-9])*\)/", "", $col_type);
            }
        }
    }
    
    public function get_fields()
    {
        return $this->fields;
    }
    
    public function has_field($var)
    {
        return array_key_exists($var, $this->fields);
    }
    
    /**
     * Check and see if a field is a numeric type; dates are not numeric.
     */
    public function is_numeric($field)
    {
        $type = $this->get_field_type($field);
        return static::$db->is_numeric($type);
        
        //$numeric= array("decimal", "tinyint", "bigint", "int", "float", "double");
        //if (array_search($type, $numeric) !== false) return true;
        //if (strpos($type, "decimal") !== false) return true;  // e.g. "decimal(5,2)"
        //return false;
    }
    
    public function get_field_types()
    {
        return $this->field_types;
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
    function refresh($field_name, $sort_fun=null, $order=null, $limit=null)
    {   
        $joiner= false;
        
        // Check to see if we need to override the getter by calling a different
        //  method:
        if (($this->getter_override and array_key_exists($field_name, $this->getter_override)) or method_exists($this, "get_".$field_name))
        {
            $method= ($this->getter_override and array_key_exists($field_name, $this->getter_override)) ?  $this->getter_override[$field_name] : "get_".$field_name;
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
                $lookup_id= $this->fields[$field_name.PK_SEPARATOR.PRIMARY_KEY];
                $lookup_field= PRIMARY_KEY;
            
                $results= $field_name::find($lookup_field."='".$lookup_id."'");
            }
        
            if ($this->is_has_many($field_name))
            {
                $lookup_id= $this->fields[PRIMARY_KEY];
                $lookup_field= classname_only(static::classname()).PK_SEPARATOR.PRIMARY_KEY;
                
                $sql= $lookup_field."='".$lookup_id."' ".$order." ".$limit;
                
                $this->debug(5, $field_name." sql=".$sql);
                
                $results= $field_name::find($sql);
            }
            
            if ($this->is_has_one($field_name))
            {
                $lookup_id= $this->fields[PRIMARY_KEY];
                $lookup_field= classname_only(static::classname()).PK_SEPARATOR.PRIMARY_KEY;
                
                $results= $field_name::find($lookup_field."='".$lookup_id."' order by id desc limit 1");
            }
        }
        
        if ($sort_fun)
            usort($results, $sort_fun);
        
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
        // if no_cache, then get from the database directly:
        if (array_search($field_name, $this->no_cache) !== false)
        {
            // access database directly:
            $fields= static::$db->get_database()->load(static::cname(), $this->id);
            
            //DB::fetch_array(DB::query('select '.$field_name.' from '.classname_only(static::classname()).' where id='.$this->id));
            return $fields[$field_name];
        }
        
        // lazy loading part.
        if ($this->is_foreign($field_name))
        {
            $this->debug(5, "Found foreign: ".$field_name);
            if (array_key_exists($field_name, $this->fields) == false or is_object($this->fields[$field_name]) == false or ENABLE_OBJECT_CACHE == 0)
            {
                $this->debug(5, "Refreshing ".$field_name. " (ENABLE_OBJET_CACHE=".ENABLE_OBJECT_CACHE.")");
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

        // call the datetime handler if this is a datetime:
        if (array_key_exists($field_name, $this->field_types))
        {
            switch (strtolower($this->field_types[$field_name]))
            {
                case "date": // fall through
                case "datetime": return format_date($this->fields[$field_name]);
                case "float": // fall through
                case "double": return format_float($this->fields[$field_name]);
            }
        }            
            
        if (is_object($this->fields[$field_name]))
            return $this->fields[$field_name];
        else
            return stripslashes($this->fields[$field_name]); // remove sanitized escape
    }
    
    /**
     * Generic __set. All $obj->property= $val calls come through here.
     *  Note that Controller handles the weatherstripping of user input.
     */
    function __set($field_name, $value)
    {
        // if no_cache, then get from the database directly:
        if (array_search($field_name, $this->no_cache) !== false)
        {
            //$sql= 'update '.classname_only(static::classname()).' set '.$field_name.'="'.$value.'" where id='.$this->id;
            //DB::query($sql);
            $this->fields[$field_name]= $value;
            static::$db->get_database()->store(static::cname(), $this->fields);
            return; 
        }
        
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
        {
            // call the datetime handler if this is a datetime:
            if (array_key_exists($field_name, $this->field_types) and $this->field_types[$field_name] == "datetime" and strlen($value) > 0)
            {
                $value = format_date_sql($value);
            }
            
            $this->fields[$field_name]= $value;
        }
    }
    
    /**
     *  Find and overwrite, or create and store, an object of $classname
     *      with $values_array that has a relationship to $this. 
     *      Use store() on an object, save() on a relationship.
     *      TODO: This should be moved into joiner, which deals with relationships...
     */
    function save($classname, $values_array)
    {
        if ( !is_array($values_array) ) return false;

        // we'll return whatever we create / add / update:
        $return= null;
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
                $return= $object;
                
                $lookup_field= $classname.PK_SEPARATOR.PRIMARY_KEY;
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
                $this->$classname->populate_from($values_array[$classname], 0);
                $this->$classname->store();
                $return= $this->$classname;
            }
            else
            {
                $this_class= classname_only(static::classname());
                $lookup_field= $this_class.PK_SEPARATOR.PRIMARY_KEY;
                
                $object= new $classname();
                $object->fields[PRIMARY_KEY]= null;
                $object->$lookup_field= $this->fields[PRIMARY_KEY];
                $object->populate_from($values_array[$classname], 0);
                $object->store();
                $return= $object;
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
                $this_class= classname_only(static::classname());
                $lookup_field= $this_class.PK_SEPARATOR.PRIMARY_KEY;
                
                if (!count($values_array))
                {
                    $this->debug(5, "No data; adding raw object");
                    // add a new one:
                    $obj= new $classname();
                    $obj->$lookup_field= $this->id;
                    $obj->store();
                    $return= $obj;
                    $this->refresh($classname);
                }
                else
                {
                    foreach($values_array[$classname] as $property=>$index)
                    {
                        $number_of_records= count($values_array[$classname][$property]);
                        break; 
                    }
                    
                    for($i=0; $i<$number_of_records; $i++)
                    {
                        if (array_key_exists(PRIMARY_KEY, $values_array[$classname]))
                        {
                            $obj= $this->$classname->find_object(array(PRIMARY_KEY=>$values_array[$classname][PRIMARY_KEY][$i]));
                            if ($obj) 
                            {
                                $this->debug(5, 'Found '.$obj->id);
                                $obj->populate_from($values_array[$classname], $i);
                                $obj->store();
                                $return[]= $obj;
                            }
                        }
                        else
                        {
                            $this->debug(5, 'Not found, creating');
                            $return[]= $this->$classname->add_array($values_array[$classname], $i);
                        }
                    }
                }
            }
        } // end has_many
        return $return;
    } // end save()
    
    /**
     * Auto populate the fields from an array
     */
    function populate_from($arr, $index= null)
    {
        if (is_array($arr))
        {
            foreach($arr as $key=>$value)
            {   
                $this->debug(5, $key);
                if (array_key_exists($key, $this->field_types) and $this->is_foreign($key) === false)
                {
                    //$this->fields[$key]= ($index === null) ? $this->sanitize($key, $value) : $this->sanitize($key, $value[$index]);
                    // use set to ensure datetimes are handled
                    $this->__set($key, ($index === null) ? $this->sanitize($key, $value) : $this->sanitize($key, $value[$index]));
                    $this->debug(5,"Populating $key as " . $this->fields[$key]);
                }
            }
        }
    }
    
    /**
    * Find and return array of objects
    * 
    * SQL must return a single field that is the primary key of the table that represent this class
    * Returns assoc array with primary key as key and object as value
    * 
    * @param $sql string SQL statement that selects primary keys
    */
    static function find_sql($sql)
    {
        self::debug(5, "find_sql using sql = ".$sql);
        $res = DB::query($sql);
        if (!$res) 
        { 
            self::debug(5, 'No result');
            return array();
        }

        $classname= classname_only(static::classname());
        $objects = array();
        while ($row = DB::fetch_array($res))
        {
            $primary_key = $row[0];
            $objects[$pk] = new $classname($primary_key);
        }
        return $objects;
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
     * Find and return an array of objects.
     */
    static function find($query=null)
    {
        
        return static::$db->find(Meta::classname_only(static::classname()), $query);
        
        /*
        $classname= classname_only(static::classname());
        
        if ($query === null or strlen($query) < 1)
        {
            $query= "select ".PRIMARY_KEY." from ".$classname;  
            self::debug(5, $query);
            if ($db)
                $result= $db::query($query);
            else
                $result= DB::query($query);
        }
        else
        {
            $query= "select ".PRIMARY_KEY." from ".$classname." where ".$query;
            self::debug(5, $query);
            if ($db)
                $result= $db::query($query);
            else
                $result= DB::query($query);
        }
        
        $results= array();
        
        if (!$result) // or DB::num_rows($result) < 1)
        { 
			self::debug(5, 'No result');
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
        */
    }
    
    /**
     * Sanitize something for mysql, if needed:
     */
    function sanitize($key, $val)
    {
        if (SANITIZE_INPUT)
        { 
            if (USE_MYSQLI)
                $val= \mysqli_real_escape_string(DB::getInstance()->connect_id, $val);
            else
                $val= \mysql_real_escape_string($val);
        }
        
        if ($this->is_numeric($key) and $val != "NULL")
        {
            $val= preg_replace("/[A-Za-z\$,_\%']/i", "", $val);
        }
        
        return $val;
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
            foreach($this->field_types as $k=>$v)
            {
                $v= $this->fields[$k];
                if ($k != PRIMARY_KEY)
                {
                    if (!is_object($v) and !is_array($v) and ($v === null or strlen($v) == 0)) 
                        $v= "NULL";
             
                    if ($k == PRIMARY_KEY) continue;
                
                    if (array_search($k, $this->no_cache) !== false)
                    {
                        // if it's in a no-cache state, then don't update it.
                    }
                    elseif ($this->is_belongs_to($k) and is_object($v))
                    {
                        // remember: it's a joiner object.
                    }
                    elseif ($this->is_numeric($k) or $v === "NULL" )
                    {
                        $field_query[]= $k."=".$this->sanitize($k, $v)."";
                    }
                    elseif ($this->is_foreign($k))
                    {
                        // if it's otherwise foreign, ignore it.
                    }
                    else
                        $field_query[]= $k."='".$this->sanitize($k, $v)."'";         
                }           
            }
            $sql.= implode(",", $field_query)." where ".PRIMARY_KEY."='".$this->fields[PRIMARY_KEY]."'";
        }
        else
        {   
            unset($this->fields[PRIMARY_KEY]);
            $field_query= array();				
            foreach($this->field_types as $k=>$v)
            {
                if ($k != PRIMARY_KEY)
                {
                    $v= $this->fields[$k];
                    
                    if ($v === null or strlen($v) == 0) { $v= "NULL";  }
                    
                    if (array_search($k, $this->no_cache) !== false)
                    {
                        // if it's in a no-cache state, then don't update it.
                        continue;   
                    }
                    elseif ($this->is_numeric($k) or $v === "NULL" )
                        $field_query[$k]= $this->sanitize($k, $v);
                    elseif ($this->is_belongs_to($k) and is_object($v))
                    {
                        $field_query[$k]= $this->sanitize($k, $v->id);
                    }
                    elseif ($this->is_foreign($k))
                    {
                        continue;
                    }
                    else
                        $field_query[$k]= '"'.$this->sanitize($k, $v).'"';
                }
            }
            
            $sql= 'insert into '.classname_only(static::classname()).'('.PRIMARY_KEY.', '.implode(',', array_keys($field_query)).') values(null, '.implode(',',array_values($field_query)).');';
        }
        
        $this->debug(5, $sql);
        
        // TODO update children
        DB::query($sql);

        if (!$existing)
        {
			$id= DB::insert_id();
			
            $this->fields[PRIMARY_KEY]= $id;
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
            $sql= "update ".classname_only(static::classname())." set ";
            $field_array= array();
            foreach($additional as $from=>$to)
            {
                $field_array[]= $to."=".$from;
            }
            $sql.= implode(", ", $field_array)." where ".PRIMARY_KEY."=".$this->fields[PRIMARY_KEY];
            $this->debug(5, "Performing additional trigger: ".$sql);
            DB::query($sql);
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
        
    /**
     * Create class files based on database structure.
     *  This will not create files if they already exist.
     */
    static function generate_models()
    {
        $qry= DB::query("show tables");
        $classes= array();
        while($table= DB::fetch_array($qry))
        {   
            $table= $table[0];
            //print("Generating ".$table."...\n");
            
            // check for habtm:
            $split= explode(PLOOF_SEPARATOR, $table);
            $habtm = true;

            if (count($split) < 2)
                $habtm= false; // no habtm
            else
            {   
                // we may have a habtm; check to make sure that the joined tables match:
                //foreach($split as $s=>$search_for_tablename)
                //{
                //    if (array_search($split[0], $search_for_tablename) === false)
                //        $habtm = false;
                //}

                //if ($habtm)
                //{
                    // link them all together:
                    foreach($split as $s=>$search_for_tablename)
                    {
                        foreach($split as $s2=>$search_for_tablename2)
                        {
                            if ($search_for_tablename != $search_for_tablename2)
                                $classes[$search_for_tablename]['habtm'][]= $search_for_tablename2;
                        }
                    }
                //}
            }

            if ($habtm == false)
            {
                $qry2= DB::query("show columns from ".$table);
                
                while($column= DB::fetch_array($qry2))
                {
                    $column= $column["Field"];
                    
                    if (preg_match('/'.PK_SEPARATOR.PRIMARY_KEY.'$/', $column))
                    {
                        $foreign_table= str_replace( PK_SEPARATOR.PRIMARY_KEY,"",$column);
                        $classes[$table]['belongs_to'][]= $foreign_table;
                        $classes[$foreign_table]['has_many'][]= $table;
                    }
                } // end foreach show columns
            } // end if !habtm

        } // end foreach table
        
        foreach($classes as $class=>$relations)
        {
            if (IN_UNIT_TESTING)
                $file = "test/temp/".$class.".php";
            else
                $file = "model/".$class.".php";
            if (file_exists($file) == false)
            {
                $f= fopen($file, "w+");
                fwrite($f,"<?\n");
                fwrite($f,"class $class extends \\core\\Model\n");
                fwrite($f,"{\n");
                foreach ($relations as $k=>$r)
                    fwrite($f,"    protected \$$k= array('".implode("', '", $r)."');\n");
                fwrite($f,"    static function classname()\n");
                fwrite($f,"    {\n");
                fwrite($f,"        return __CLASS__;\n");
                fwrite($f,"    }\n");
                fwrite($f,"}\n");
                fwrite($f,"?>");
                fclose($f);
            }
        }
    } // end generate_models
    
    /**
     * Try and build a JSON encoded version of this object.
     */
    function to_json()
    {
        $cols = $this->get_fields();
        $yarr = array();
        foreach($cols as $k=>$v)
        {
            $yarr[$k]= $v;
        }
        
        $yarr['classname']= classname_only(static::classname());

        if (function_exists("json_encode"))
            return json_encode($yarr);
        else
            throw new Exception("Native JSON doesn't exist!");
    }
    
    /**
     *  Get the raw value of a field without any help from ploof.
     */
    function raw($field)
    {
        return $this->fields[$field];
    }
    
    /**
     * Basic clone functionality -- basically clear the PK
     */
     function __clone()
     {
         $this->fields[PRIMARY_KEY]= null;
     }
     
     function cname()
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
