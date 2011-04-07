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
        
    }
    
    // find everything in table matching where_array
    function find_rows($table, $where_array)
    {
        
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
        while($col= \mysqli_fetch_assoc($result))
        {
            $columns[$col['Field']]= $col['Type'];
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
        
        if (!$data[PRIMARY_KEY])
            $existing= false;
        
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
                
                    if ($this->is_belongs_to($k) and is_object($v))
                    {
                        // remember: it's a joiner object.
                    }
                    elseif ($this->is_numeric_datatype($col_types[$k]) or $v === "NULL" )
                    {
                        $field_query[]= $k."=".$v."";
                    }
                    else
                        $field_query[]= $k."='".$this->sanitize($k, $v)."'";         
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
                        $field_query[$k]= $this->sanitize($k, $v);
                    else
                        $field_query[$k]= '"'.$v.'"';
                }
            }
            
            $sql= 'insert into '.$table.'('.PRIMARY_KEY.', '.implode(',', array_keys($field_query)).') values(null, '.implode(',',array_values($field_query)).');';
        }
        
        $this->query($sql);
        
        $id= ($existing) ? $data[PRIMARY_KEY] : \mysqli_insert_id($this->conn);
        return $id;
    }
}

?>