<?
namespace core;
require_once 'PHPUnit/Framework.php';

class TestCase extends \PHPUnit_Framework_TestCase
{    
    function setUp()
    {
        if (classname_only(static::classname()) != "TestCase")
        {
            // let everyone know that we are in a test environment:
            define('IN_UNIT_TESTING', 1);
        
            DB::query("drop database ".TEST_DATABASE_NAME);
            DB::query("create database ".TEST_DATABASE_NAME);
        
            // TODO: Make this work with DB::query() properly... see trial run commented out below...
            $sql_fixture= "test/".SCHEMA."/fixtures/".classname_only(static::classname()).".sql";
            if (file_exists($sql_fixture))
            {
                $catter= "cat $sql_fixture | mysql -u ".TEST_DATABASE_USER." --password=".TEST_DATABASE_PASS." -h ".TEST_DATABASE_HOST." ".TEST_DATABASE_NAME;
        
                `$catter`;
            }
        
            /*
            $query= file_get_contents("resource/schemas/".SCHEMA);
            foreach(explode(";", $query) as $v)
            {
                `cat `
                DB::query($v);
            }    
            */
        
            // wipe and generate classes:
            `rm -f test/temp/*`;
            Model::generate_models();
        }
    }
    
    function test_nothing()
    {
        // this is here to discourage warnings out of PHUNIT.
    }
    
    static function classname()
    {
        return __CLASS__;
    }
}
?>