<?
namespace core;

abstract class AbstractDatabase extends Ploof
{
    // build a better database plugin!
    abstract public function __construct();
    
    // load a single row in table identified by id
    abstract function load($table, $id);
    
    // find everything in table matching where_array
    abstract function find($table, $where_array);
    
    // get a list of tables
    abstract function show_tables();
    
    // get a list of columns, with type information, for table
    abstract function get_columns($table);
    
    // is field_type considered numeric for this database?
    abstract function is_numeric($field_type);
    
    // delete a row from table identified by id
    abstract function delete($table, $id);
    
    // store data into table
    abstract function store($table, $data);
    
    // not sure if this is really required...?
    abstract function query($sql);
    
    abstract function is_date($field_type);
}
?>
