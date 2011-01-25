<?
namespace plugins\DB;

// stores data in an array in the session:
//  table=>key=>value
//  
//
class SessionDB extends \core\AbstractDB
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
    public function __construct($init= array())
    {
        \core\Session::set('SessionDB', $init);
    }
    
    // where array: array('key'=>'value')
    //  this is the simplified loader used by the model.
    function load($table, $id)
    {
        return array_pop($this->find($table, array(PRIMARY_KEY=>$id)));
    }
    
    function find($table, $where_array)
    {
        $results= array();
        $db= \core\Session::get('SessionDB');
        
        $table_data= $db[$table];
        $this->debug(1, $table);
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
        
    }
    function show_columns($table)
    {
        
    }
    function is_numeric($field_type)
    {
        
    }
    function query($sql)
    {
        
    }
}

?>
