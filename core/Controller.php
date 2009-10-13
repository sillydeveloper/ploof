<?
namespace core;

class Controller
{
    protected $data= array();
    private $assigns= array();
    
    public static $layout= null;
        
    function sanitize_inputs()
    {
        // TODO
    }
    
    function assign($name, $value)
    {
        $this->assigns[$name]= $value;
    }
    
    function call($action)
    {
        $this->data= $_REQUEST["data"];
        
        if (!$this->id)
            $this->id = $_REQUEST["id"];
        
        $this->assign('session_object', Session::get('object'));

        if (SANITIZE_INPUT)
                $this->sanitize_inputs();
                
        $this->$action();
        
        foreach($this->assigns as $name=>$value)
        {
            $$name = $value;
        }
            
        include("../view/".classname_only(static::classname())."/".$action.VIEW_EXTENSION);
    }
    
    static function classname()
    {
        return __CLASS__;
    } 
}

?>