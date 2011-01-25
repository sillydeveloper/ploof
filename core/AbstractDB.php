<?
namespace core;

abstract class AbstractDB extends Ploof
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
    * $host, $database, $username, $password
    */
    abstract public function __construct();
    abstract function load($table, $id);
    abstract function find($table, $where_array);
    abstract function show_tables();
    abstract function show_columns($table);
    abstract function is_numeric($field_type);
    abstract function query($sql);
}

?>
