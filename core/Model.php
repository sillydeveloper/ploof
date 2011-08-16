<?
// Copyright (c) 2010, ploof development team
// All rights reserved.
// 
// Redistribution and use in source and binary forms, with or without modification, are permitted provided 
// that the following conditions are met:
// 
// Redistributions of source code must retain the above copyright notice, this list of conditions and the 
// following disclaimer. 
// Redistributions in binary form must reproduce the above copyright notice, this list of 
// conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
// The names of its contributors may not be used to endorse or promote products derived from this software without 
// specific prior written permission.
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, 
// INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
// ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, 
// INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE 
// GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
// LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY 
// OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

namespace core;

class Model extends Ploof
{
    static protected $repository;
    
    // storage area for database field values 
    protected $fields= null;
    
    // storage area for database field types (datetime, int, etc)
    static protected $field_types= null;   
    
    static protected $has_many= null;
    static protected $has_one= null;
    static protected $belongs_to= null;
    static protected $has_and_belongs_to_many= null; 
    
    // TODO: an array of sort functions; ex:
    //  $sort_by= array('name'=>function($a, $b) 
    //      { if ($a->name == $b->name) return 0; return ($a->name < $b->name) ? -1 : 1;) });
    protected $sort_by= null;
    
    // you can override relationships by naming a method
    //  get_[relatedclass]() or set
    //  $getter_override= array('[relatedclass]'=>'[method]')
    protected $getter_override= null;
    
    // array of classes that will be autocreated when asked for via find()
    static protected $requires= null;
    
    // array of properties to NOT cache. 
    //  these will be ignored by __get() and store().
    static protected $no_cache= array();
        
    function __construct($id= null, $repository= null)
    {
        if ($repository) static::set_repository($repository);
        
        if (static::$repository)
        {
            if ($id)
            {
                $this->load($id);
                $this->set_field_types();
            }
            elseif (count($this->fields) < 1)
            {
                $this->set_field_types();
            }   
        }
    }
        
    public static function set_repository($repository)
    {
        static::$repository= $repository;
    }
    
    static function get_repository()
    {
        return static::$repository;
    }    
    
    static function get_requires()
    {
        return static::$requires;
    }
    
    function load($id)
    {
        if ($id > 0 and static::$repository)
        {
            $data= static::$repository->load_row(static::cname(), $id);
            if (!is_array($data)) throw new \Exception('Couldn\'t load row!');
            foreach($data as $key=>$value)
            {   
                $this->fields[$key] = stripslashes($value);
            }
        }
    }
    
    static function requires_a($classname)
    {
        if (!static::$requires) return false;
        return (array_search($classname, static::$requires) !== false);
    }
    
    static function set_field_types()
    {
        if (static::$repository)
        {
            $columns= static::$repository->get_table_columns(Meta::classname_only(static::classname()));
            foreach($columns as $col_name=>$col_type)
            {
                static::$field_types[$col_name]= preg_replace("/\(([0-9])*\)/", "", $col_type);
            }
        }
    }
    
    public function get_fields()
    {
        return $this->fields;
    }
    
    static public function has_field($var)
    {
        return array_key_exists($var, static::$field_types);
    }
    
    /**
     * Check and see if a field is a numeric type; dates are not numeric.
     */
    static public function is_numeric($field)
    {
        $type = static::get_field_type($field);
        return static::$repository->is_numeric($type);
    }
    
    static public function get_field_types()
    {
        return static::$field_types;
    }
    
    static public function get_field_type($field)
    {
        return static::$field_types[$field];
    }
    
    /**
     *  Whether or not a field has _any_ foreign key characteristics (has_many, has_one, habtm) 
     */
    static function is_foreign($field_name)
    {
        return (static::is_has_many($field_name) || static::is_has_one($field_name) 
            || static::is_habtm($field_name) || static::is_belongs_to($field_name));
    }
    
    /**
     * Whether or not a field is a belongs_to relationship
     */
    static function is_belongs_to($field_name)
    {
        if (count(static::$belongs_to) > 0 and array_search($field_name, static::$belongs_to) !== false)
            return true;
        return false;
    }
    
    /**
     *  Whether or not a field is a has_many relationship
     */
    static function is_has_many($field_name)
    {
        if (count(static::$has_many) > 0 and array_search($field_name, static::$has_many) !== false)
            return true;
        return false;
    }
    
    /**
     * Whether or not a field is a has_one relationship
     */
    static function is_has_one($field_name)
    {
        if (count(static::$has_one) > 0 and array_search($field_name, static::$has_one) !== false)
            return true;
        return false;
    }
    
    /**
     * Whether or not a field is a habtm relationship
     */
    static function is_habtm($field_name)
    {
        if (count(static::$has_and_belongs_to_many) > 0 and array_search($field_name, static::$has_and_belongs_to_many) !== false)
            return true;
        return false;        
    }
    
    /**
     * Generate the join table name for field_name
     * TODO: Change this method name to get_habtm_join_table
     */
    static function get_join_table($field_name)
    {
        $my_class = classname_only(static::classname());
        return ($field_name > $my_class) ? $my_class.PLOOF_SEPARATOR.$field_name : $field_name.PLOOF_SEPARATOR.$my_class;
    }
    
