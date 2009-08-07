<?

class Dashboard extends core\Controller
{
    function index()
    {
        $this->assign("contact_email", "andrew@frugalprogrammer.com");
        
        $c= new Customer(2);
        $this->assign("customer", $c);
    }   
    
    static function classname()
    {
        return __CLASS__;
    }
}