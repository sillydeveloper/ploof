<?
namespace core;

class Controller
{
    protected $data= array();
    public $layout= "default";
    private $assigns= array();
    
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

        if (SANITIZE_INPUT)
                $this->sanitize_inputs();
                
        $this->$action();
        
        foreach($this->assigns as $name=>$value)
        {
            $$name = $value;
        }
            
        include("../view/".static::classname()."/".$action.VIEW_EXTENSION);
    }
    
    static function classname()
    {
        return __CLASS__;
    } 
}

?>