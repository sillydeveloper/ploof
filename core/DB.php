<?
namespace core;

class DB extends Ploof
{
    private static $db;
    private $connect_id;
    
    private function __construct($username, $password, $host, $database)
    {
        if (USE_MYSQLI)
            $this->connect_id = \mysqli_connect($host, $username, $password, $database);
        else
        {
            $this->connect_id = \mysql_connect($host, $username, $password) or die("Could not connect to mysql");
            if (IN_UNIT_TESTING)
                mysql_select_db(TEST_DATABASE_NAME);
            else
                mysql_select_db(DATABASE_NAME);
        }
    }

    public static function getInstance()
    {
        if (IN_UNIT_TESTING)
            self::$db = new DB(TEST_DATABASE_USER, TEST_DATABASE_PASS, TEST_DATABASE_HOST, TEST_DATABASE_NAME);
        elseif (empty(self::$db))
            self::$db = new DB(DATABASE_USER, DATABASE_PASS, DATABASE_HOST, DATABASE_NAME);
        
        return self::$db;
    }
    
    static function insert_id()
    {   
        if (USE_MYSQLI)
            return \mysqli_insert_id(self::getInstance()->connect_id);
        else 
            return \mysql_insert_id();
    }
    
    static function fetch_array($res)
    {
        if ($res)
        {
            if (USE_MYSQLI)
                return \mysqli_fetch_array($res);
            else
                return \mysql_fetch_array($res);
        }
        return false;
    }
    
    static function fetch_assoc($res)
    {
        if ($res)
        {
            if (USE_MYSQLI)
                return \mysqli_fetch_assoc($res);
            else
                return \mysql_fetch_assoc($res);
        }
        return false;
    }
    
    static function query_first($sql)
    {
        $res= self::getInstance()->query($sql);
        
        if (USE_MYSQLI)
            $result= \mysqli_fetch_array($res);
        else
            $result= \mysql_fetch_array($res);
            
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
        return self::getInstance()->run_sql($sql); 
    }
    
    public function run_sql($sql)
    {
        if (USE_MYSQLI)
            return \mysqli_query($this->connect_id, $sql);// or die("Could not execute query: \n".mysqli_error());
        else
        {
            return \mysql_query($sql);// or die("Could not execute query: \n".mysql_error());
        }
    }
}

?>