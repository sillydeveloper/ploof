<?
namespace core;
require_once 'PHPUnit/Framework.php';

class TestCase extends \PHPUnit_Framework_TestCase
{   
    function __construct()
    {
        // this closure seems to cause problems for PHPUnit's serialize:
        unset($GLOBALS['error_function']);
        parent::__construct();
    }

    function setUp()
    {
        if (false) // turn off fixtures for now
        {
            if (classname_only(static::classname()) != "TestCase")
            {       
                // TODO: Make this work with DB::query() properly... see trial run commented out below...
                $sql_fixture= "test/".SCHEMA."/fixtures/".classname_only(static::classname()).".sql";
                if (file_exists($sql_fixture))
                {
                    $catter= "cat $sql_fixture | mysql -u ".TEST_DATABASE_USER." --password=".TEST_DATABASE_PASS." -h ".TEST_DATABASE_HOST." ".TEST_DATABASE_NAME;
                    `$catter`;
                }
            
                if (SCHEMA == 'framework')
                {
                    // wipe and generate classes:
                    `rm -f test/temp/*`;
                    Model::generate_models();
                }
            }
        }
    }


   /* 
    function test_nothing()
    {
        // this is here to discourage warnings out of PHUNIT.
    }
    */
    
    static function classname()
    {
        return __CLASS__;
    }
}
?>
