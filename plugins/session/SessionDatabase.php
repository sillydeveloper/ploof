<?php
namespace plugins\Session;

class SessionDatabase extends core\AbstractSession
{

   /**
    *  Contains the Database object.
    *
    *  @var object $_db
    *  @access private
    */
    private $_db;

   /**
    *  The timestamp of the user's last activity.
    *
    *  @var int $_last_active
    *  @access private
    */
    private $_last_active;

   /**
    *  The user's id.
    *
    *  @var int $_user_id
    *  @access private
    */
    private $_user_id = 0;

    const SESSION_LIFETIME    = 3600;            // 60 minutes
    const LOGIN_COOKIE_EXPIRE = 2592000;         // Cookie expiration date (30 days)
    const SESSION_SALT        = 'M^mc?(9%ZKx[';  // Session salt
    const COOKIE_ID_NAME      = 'oid';           // Name of the cookie for the user date

   /**
    *  Constructor.
    * 
    *  @param PDO object $dbh       The database handler.
    *  @access public
    *  @return void
    */
    public function __construct($dbh) 
    {
        $this->_db = $dbh;
        $this->_last_active = time();

        session_set_save_handler(array($this,'open'),
                                 array($this,'close'),
                                 array($this,'read'),
                                 array($this,'write'),
                                 array($this,'destroy'),
                                 array($this,'gc'));

        register_shutdown_function('session_write_close');

        session_start();
    }

    public function clear_messages($type=null)
    {
        if ($type)
        {
            $sys = $this->get("PLOOF_MESSAGES");
            unset($sys[$type]);
            $this->set("PLOOF_MESSAGES", $sys);
        }
        else
        {
            $this->clear("PLOOF_MESSAGES");
        }
    }

   /**
    *  Executes when the session operation is done.
    * 
    *  @access public
    *  @return bool
    */
    public function close() 
    {
        return true;
    }

   /**
    *  Creates a new login session.
    *       
    *  @param int $user_id     The user's ID.
    *  @access private
    *  @return void
    */
    private function _create($user_id) 
    {
        $this->_user_id = $user_id;
        session_regenerate_id(true);
        $_SESSION = array();
        $_SESSION['uid'] = $user_id;
        $_SESSION['fingerprint'] = $this->_get_fingerprint($user_id); 
        $_SESSION['last_active'] = $this->_last_active;
        setcookie(self::COOKIE_ID_NAME, $this->_encrypt_id($user_id), time() + self::LOGIN_COOKIE_EXPIRE);
    }

   /**
    *  Decrypts cookie user ID.
    *       
    *  @param string $hex_hash      The hash to be decrypted.
    *  @access private
    *  @return int
    */
    private function _decrypt_cookie($hex_hash) 
    {
        $hex_hash = filter_var($hex_hash, FILTER_SANITIZE_STRING);
        if ( strlen($hex_hash) !== 40 ) 
        {
            return false;
        }
        // Extrapolate hex from hash
        $cur_pos = 0;
        $hex_id = '';
        for ( $i = 0; $i <= 7; $i++ ) 
        {
            $cur_pos += $i + 1; 
            $hex_id .= substr($hex_hash, $cur_pos, 1);
        }
        // Convert hex to user id
        return hexdec($hex_id);
    }

   /**
    *  Executes when a session is destroyed.
    * 
    *  @param string $session_id        The session id.
    *  @access public
    *  @return bool
    */
    public function destroy($session_id) 
    {
        $sql = 'DELETE FROM `' . $this->_db->get_table_name('session') . '` WHERE `session_id`=:session_id';
        $params = array('session_id' => $session_id);
        if ( !$this->_db->query($sql, $params) ) 
        {
            return false;
        }
        return true;
    } 

   /**
    *  Encrypts user ID for cookie use.
    *       
    *  @param int $user_id      The user's ID.
    *  @access private
    *  @return string
    */
    private function _encrypt_id($user_id) 
    {
        // Create the hash
        $hex_salt = 'R1c?+r.VEfIN';
        $hex_hash = sha1($hex_salt . $user_id);
        // Convert user id to 8-digit hex
        $user_hex = $this->_zeropad(dechex($user_id), 8);
        // Interpolate hex into hash
        $cur_pos = 0;
        for ( $i = 0; $i <= 7; $i++ ) 
        {
            $cur_pos += $i + 1; 
            $hex_hash = substr_replace($hex_hash, substr($user_hex, $i, 1), $cur_pos, 1);
        }
        return $hex_hash;
    }

