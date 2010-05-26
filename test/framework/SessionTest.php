<?
// TODO: make this not required in each test file:
define("SCHEMA", "framework");

// note this also tests a lot of joiner too:
class SessionTest extends \core\TestCase
{
    function setUp()
    {
        parent::setUp();
        core\Session::start();
    }
    
    function tearDown()
    {
        //session_destroy();
    }
    
    function test_messages_with_clear()
    {
        core\Session::set_message(core\Session::NOTICE, "hi");
        $msg= core\Session::get_messages(core\Session::NOTICE);
        $this->assertEquals(1, count($msg));
        $this->assertEquals("hi", $msg[0]);
        
        $msg= core\Session::get_messages(core\Session::NOTICE);
        $this->assertEquals(0, count($msg));
    }
    
    function test_messages_no_clear()
    {
        core\Session::set_message(core\Session::NOTICE, "hi2");
        $msg= core\Session::get_messages(core\Session::NOTICE, false);
        $this->assertEquals(1, count($msg));
        $this->assertEquals("hi2", $msg[0]);
        
        $msg= core\Session::get_messages(core\Session::NOTICE);
        $this->assertEquals(1, count($msg));
    }
    
    function test_clear_messages()
    {
        core\Session::set_message(core\Session::NOTICE, "hi3");
        core\Session::clear_messages(core\Session::NOTICE);
        $this->assertEquals(false, core\Session::has_messages(core\Session::NOTICE));
    }
}