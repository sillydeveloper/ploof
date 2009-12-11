<?
namespace core;

class DB
{
    private static $db;
    private $connect_id;
    
    private function __construct($username, $password, $host, $database)
    {
        $this->connect_id = \mysqli_connect($host, $username, $password, $database);
    }

    public static function getInstance()
    {
        if (empty(self::$db))
            self::$db = new DB(DATABASE_USER, DATABASE_PASS, DATABASE_HOST, DATABASE_NAME);

        return self::$db;
    }
    public static function query($sql)                        { return self::getInstance()->run_sql($sql); }
    public static function query_first($sql, $list = false)    { return self::getInstance()->query_first($sql,$list); }
    
    public function run_sql($sql)
    {
        return \mysqli_query($this->connect_id, $sql);
    }
}

?>