   /**
    *  Executes when the garbage collector is executed.
    * 
    *  @param int $max_lifetime        The max session lifetime.
    *  @access public
    *  @return bool
    */
    public function gc($max_lifetime) 
    {
        $expired = $this->_last_active - $max_lifetime;
        $sql = 'DELETE FROM `' . $this->_db->get_table_name('session') . '` WHERE `last_active`<:expired';
        $params = array('expired' => $expired);
        if ( !$this->_db->query($sql, $params) ) 
        {
            return false;
        }
        return true;
    }

   /**
    *  Retrieves a session variable.
    *       
    *  @param string $name       The name of the variable.
    *  @access public
    *  @return void
    */
    public function get($name)
    {
        return $_SESSION[$name];
    }

   /**
    *  Returns the user's session fingerprint.
    *       
    *  @param int $user_id     The user's ID.     
    *  @access private
    *  @return string
    */
    private function _get_fingerprint($user_id) 
    {
        return sha1(self::SESSION_SALT . $user_id . $_SERVER['HTTP_USER_AGENT']);
    }

    public function get_messages($type=null)
    {
        $msgs = $this->get("PLOOF_MESSAGES");
        
        $message = ($type) ? $msgs[$type] : $msgs;
        
        return $message;
    }
    
   /**
    *  Returns the user's ID based on whether or not their session is
    *  still valid.
    *       
    *  @access public
    *  @return int
    */
    public function get_user_id() 
    {
        if ( (!isset($_SESSION['uid'])) || (!isset($_COOKIE[self::COOKIE_ID_NAME])) ||
             ($_SESSION['uid'] !== $this->_decrypt_cookie($_COOKIE[self::COOKIE_ID_NAME])) || 
             (!isset($_SESSION['fingerprint'])) || ($_SESSION['fingerprint'] !== $this->_get_fingerprint($_SESSION['uid'])) ) 
             {
            $this->_user_id = 0;
            $this->kill();
        }
        elseif ( (!isset($_SESSION['last_active'])) || ($_SESSION['last_active'] + self::SESSION_LIFETIME < time()) ) 
        {
            $this->_user_id = 0;
            $this->reset();
        }
        else 
        {
            $this->_user_id = $_SESSION['uid'];
            $_SESSION['last_active'] = $this->_last_active;
        }
        return $this->_user_id;
    }

    public function has_messages($type)
    {
        return (count($this->get_messages($type, false)) > 0);
    }
    
   /**
    *  Ends the current session and deletes the login cookie.
    *       
    *  @access public
    *  @return void
    */
    public function kill() 
    {
        $_SESSION = array();
        setcookie(self::COOKIE_ID_NAME, '', time() - 3600);
        session_unset();
        session_destroy();
    }
   
   /**
    *  Logs a user in.
    *
    *  @param string $username    The user's username.
    *  @param string $password    The user's password.
    *  @param string $ip          The user's IP address.
    *  @access public
    *  @return int                The user's ID.
    */
    public function login($username, $password, $ip) {
        if ( $this->_user_id !== 0 ) 
        {
            return $this->get_user_id();
        }

        $sql = 'SELECT `user_id`, `password`, `join_date` FROM `' . $this->_db->get_table_name('user') . '` WHERE `username`=:username';  
        $params = array('username' => $username);
        $record = $this->_db->query_first($sql, $params, 'assoc');
        if ( !$record ) 
        {
            return false; 
        }
        
        // Check that password matches
        $encrypt = new Encrypt();
        $user_id = (int) $record['user_id'];
        $hashed_pass = $encrypt->password($password, $user_id, $username, $record['join_date']);
        if ( $record['password'] !== $hashed_pass ) 
        {
            return false;
        }
        
        // Format data
        $data['ip'] = sprintf("%u", ip2long($ip));
        $data['last_login'] = date('Y-m-d H:i:s');
        if ( !$this->_db->update('user', $data, '`user_id`=' . $user_id) ) {
            return false;
        }
       
        $this->_create($user_id); 

        return true;
    }

