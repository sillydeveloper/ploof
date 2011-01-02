<?

class TestRunner extends core\Controller
{
    
    function index()
    {   
        $this->assign('tests', File::list_files(BASE_INSTALL."/test/framework/"));
    }
        
    static function classname()
    {
        return __CLASS__;
    }
}