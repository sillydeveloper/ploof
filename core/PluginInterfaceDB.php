<?
namespace core;

interface PluginInterfaceDB
{
    // build a better database plugin!
    
    // load a single row in table identified by id
    function load_row($table, $id);
    
    // find everything in table matching where_array
    function find_rows($table, $where_array);
    
    // get a list of tables
    function show_tables();
    
    // get a list of columns, with type information, for table
    function get_table_columns($table);
    
    // is field_type considered numeric for this database?
    function is_numeric_datatype($field_type);
    
    // delete a row from table identified by id
    function delete_row($table, $id);
    
    // store data into table
    function store_row($table, $data);
    
    // not sure if this is really required...?
    function query($sql);
    
    function is_date_datatype($field_type);
}
?>
