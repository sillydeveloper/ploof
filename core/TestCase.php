<?
// Copyright (c) 2010, ploof development team
// All rights reserved.
// 
// Redistribution and use in source and binary forms, with or without modification, are permitted provided 
// that the following conditions are met:
// 
// Redistributions of source code must retain the above copyright notice, this list of conditions and the 
// following disclaimer. 
// Redistributions in binary form must reproduce the above copyright notice, this list of 
// conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
// The names of its contributors may not be used to endorse or promote products derived from this software without 
// specific prior written permission.
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, 
// INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
// ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, 
// INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE 
// GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
// LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY 
// OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

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
