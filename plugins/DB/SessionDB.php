<?
namespace plugins\DB;

// stores data in an array in the session:
//  table=>(row=>(key=>value))
//  
//
class SessionDB implements \core\PluginInterfaceDB
{
   /**
    *  The db handle. 
    *
    *  @var object
    *  @access private
    */
    private $_dbh;
    
   /**
    *  Connects and selects database.
    *
    *  @access public
    *  @return void
    */
    public function __construct($init_values= array(), $init_types= array())
    {
        \core\Session::set('SessionDBValues', $init_values);
        \core\Session::set('SessionDBTypes', $init_types);
    }
    
    function load_row($table, $id)
    {
        return array_pop($this->find_rows($table, array(PRIMARY_KEY=>$id)));
    }
    
    // where array: array('key'=>'value')
    //  this is the simplified finder used by core.
    function find_rows($table, $where_array=null)
    {
        $results= array();
        $db= \core\Session::get('SessionDBValues');
        
        $table_data= $db[$table];
        
        if (!$where_array)
            return $table_data;
                    
        foreach($table_data as $td)
        {
            foreach($where_array as $key=>$value)
            {
                if ($td[$key] == $value)
                {
                    $results[]= $td;
                }
            }
        }
        return $results;
    }
    
    function show_tables()
    {
        return array_keys(\core\Session::get('SessionDBValues'));
    }
    
    function get_table_columns($table)
    {
        $db= \core\Session::get('SessionDBTypes');
        return $db[$table];
    }
    
    function delete_row($table, $id)
    {
        
        
    }
    
    function store_row($table, $data)
    {
        $stored_data= \core\Session::get('SessionDBValues');
        $id_to_find= $data['id'];
        foreach($stored_data[$table] as $key=>$td)
        {
            if ($td['id'] == $id_to_find)
            {
                $stored_data[$table][$key]= $data;
            }
        }
        \core\Session::set('SessionDBValues', $stored_data);
    }
    
    function is_numeric_datatype($field_type)
    {
        
    }
    
    function query($sql)
    {
        
    }
    
    function is_date_datatype($field_type)
    {
        return ($field_type == 'date');
    }
    
    static function classname()
    {
        return __CLASS__;
    }
}

?>
