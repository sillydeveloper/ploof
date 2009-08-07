<?
namespace core;

class Controller
{
    protected $data= array();
    public $layout= "default";
    private $assigns= array();
    
    function sanitize_inputs()
    {
        $this->data= $_REQUEST["data"];
        foreach($this->data as $k=>$v)
        {
            $this->data[$k]= $v;
        }
    }
    
    function assign($name, $value)
    {
        $this->assigns[$name]= $value;
    }
    
    function call($action)
    {
        $this->$action();
        foreach($this->assigns as $name=>$value)
        {
            if (is_array($value) or is_object($value))
                $$name = $value;
            else
                eval("$".$name."='".$value."';");
        }
            
        include("../view/".static::classname()."/".$action.VIEW_EXTENSION);
    }
    
    static function classname()
    {
        return __CLASS__;
    } 
}

?>