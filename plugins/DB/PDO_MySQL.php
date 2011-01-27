<?php
namespace plugins\DB;

class PDO_MySQL extends core\PluginInterfaceDB 
{
   /**
    *  The db handle. 
    *
    *  @var object
    *  @access private
    */
    private $_dbh;

   /**
    *  Number of rows affected by MySQL query.
    *
    *  @var int
    *  @access private
    */
    private $_affected_rows = 0;

   /**
    *  The result set associated with a prepared statement.
    *
    *  @var PDOStatement
    *  @access private
    */
    private $_statement;

   /**
    *  Controls how the rows will be returned.
    *
    *  @var string
    *  @access private
    */
    private $_fetch_style = 'assoc';

   /**
    *  Connects and selects database.
    *
    *  @access public
    *  @return void
    */
    public function __construct($host, $database, $username, $password) 
    {
        $dsn = 'mysql:host=' . $host . ';dbname=' . $database; 
        try 
        {
            $this->_dbh = new PDO($dsn, $username, $password);
            $this->_dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch ( PDOException $e ) 
        {
            // How to handle error reporting?
        }
    }

   /**
    *  Returns the number of rows affected by the last DELETE, INSERT, or UPDATE query.
    *
    *  @access public
    *  @return int
    */
    public function affected_rows() 
    {
    	return $this->_affected_rows;
    }

   /**
    *  Closes the connection.
    *
    *  @access public
    *  @return void
    */
    public function close()
    {
        $this->_dbh = null;
    }
    
   /**
    *  Fetches the next row from the result set in memory (i.e., the one
    *  that was created after running query()).
    *
    *  @access public
    *  @return mixed
    */
    public function fetch() 
    {
        return $this->_statement->fetch($this->_fetch_style);
    }

   /**
    *  Returns an array containing all of the result set rows.
    *
    *  @access public
    *  @return mixed
    */
    public function fetch_all() 
    {
        return $this->_statement->fetchAll($this->_fetch_style);
    }

   /**
    *  Returns a single column from the next row of a result set or false if there are no more rows.
    *
    *  @param int $column_number         Zero-index number of the column to retrieve from the row.
    *  @access public
    *  @return mixed
    */
    public function fetch_column($column_number=0) 
    {
        return $this->_statement->fetchColumn($column_number);
    }

   /**
    *  Inserts an object into the database. 
    *
    *  @param obj $obj        The object to be inserted. 
    *  @access public
    *  @return bool
    */
    public function insert($obj) 
    {
        $table = $this->classname_only($obj);
        $properties = get_object_vars($obj);

    	$sql = 'INSERT INTO `' . $table . '` ';
        
        $property_names = array_keys($properties);
    	$fields = '`' . implode('`, `', $property_names) . '`';
        $values = ':' . implode(', :', $property_names);
    
    	$sql .= '(' . $fields . ') VALUES (' . $values . ')';

    	$statement = $this->_dbh->prepare($sql);

        try 
        {
            $statement->execute($properties);
    	}
        catch ( PDOException $e ) 
        {
            // How to handle error reporting?
            return false;
        }

    	$this->_affected_rows = $statement->rowCount();
        return true;
    }

   /**
    *  Returns the ID of the last inserted row or sequence value.
    *
    *  @access public
    *  @return int
    */
    public function insert_id()
    {
        return $this->_dbh->lastInsertId();
    }

   /**
    *  Returns the number of rows affected by the last SELECT query.
    *
    *  @access public
    *  @return int       
    */
    public function num_rows()
    {
        $this->query('SELECT FOUND_ROWS()');
        $rows = $this->fetch_column();
        return $rows;
    }

   /**
    *  Executes SQL query.
    *
    *  @param string $sql           The SQL query to be executed.
    *  @param array $parameters     An array containing the parameters to be bound.
    *  @access public
    *  @return bool 
    */
    public function query($sql, $parameters=null) 
    {
        $statement = $this->_dbh->prepare($sql);
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
        $this->_statement = $statement;
    	return true;
    }

   /**
    *  Executes SQL query and returns the first row of the results.
    *
    *  @param string $sql                The SQL query to be executed.
    *  @param array $parameters          An array containing the parameters to be bound.
    *  @access public
    *  @return mixed       
    */
    public function query_first($sql, $parameters=null) 
    {
        $this->query($sql . ' LIMIT 1', $parameters);
        return $this->fetch();
    }

   /**
    *  Sets the fetch mode.
    *
    *  @param string $fetch_style        Controls how the rows will be returned.
    *  @access private
    *  @return int 
    */
    public function set_fetch_mode($fetch_style) 
    {
        switch ( $fetch_style ) 
        {
            case 'assoc':
                $fetch_style = PDO::FETCH_ASSOC;
                break;
            case 'both':
                $fetch_style = PDO::FETCH_BOTH;
                break;
            case 'class':
                $fetch_style = PDO::FETCH_CLASS;
                break;
            case 'into':
                $fetch_style = PDO::FETCH_INTO;
                break;
            case 'lazy':
                $fetch_style = PDO::FETCH_LAZY;
                break;
            case 'num':
                $fetch_style = PDO::FETCH_NUM;
                break;
            case 'obj':
                $fetch_style = PDO::FETCH_OBJ;
                break;
            default:
                $fetch_style = PDO::FETCH_ASSOC;
                break;
        }
        $this->_fetch_style = $fetch_style;
    }

   /**
    *  Updates an object in the database.
    *
    *  @param obj $obj              The object to be updated.
    *  @param string $where         The WHERE clause of the SQL query.
    *  @access public
    *  @return bool 
    */
    public function update($obj, $where = '1') 
    {
        $table = $this->classname_only($obj);
        $properties = get_object_vars($obj);

    	$sql = 'UPDATE `' . $table . '` SET ';
    
        $property_names = array_keys($properties);
    	foreach ( $property_names as $name ) 
        {
            $sql .= '`' . $name . '`=:' . $name . ', ';
    	}
    
    	$sql = rtrim($sql, ', ') . ' WHERE ' . $where;
    	$statement = $this->_dbh->prepare($sql);

        try 
        {
            $statement->execute($properties);
    	}
        catch ( PDOException $e ) 
        {
            // How to handle error reporting?
            return false;
        }
    
    	$this->_affected_rows = $statement->rowCount();
    	return true;
    }

   /**
    *  Inserts or updates (if exists) an object in the database.
    *
    *  @param obj $obj                The object to be upserted.
    *  @access public
    *  @return bool 
    */
    public function upsert($obj) 
    {
        $table = $this->classname_only($obj);
        $properties = get_object_vars($obj);

    	$sql = 'INSERT INTO `' . $table . '` ';
        
        $property_names = array_keys($properties);
    	$fields = '`' . implode('`, `', $property_names) . '`';
        $values = ':' . implode(', :', $property_names);
    
        $sql .= ' ON DUPLICATE KEY UPDATE `' . PRIMARY_KEY . '`=LAST_INSERT_ID(`' . PRIMARY_KEY . '`), ';

    	foreach ( $property_names as $name ) 
        {
            $sql .= '`' . $name . '`=:' . $name . ', ';
    	}

        $sql = rtrim($sql, ', ');
    	$statement = $this->_dbh->prepare($sql);

        try 
        {
            $statement->execute($insert_data);
    	}
        catch ( PDOException $e ) 
        {
            // How to handle error reporting?
            return false;
        }

    	$this->_affected_rows = $statement->rowCount();
        return true; 
    }
}

?>