    /**
     * If a field_name is foreign, then refresh the data it points to.
     * Used to make sure cached data is up-to-date.
     * NOTICE: This method is marked for deprecation.
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
            $joiner= new Joiner($this, static::cname(), $field_name);
        }
        else 
        {
            if ($this->is_belongs_to($field_name))
            {
                $lookup_id= $this->fields[$field_name.PK_SEPARATOR.PRIMARY_KEY];
                $lookup_field= PRIMARY_KEY;
            
                // this needs to be a raw db load for this to 'actually' work:
                $result= $field_name::find_object(array($lookup_field=>$lookup_id));
                
                // now update the cache:
                $field_name::$repository->replace_in_cache($result->ckey(), $result);
            }
        
            if ($this->is_has_many($field_name))
            {
                $lookup_id= $this->fields[PRIMARY_KEY];
                $lookup_field= static::cname().PK_SEPARATOR.PRIMARY_KEY;
                
                $results= $field_name::find(array($lookup_field=>$lookup_id));
                
                foreach($results as $obj)
                {
                    $field_name::$repository->replace_in_cache($obj->ckey(), $obj);
                }
                
                /* 
                if ($sort_fun)
                    usort($results, $sort_fun);
                
                $joiner= new Joiner();
                $joiner->set_objects($results);
                $joiner->set_parent($this, classname_only(static::classname()));
                $joiner->set_child_class($field_name);
                
                $this->fields[$field_name]= $joiner;
                */
            }
            
            if ($this->is_has_one($field_name))
            {
                $lookup_id= $this->fields[PRIMARY_KEY];
                $lookup_field= static::cname().PK_SEPARATOR.PRIMARY_KEY;
                
                $result= $field_name::find_object(array($lookup_field=>$lookup_id));

                 // now update the cache:
                $field_name::$repository->replace_in_cache($result->ckey(), $result);
            }
        } // end habtm check
    } // end refresh
    
    /**
     * Generic __get. All $obj->property calls come through here.
     */
    function __get($field_name)
    {
        // if no_cache, then get from the database directly:
        if (array_search($field_name, static::$no_cache) !== false)
        {
            // access database directly, bypass any cache access:
            $fields= static::$repository->get_database()->load(static::cname(), $this->id);
            return $fields[$field_name];
        }
        
        $results= null;
        
        if ($this->is_foreign($field_name))
        {
            $this->debug(5, "Found foreign: ".$field_name);
            
            if (method_exists($this, "get_".$field_name))
            {
                $method= "get_".$field_name;
                $this->debug(5, "Calling override ".$method." for __get(".$field_name.")");
                $results= $this->$method();
            }
            else
            {
                if ($this->is_belongs_to($field_name))
                {
                    $lookup_id= $this->fields[$field_name.PK_SEPARATOR.PRIMARY_KEY];
                    $lookup_field= PRIMARY_KEY;

                    $results= $field_name::find_object(array($lookup_field=>$lookup_id));
                
                }

                if ($this->is_has_many($field_name))
                {
                    $results= new HasManyWrapper($this, $field_name);
                }

                if ($this->is_has_one($field_name))
                {
                    $lookup_id= $this->fields[PRIMARY_KEY];
                    $lookup_field= static::cname().PK_SEPARATOR.PRIMARY_KEY;

                    $results= $field_name::find_object(array($lookup_field=>$lookup_id));
                }
            }
        }
        
        if ($this->requires_a($field_name) and !$results)
        {
            $obj= new $field_name();
            $obj->$lookup_field= $lookup_id;
            $obj->store();
            if ($this->is_has_many($field_name))
                $results[]= $obj;
            else
                $results= $obj;
        }
        
        if ($results)
            return $results;
        else
        {
            return stripslashes($this->fields[$field_name]); // remove sanitized escape
        }
    }
    
    /**
     * Generic __set. All $obj->property= $val calls come through here.
     *  Note that Controller handles the weatherstripping of user input.
     */
    function __set($field_name, $value)
    {
        // if no_cache, then get from the database directly:
        if (array_search($field_name, static::$no_cache) !== false)
        {
            $this->fields[$field_name]= $value;
            static::$repository->get_database()->store_row(static::cname(), $this->fields);
            return; 
        }
        
        if ($this->is_foreign($field_name))
        {
            if ($this->is_belongs_to($field_name))
            {
                $lookup_id= $this->fields[$field_name.PK_SEPARATOR.PRIMARY_KEY];
                $lookup_field= PRIMARY_KEY;

                $results= $field_name::find_object(array($lookup_field=>$lookup_id));
            }

            if ($this->is_has_many($field_name))
            {
                $lookup_id= $this->fields[PRIMARY_KEY];
                $lookup_field= static::cname().PK_SEPARATOR.PRIMARY_KEY;
                
                // return a dummy object?
                // TODO: andrew doesn't like this. 
                $results= $field_name::find(array($lookup_field=>$lookup_id));
            }

            if ($this->is_has_one($field_name))
            {
                $lookup_id= $this->fields[PRIMARY_KEY];
                $lookup_field= static::cname().PK_SEPARATOR.PRIMARY_KEY;

                $results= $field_name::find_object(array($lookup_field=>$lookup_id));
            }
            
            if ($this->is_has_one($field_name) or $this->is_belongs_to($field_name))
            {
                // replace into the cache
            }
            
            
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
            if (array_key_exists($field_name, static::$field_types) and static::$repository->get_database()->is_date_datatype($this->get_field_type($field_name)) and strlen($value) > 0)
            {
                $value = Format::date_sql($value);
            }
            $this->fields[$field_name]= $value;

        }
    }
    
    /**
     *  Store() many at once:
     */
    static function save($values_array, $class= null)
    {
        if ( !is_array($values_array) ) return false;
        
        if ($class == null) $class= static::classname();
        
        // check and see if the class is a subarray;
        //  if so, take it out:
        if (array_key_exists($class, $values_array)) $values_array= $values_array[$class];
        
        // we need to figure out how many things we're saving:
        $keys= array_keys($values_array);
        $cnt= count($values_array[$keys[0]]);
        
        for($i=0; $i<$cnt; $i++)
        {
            $obj= new $class();
            $obj->populate_from($values_array, $i);
            $obj->store();
        }
    } // end save()
    
    /**
     * Auto populate the fields from an array
     */
    function populate_from($arr, $index= 0)
    {
        if (is_array($arr))
        {
            foreach($arr as $key=>$value)
            {   
                if (array_key_exists($key, static::$field_types) and $this->is_foreign($key) === false)
                {
                    // use __set to ensure special cases are handled (like datetimes)
                    $this->__set($key, ($index === null) ? $this->sanitize($key, $value) : $this->sanitize($key, $value[$index]));
                    $this->debug(5, "Populating $key as " . $this->fields[$key]);
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
        $res = static::$repository->get_database()->query($sql);
        if (!$res) 
        { 
            self::debug(5, 'No result');
            return array();
        }

        $classname= static::classname();
        $objects = array();
        foreach($res as $row)
        {
            $objects[] = new $classname($row[PRIMARY_KEY]);
        }
        return $objects;
    }
    
    /**
     * Find and return an object. Anything after "select * from Foo where" can be in your query.
     */
    static function find_object($query)
    {
        $classname= static::cname();
        $results= $classname::find($query);

        if ($results)
            return array_pop($results);
            
        return false;
    }
    
    /** 
     * Map to Repository::query
     */
    static function query($query)
    {
        $classname= static::cname();
        return $classname::get_repository()->query($query);
    }
    
    /**
     * Find and return an array of objects.
     */
    static function find($query=null)
    {
        $returns= array();
        $classname= static::classname();   
        $results= static::$repository->find_rows(static::cname(), $query);
        if (is_array($results))
        {
            foreach($results as $key=>$object_data)
            {
                $returns[]= new $classname($object_data[PRIMARY_KEY]);
            }
        }
        return $returns;
    }
    
    /** 
     * Store this object back into the database.
     */
    function store($additional=null)
    {
        $id= static::$repository->store_row(static::cname(), $this->fields);
        $this->id= $id;
        
        // think of $additional as a trigger.
        // this allows you to use a primary key that is not named like the others;
        //  to use it, override store() like:
        //      function store() { parent::store(array(to=>from)); }
        // this is not recommended for long term use due to indexing and other possible problems,
        //  but can be used to migrate from an old table system.
        if ($additional)
        {
            $sql= "update ".$table." set ";
            $field_array= array();
            foreach($additional as $from=>$to)
            {
                $field_array[]= $to."=".$from;
            }
            $sql.= implode(", ", $field_array)." where ".PRIMARY_KEY."=".$this->fields[PRIMARY_KEY];
            static::$repository->query($sql);
        }
    } // end store
    
    function delete()
    {
        static::$repository->delete_row(static::cname(), $this->id);
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
        $qry= \ApplicationModel::get_repository()->get_database()->query("show tables");
        $classes= array();
        while($table= \mysqli_fetch_array($qry)) // TODO: fix no bueno mysql specific here!
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
                $qry2= \ApplicationModel::get_repository()->get_database()->query("show columns from ".$table);
                
                while($column= \mysqli_fetch_array($qry2)) // TODO: fix no bueno mysql specific here!
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
            $file = "model/".$class.".php";
            if (file_exists($file) == false)
            {
                $f= fopen($file, "w+");
                fwrite($f,"<?\n");
                fwrite($f,"class $class extends ApplicationModel\n");
                fwrite($f,"{\n");
                foreach ($relations as $k=>$r)
                    fwrite($f,"    static \$$k= array('".implode("', '", $r)."');\n");
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
     
     /**
      * Get the cache key for this object:
      */
     function ckey()
     {
         $pk= PRIMARY_KEY;
         return static::cname().'_'.$this->$pk;
     }
     
     /** 
      * Shortcut for Meta::classname_only(static::classname())
      */
     static function cname()
     {
         return Meta::classname_only(static::classname());
     }
     
     static function sanitize($key, $value)
     {
         return $value;
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
