<?
namespace core;

abstract class DB extends Ploof
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
    abstract public function __construct($host, $database, $username, $password);

   /**
    *  Returns the number of rows affected by the last DELETE, INSERT, or UPDATE query.
    *
    *  @access public
    *  @return int
    */
    abstract public function affected_rows();
    
   /**
    *  Closes the connection.
    *
    *  @access public
    *  @return void
    */
    abstract public function close();

   /**
    *  Fetches the next row from a result set.
    *
    *  @param $res                  The SQL result set from which to fetch rows.
    *  @access public
    *  @return mixed
    */
    abstract public function fetch($res);
    
   /**
    *  Returns an array containing all of the result set rows.
    *
    *  @param $res                  The SQL result set from which to fetch rows.
    *  @access public
    *  @return mixed
    */
    abstract public function fetch_all($res);

   /**
    *  Inserts data by means of an array.
    *
    *  @param string $table         The SQL table to be inserted into.
    *  @param array $data           The array containing the fields and values ($field => $value).
    *  @access public
    *  @return bool
    */
    abstract public function insert($table, $data);

   /**
    *  Returns the ID of the last inserted row or sequence value.
    *
    *  @access public
    *  @return int
    */
    abstract public function insert_id();

   /**
    *  Checks that index is a valid, positive integer.
    *  
    *  @param int $id               The integer to be checked.
    *  @access public          
    *  @return bool
    */ 
    abstract public function is_valid_id($id);
    
   /**
    *  Returns the number of rows affected by the last SELECT query.
    *
    *  @access public
    *  @return int       
    */
    abstract public function num_rows();

   /**
    *  Executes SQL query.
    *
    *  @param string $sql           The SQL query to be executed.
    *  @access public
    *  @return resource       
    */
    abstract public function query($sql);
    
   /**
    *  Executes SQL query and returns the first row of the results.
    *
    *  @param string $sql           The SQL query to be executed.
    *  @access public
    *  @return mixed       
    */
    abstract public function query_first($sql);

   /**
    *  Updates query by means of an array.
    *
    *  @param string $table         The SQL table to be updated.
    *  @param array $data           The array containing the fields and values ($field => $value).
    *  @param string $where         The WHERE clause of the SQL query.
    *  @access public
    *  @return bool 
    */
    abstract public function update($table, $data, $where);

   /**
    *  Inserts or updates (if exists) data by means of an array.
    *
    *  @param string $table         The SQL table to be inserted into.
    *  @param array $insert_data    The array containing the fields and values ($field => $value) for the INSERT clause.
    *  @param array $update_data    The array containing the fields and values ($field => $value) for the UPDATE clause. 
    *  @access public
    *  @return bool 
    */
    abstract public function upsert($table, $insert_data, $update_data);
}

?>
