<?
namespace plugins;

class MySQLi extends core\AbstractDB
{
   /**
    *  The db handle. 
    *
    *  @var object
    *  @access private
    */
    private $_dbh;

    public $connect_id;
    
    public function __construct($host, $database, $username, $password)
    {
        $this->_dbh = new mysqli($host, $username, $password, $database);
        if ($this->_dbh->connect_error) {
            //die('Connect Error (' . $this->_dbh->connect_errno . ') ' . $this->_dbh->connect_error);
            // How to handle error reporting?
        }
    }

    public function insert_id()
    {   
        return $this->_dbh->insert_id;
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
    
    public static function query($sql, $parameters=null)
    { 
        $statement = $this->_dbh->stmt_init();
        $statement->prepare($sql);
        if ( is_array($parameters) ) 
        {
            foreach ( $parameters as $field => &$value ) 
            {
                $statement->bindParam(':' . $field, $value);
            }
        }
        try 
        {
            $statement->execute();
    	}
        catch ( PDOException $e ) 
        {
            // How to handle error reporting?
            $this->_affected_rows = 0;
            return false;
        }
    
    	$this->_affected_rows = $statement->rowCount();
    	return $statement;
    }
    
}

?>
