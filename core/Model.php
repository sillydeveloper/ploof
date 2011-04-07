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
        if ($id and static::$repository)
        {
            $data= static::$repository->load_row(Meta::classname_only(static::classname()), $id);
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
        return array_key_exists($var, static::$fields_types);
    }
    
    /**
     * Check and see if a field is a numeric type; dates are not numeric.
     */
    static public function is_numeric($field)
    {
        $type = static::$get_field_type($field);
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
                $objects= $field_name::find(array($lookup_field=>$lookup_id));
                $results= new HasManyWrapper($objects);
            }

            if ($this->is_has_one($field_name))
            {
                $lookup_id= $this->fields[PRIMARY_KEY];
                $lookup_field= static::cname().PK_SEPARATOR.PRIMARY_KEY;

                $results= $field_name::find_object(array($lookup_field=>$lookup_id));
            }
        }

        // call the datetime handler if this is a datetime:
        if (array_key_exists($field_name, static::$field_types))
        {
            if (static::$repository->get_database()->is_date_datatype(static::$field_types[$field_name]))
            {
                return Format::date($this->fields[$field_name]);
            }
        }            
            
        if ($results)
            return $results;
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
            if (array_key_exists($field_name, static::$field_types) and static::$repository->get_database()->is_date_datatype($this->field_types[$field_name]) and strlen($value) > 0)
            {
                $value = Format::date_sql($value);
            }
            
            $this->fields[$field_name]= $value;
        }
    }
    
    function add_object($class, $object)
    {
        
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
        $res = static::$repository->get_database()->query($sql);
        if (!$res) 
        { 
            self::debug(5, 'No result');
            return array();
        }

        $classname= static::cname();
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
     * Find and return an array of objects.
     */
    static function find($query=null)
    {
        $returns= array();
        $classname= static::cname();   
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
        $this->fields[PRIMARY_KEY]= $id;
        
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
    
    /**
     * Get this objects static classname (PHP5.3 only)
     */
    static function classname()
    {
        return __CLASS__;
    }
}

?>