   /**
    *  Logs a user out.
    *
    *  @access public
    *  @return void
    */ 
    public function logout()
    {
        $this->kill();
    }

   /**
    *  Executes when the session is being opened.
    * 
    *  @access public
    *  @return bool
    */
    public function open() 
    {
        return true;
    }

    public function pop_request()
    {
        $sys = $this->get("PLOOF_ROUTES");
        $res = array_pop($sys);
        $this->set("PLOOF_ROUTES", $sys);
        return $res;
    }

    /* Does this work when user can click 'back' button */
    public function push_request()  
    {
        $sys = $this->get("PLOOF_ROUTES");
        if (is_array($sys)) 
        {
            array_push($sys, $_SERVER['REQUEST_URI']);
        }
        else
        {
            $sys= array($_SERVER['REQUEST_URI']);
        }
        
        // keep the queue at 5 entries:
        if (count($sys) > 5)
        {
            $sys= array_slice($sys, -5, 5);
        }
            
        $this->set("PLOOF_ROUTES", $sys);
    }
    
   /**
    *  Reads the session data.  MUST return a string for save handler
    *  to work as expected.
    * 
    *  @param string $session_id      The session id.
    *  @access public
    *  @return string
    */
    public function read($session_id) 
    {
        $sql = 'SELECT `data` FROM `' . $this->_db->get_table_name('session') . '` WHERE `session_id`=:session_id';  
        $params = array('session_id' => $session_id);
        $query = $this->_db->query($sql, $params);
        $record = $query->fetch(PDO::FETCH_ASSOC); 
        if ( !$record )
        {
            return '';
        }
        return $record['data'];
    }

   /**
    *  Provides the redirect location based on the page provided.
    *       
    *  @param string $page      The page to be checked.
    *  @access public
    *  @return string
    */
    public function redirect($page) 
    {
        $query = '?' . parse_url($page, PHP_URL_QUERY);
        $page = str_replace($query, '', $page);
        $redirect_location = $_SERVER['SERVER_NAME'] . '/';
        switch ( $page ) 
        {
            default:
                $redirect_location .= 'index.php';
                break;    
        }
        return $redirect_location;
    }

   /**
    *  Ends the current session and starts a new one.
    *       
    *  @access public
    *  @return void
    */
    public function reset() 
    {
        session_unset();
        session_destroy();
        session_start();
        session_regenerate_id();
        $_SESSION = array();
    }

   /**
    *  Sets a session variable.
    *       
    *  @param string $name       The name of the variable.
    *  @param mixed $value       The value to be set.
    *  @access public
    *  @return void
    */
    public function set($name, $value)
    {
        $_SESSION[$name]= $value;
    }
    
    public function set_message($type, $msg)
    {
        $sys= $this->get("PLOOF_MESSAGES");
        if (is_array($sys[$type]))
        {
            array_push($sys[$type], $msg);
        }
        else
        {
            $sys= array($type => array($msg));
        }
        
        $this->set("PLOOF_MESSAGES", $sys);
    }
    
   /**
    *  Saves the session data.
    * 
    *  @param string $session_id        The session id.
    *  @param string $data              The session data.
    *  @access public
    *  @return int                      The lastInsertID.
    */
    public function write($session_id, $data) 
    {
        $insert = array('session_id'  => $session_id,
                        'user_id'     => $this->_user_id,
                        'data'        => $data,
                        'last_active' => date('Y-m-d H:i:s', $this->_last_active));
        return $this->_db->upsert('session', $insert, $insert);       
    }

   /**
    *  Pads a string with leading zeroes.
    *       
    *  @param string $num      The string to be padded.
    *  @param int $limit       The length of the final string.
    *  @access private
    *  @return string
    */
    private function _zeropad($num, $limit) 
    {
        return str_repeat('0', max(0, $limit - strlen($num))) . $num;
    }
    
}

?>
