<?
namespace core;

class HasManyWrapper extends Model
{
    protected $objects;
    protected $class;
    
    function set_objects($objects)
    {
        $this->objects= $objects;
        
        // note we're going to assume that all objects are the same class type:
        $this->class= $objects[0]::cname();
    }
    
    function __construct($objects= null)
    {
        if ($objects) $this->set_objects($objects);
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
        
        $this->debug(1, $this->fields);
        
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