<?
// note this also tests a lot of joiner too:
class SessionDBTest extends \core\TestCase
{    
    function test_init()
    {
        $init_values= array('Parents'=>array(
                            array('id'=>1, 'phrase'=>'Just dont understand')
                        )
                    );
                    
        $init_types= array('Parents'=>array('id'=>'int', 'phrase'=>'char'));
                        
        $db= new \plugins\DB\SessionDB($init_values, $init_types);
        $this->assertEquals(\core\Session::get('SessionDBValues'), $init_values, 'Values dont match on init');
        $this->assertEquals(\core\Session::get('SessionDBTypes'), $init_types, 'Types dont match on init');
    }
    
    
    
}
?>