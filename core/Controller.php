<?
namespace core;

class Controller
{
    protected $data= array();
    private $assigns= array();
    
    public $protected= array(); // protected actions
    public $handler= null; // handler for errors, array('controller'=>foo, 'action'=>bar)
    
    public static $layout= null;
        
    function sanitize_inputs()
    {
        if (!USE_MYSQLI)
        {
            if (is_array($this->data))
                foreach($this->data as $k=>$v)
                {
                     $this->data[$k]= \mysql_real_escape_string($v);
                }
        }
    }
    
    function assign($name, $value)
    {
        $this->assigns[$name]= $value;
    }
    
    function call($action, $set_assigns=null)
    {
        $this->data= $_REQUEST["data"];

        if ($set_assigns)
        {
            $this->assigns= $set_assigns;
        }
        
        if ($this->id === null)
            $this->id = $_REQUEST["id"];
        
        if (SANITIZE_INPUT)
                $this->sanitize_inputs();
        
        if (method_exists($this, $action))
        {
            try
            {
                $this->debug(5, "Calling action $action...");
                $this->$action();
                $this->debug(5, "Call complete.");   
            }
            catch (\Exception $e)
            {
                if ($this->handler)
                {
                    render($this->handler['controller'], $this->handler['action'], $this->id);
                    exit;
                }
                print_r('<pre>Controller caught a message it didn\'t know how to handle:');
                print_r($e->getMessage());
                print_r($e->getTraceAsString());
                print_r('</pre>');
                exit;
            }                    
        }
        foreach($this->assigns as $name=>$value)
        {
            $$name= $value;
        }
        
        if (file_exists("../view/".classname_only(static::classname())."/".$action.VIEW_EXTENSION))
            include("../view/".classname_only(static::classname())."/".$action.VIEW_EXTENSION);
    }
    
    static function debug($level, $msg)
    {
        if ($level <= DEBUG_LEVEL)
        {
            echo "<pre>";
            echo static::classname()."($level): ";
            print_r($msg);
            echo "</pre>";
        }
    }
    
    static function classname()
    {
        return __CLASS__;
    } 
}

?>