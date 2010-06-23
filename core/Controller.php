<?
namespace core;

class Controller extends Ploof
{
    protected $data= array();
    private $assigns= array();
    
    private $use_routes= false;
    
    public $protected= null; // array of blacklist protected actions
    public $protected_exceptions= null; // array of whitelist unprotected actions)
    public $handler= null; // handler for errors, array('controller'=>foo, 'action'=>bar)
    
    // override the default layout:
    public static $layout= null;
    
    // if this controller is not a pluralization of a model object
    //  set this in the controller:
    protected static $object= null;
    
    // set via /parent_object_controller/parent_id/this_controller/this_action
    protected static $parent= null; 
    
    // set automatically if ajax call is detected:
    protected static $is_ajax= false;
    
    public $routable_actions= array();
    public $routable_messages= array();
    
    static function object()
    {
        if (static::$object)
            return static::$object;
            
        $name= classname_only(static::classname());
        // by default, assume it's a plural version of a Model object
        //  but they may have customized this method:
        return convert_controller_to_object_name($name);
    }
    
    static function object_instance($id=null)
    {
        $classname= static::object();
        return new $classname($id);
    }
        
    function protect($action)
    {
        // override to protect things.
    }
    
    function preload($action)
    {
        // called before call(). override to do stuff before loading $action.
    }
    
    function redirect($url)
    {
        if (headers_sent())
            print '<META CONTENT="0; URL='.$url.'" HTTP-EQUIV="REFRESH"/>';
        else
            header("Location: $url");
        exit();
    }
    
    function assign($name, $value)
    {
        $this->assigns[$name]= $value;
    }
    
    function get_assign($name)
    {
        return $this->assigns[$name];
    }
    
    function call($action, $set_assigns=null, $id=null)
    {
        $this->data= $_REQUEST["data"];

        if ($set_assigns)
        {
            foreach($set_assigns as $k=>$v)
                $_REQUEST[$k]= $v;
            
            $this->assigns= $set_assigns;
        }
        
        if ($this->id === null and $id)
            $this->id= $id;
        
        try
        {
            if ($this->is_protected($action))
                $this->protect($action); // should throw an exception
            
            if (method_exists($this, $action))
            {
                $this->preload($action);
                
                if ($_REQUEST["parent"] and $_REQUEST["parentid"])
                {
                    $this->parent= $_REQUEST["parent"]::object_instance($_REQUEST["parentid"]);
                    $this->debug(5, "Parent detected: ".$_REQUEST["parent"].", ".$_REQUEST["parentid"]);
                }
                
                if ($_REQUEST['ajax'])
                {
                    $this->debug(4, "Ajax detected");
                    $this->is_ajax= true;
                }
				else
					echo '<!-- begin '.$this->classname().'::'.$action.' -->';
                    
                $this->debug(4, "Calling action $action...");
                $this->$action();
                
                if ($_REQUEST['redir'])
                {
                    $this->debug(4, "redir command found, sending to ".$_REQUEST['redir']);
                    Session::push_request();
                    $this->redirect($_REQUEST['redir']);
                }
                elseif ($this->is_routable($action))
                {
                    $this->debug(4, "Form content found for a routable action; using routes...");
                    $this->use_routes= true;
                    $this->route($action);
                }
                
                // if it isn't an ajax call, 
                //  document it on the stack:
                if (!$this->is_ajax)
                {
                    Session::push_request();
                }
                                
                $this->debug(4, "Call complete.");   
            }   
            
            if (!$this->use_routes)
            {
                // assigns should be handled after the call check
                //  so that pass-thru assigns can still be assigned downwards.
                foreach($this->assigns as $name=>$value)
                {
                    $$name= $value;
                }
            
                if (file_exists("../view/".classname_only(static::classname())."/".$action.VIEW_EXTENSION))
                    include("../view/".classname_only(static::classname())."/".$action.VIEW_EXTENSION);
            }  
        }
        catch (\Exception $e)
        {
            if ($this->handler)
            {
                render("/".$this->handler['controller']."/".$this->handler['action']."/".$this->id);
            }
            
            print_r('<pre>Controller caught a message it didn\'t know how to handle:');
            print_r($e->getMessage());
            print_r($e->getTraceAsString());
            print_r('</pre>');
            exit;
        }  
		if(!$this->is_ajax)      
			echo '<!-- end '.$this->classname().'::'.$action.' -->';            
    } // end controller::call
    
    function is_protected($action)
    {
        if (is_array($this->protected))
            return (array_search($action, $this->protected) !== false);
            
        if (is_array($this->protected_exceptions))
            return (array_search($action, $this->protected_exceptions) === false);

        return false;
    }
    
    function route($action)
    {
        Session::set_message(Session::NOTICE, $this->get_routable_message($action));
        $last_url= Session::pop_request();
        $last_url= preg_replace("/[\\?&]popdialog=([^&#]*)/", "", $last_url);
        $this->redirect($last_url);
    }
    
    function is_routable($action)
    {
        return (array_search($action, $this->routable_actions) !== false);
    }
    
    function get_routable_message($action)
    {
        return $this->routable_messages[$action];
    }
    
    static function classname()
    {
        return __CLASS__;
    } 
}

?>