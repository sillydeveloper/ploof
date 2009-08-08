<?

class Dashboard extends core\Controller
{
    function index()
    {
        $this->assign("contact_email", "andrew@frugalprogrammer.com");
    }   
    
    static function classname()
    {
        return __CLASS__;
    }
}