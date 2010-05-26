<?
// TODO: make this not required in each test file:
define("SCHEMA", "framework");

// note this also tests a lot of joiner too:
class ControllerTest extends \core\TestCase
{
    function test_is_routable()
    {
        $c= new core\Controller();
        $c->routable_actions= array("test", "test2", "test3");
        $c->routable_messages= array("test"=>"foo");
        
        $this->assertEquals(true, $c->is_routable('test2'));
        $this->assertEquals(false, $c->is_routable('monkeys'));
    }

    function test_get_routable_message()
    {
        $c= new core\Controller();
        $c->routable_actions= array("test");
        $c->routable_messages= array("test"=>"foo");
        
        $this->assertEquals("foo", $c->get_routable_message('test'));
        $this->assertEquals("", $c->get_routable_message('monkeys'));   
    }
    
    function test_call_with_assign()
    {
        $c= new core\Controller();
        $c->fun= function() { $this->assign('foo', 'bar'); echo 'hi'; };
        //$c->call('fun');
        //$c->fun();
        //$this->assertEquals('bar', $c->get_assign('foo'));
    }
}