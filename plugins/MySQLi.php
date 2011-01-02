<?
namespace core;

class MySQLi extends core\DB
{
    private static $db;
    public $connect_id;
    
    public function __construct($host, $database, $username, $password)
    {
        $this->connect_id = \mysqli_connect($host, $username, $password, $database) or die("Could not connect to mysqli");
    }

    public function insert_id()
    {   
        $id= \mysqli_insert_id($this->connect_id);
        return $id;
    }
    
    public function fetch_array($res)
    {
        if ($res)
        {
            return \mysqli_fetch_array($res);
        }
        return false;
    }
    
    public function fetch_assoc($res)
    {
        if ($res)
        {
            return \mysqli_fetch_assoc($res);
        }
        return false;
    }
    
    public function query_first($sql)
    {
        $res= $this->query($sql);
    
        $result= $this->fetch_array($res);            
        if (is_array($result))
            return array_pop($result);
        
        return false;
    }
    
    public static function num_rows($res)
    {
        return mysqli_num_rows($res);
    }
    
    public static function query($sql)
    { 
        return $this->run_sql($sql); 
    }
    
    public function run_sql($sql)
    {
        return \mysqli_query($this->connect_id, $sql);// or die("Could not execute query: \n".mysqli_error());
    }
}

?>
