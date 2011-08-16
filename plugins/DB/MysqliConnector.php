<?
namespace plugins\DB;

class MysqliConnector implements \core\PluginInterfaceDB
{
    protected $conn= null;
    protected $data_types= array();
    
    /**
     *  Connects and selects database.
     *
     *  @access public
     *  @return void
     */
    public function __construct($username, $password, $database, $host, $port=null)
    {
        $this->conn = \mysqli_connect($host, $username, $password, $database) or die("\nCould not connect to mysqli (" . \mysqli_connect_errno() . ": " . \mysqli_connect_error() .")\n");
    }
    
    // load a single row in table identified by id
    function load_row($table, $id)
    {
        $results= $this->find_rows($table, array('id'=>$id));
        return $results[0];
    }
    
    // find everything in table matching where_array
    function find_rows($table, $where_array=null)
    {
        $sql= "select * from ".$table." ";
        $sql_arr= array();
        
        if ($where_array and !is_array($where_array))
        {
            throw new Exception('Invalid argument type to MysqliConnector::find_rows()');
        }
        
        if (is_array($where_array))
        {
            $sql.= " where "; 
            foreach($where_array as $key=>$value)
            {
                $sql_arr[]= $key."='".$value."'";
            }
            $sql.=implode(' and ', $sql_arr);
        }
        $result= $this->query($sql);
        $return= array();
        if ($result)
        {
            while($assoc= \mysqli_fetch_assoc($result))
            {
                $return[]= $assoc;
            }
        }
        return $return;
    }
    
    // get a list of tables
    function show_tables()
    {
        
    }
    
    // get a list of columns, with type information, for table
    function get_table_columns($table)
    {
        if (array_key_exists($table, $this->data_types))
        {
            return $this->data_types[$table];
        }

        $result= \mysqli_query($this->conn, "show columns from ".$table);
        
        $columns= array();
        if ($result)
        {
            while($col= \mysqli_fetch_assoc($result))
            {
                $columns[$col['Field']]= $col['Type'];
            }
        }
        $this->data_types[$table]= $columns;
        
        return $columns;
    }
    
    // is field_type considered numeric for this database?
    function is_numeric_datatype($field_type)
    {
        
    }
            
    // not sure if this is really required...?
    function query($sql)
    {
        return \mysqli_query($this->conn, $sql);   
    }
    
    function is_date_datatype($field_type)
    {
        return (strtolower($field_type) == 'datetime');
    }
    
    function delete_row($table, $id)
    {
        $sql= 'delete from '.$table.' where '.PRIMARY_KEY.'="'.$id.'"';
        $this->query($sql);
    }
    
    function store_row($table, $data)
    {
        // pull out the column type (which should be cached);
        //  we'll need column types to determine how to build our sql statement.
        $col_types= $this->get_table_columns($table);
        
        $existing= ($data[PRIMARY_KEY]) ? true : false;
        
        if (array_key_exists("created_on", $data) and !$data["created_on"])
            $data["created_on"]= date("Y-m-d H:i:s", time());
        
        if (array_key_exists("updated_on", $data))
            $data["updated_on"]= date("Y-m-d H:i:s", time());
        
        $field_query= array();

        // set it up:
        if ($existing)
        {
            $sql= "update ".$table." set ";
            
            $field_query= array();
            foreach($data as $k=>$v)
            {
                $v= $data[$k];
                if ($k != PRIMARY_KEY)
                {
                    if (!is_object($v) and !is_array($v) and ($v === null or strlen($v) == 0)) 
                        $v= "NULL";
             
                    if ($k == PRIMARY_KEY) continue;
                
                    if ($this->is_numeric_datatype($col_types[$k]) or $v === "NULL" )
                        $field_query[]= $k."=".\mysqli_real_escape_string($this->conn, $v);
                    elseif (is_object($v))
                    {
                        // do nothing for objects
                    }
                    else
                    {
                        $field_query[]= $k.'="'.\mysqli_real_escape_string($this->conn, $v).'"';         
                    }
                }           
            }
            $sql.= implode(",", $field_query)." where ".PRIMARY_KEY."='".$data[PRIMARY_KEY]."'";
        }
        else
        {   
            unset($data[PRIMARY_KEY]);
            $field_query= array();				
            foreach($data as $k=>$v)
            {
                if ($k != PRIMARY_KEY)
                {
                    $v= $data[$k];
                    
                    if ($v === null or strlen($v) == 0) { $v= "NULL";  }
                    
                    if ($this->is_numeric_datatype($col_types[$k]) or $v === "NULL" )
                        $field_query[$k]= $v;
                    else
                        $field_query[$k]= '"'.\mysqli_real_escape_string($this->conn, $v).'"';
                }
            }
            
            $sql= 'insert into '.$table.'('.PRIMARY_KEY.', '.implode(',', array_keys($field_query)).') values(null, '.implode(',',array_values($field_query)).');';
        }
        
        //\core\Ploof::debug(1, $sql);
        
        $this->query($sql);
        
        $id= ($existing) ? $data[PRIMARY_KEY] : \mysqli_insert_id($this->conn);
        //echo $id;
        //exit;
        return $id;
    }
}

?>