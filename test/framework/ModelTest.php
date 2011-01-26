<?
class ModelTest extends core\TestCase
{    
    protected $db;
    protected $session_db;
    
    function setUp()
    {
        $session_db= new plugins\DB\SessionDB(
            array('Model'=>array(
                        array('id'=>1, 'name'=>'Marcy'),
                        array('id'=>2, 'name'=>'Jack')
                    )
                ),
            array('Model'=>array('id'=>'int', 'name'=>'char'))
            );
        $this->db= new core\DB($session_db);
        $this->session_db= $session_db;
        core\Model::set_db($this->db);
    }
    
    function test_set_db()
    {
        $this->assertNotEquals(core\Model::get_db(), null, 'Model::db not being set');
    }
    
    function test_load()
    {
        $model= new core\Model(2);
        $fields= $model->get_fields();
        $this->assertEquals('Jack', $fields['name'], "Can't find Jack!");
    }

    function test_find()
    {
        $results= core\Model::find();
        $this->assertEquals(count($this->session_db->find('Model')), count($results));
    }
    
    function test_requires_a()
    {
        $model= new test\framework\fixtures\Requires();
        $this->assertEquals(true, $model->requires_a('Requirement'));
        $this->assertEquals(false, $model->requires_a('SomethingNotRequired'));
    }
    
    function test_set_field_types()
    {
        $model= new core\Model(1);
        $this->assertEquals($this->session_db->get_columns('Model'), $model->get_field_types());
    }
    
    function test_no_cache_get()
    {
        test\framework\fixtures\NoCache::set_db(new \core\DB(
                new \plugins\DB\SessionDB(
                    array('NoCache'=>array(
                                array('id'=>1, 'name'=>'Marcy'),
                                array('id'=>2, 'name'=>'Jack')
                            )
                        ),
                    array('NoCache'=>array('id'=>'int', 'name'=>'char'))
                )
            )
        );
        
        $model= new test\framework\fixtures\NoCache(1);
        $model->name= 'bar';
        $this->assertEquals('bar', $model->name);
    }
    
    /*
        // act like an incoming form:
        $a= array('belongsto'=>array('name'=>array(0=>'f', 1=>'g')));
        
        $hm= new hasmany(1);
        $hm->save('belongsto', $a);
        $this->assertEquals(2, count($hm->belongsto->find()), 'Wrong count for hasmany save() (existing object)');
        $hm->refresh("belongsto");
        $this->assertEquals(2, count($hm->belongsto->find()), 'Wrong count for hasmany save() (existing object) after refresh');
        
        $hm= new hasmany();
        $hm->store();
        $hm->save('belongsto', $a);
        $this->assertEquals(2, count($hm->belongsto->find()), 'Wrong count for hasmany save() (new object)');
        $hm->refresh('belongsto');
        $this->assertEquals(2, count($hm->belongsto->find()), 'Wrong count for hasmany save() (new object) after refresh');


    function test_delete()
    {
        $hm= new hasmany(1);
        $hm->belongsto->delete(array(1));
        $hm->refresh("belongsto");
        $this->assertEquals(1, count($hm->belongsto->find()), "Wrong count after delete with array of keys");
        $hm->belongsto->delete();
        $hm->refresh("belongsto");
        $this->assertEquals(0, count($hm->belongsto->find()), "Wrong count after delete all");
    }
    
    function test_find()
    {
        $hm= new hasmany(1);
        $this->assertEquals("a", array_pop($hm->belongsto->find(array("name"=>"a")))->name, "Couldn't find");
    }
    
    function test_add_array()
    {
        $a= array("name"=>"d");
        $hm= new hasmany(1);
        $hm->belongsto->add_array($a);
        
        $this->assertEquals(array_pop($hm->belongsto->find(array("name"=>"d")))->id, 3, "Could not find the object after add_object");
        $hm->refresh('belongsto');
        $this->assertEquals(array_pop($hm->belongsto->find(array("name"=>"d")))->id, 3, "Could not find the object after refresh");
    }
    
    function test_add_object()
    {
        $bt= new belongsto();
        $bt->name= "c";
        $bt->store();
        $hm= new hasmany(1);
        $hm->belongsto->add_object($bt);
        
        $this->assertEquals(array_pop($hm->belongsto->find(array("name"=>"c")))->id, 3, "Could not find the object after add_object");
        $hm->refresh('belongsto');
        $this->assertEquals(array_pop($hm->belongsto->find(array("id"=>3)))->id, 3, "Could not find the object after refresh");
    }
    
    function test_get_datetime()
    {
        $bt= new belongsto(1);
        $this->assertEquals('05/19/2010', $bt->dt, "Date format not called on get");
    }
    
    function test_get_fields()
    {
        $hm= new hasmany();
        $f= $hm->get_fields();
        $this->assertEquals(array("id"=>null), $f, "get_fields not returning correct array format");
    }
    
    function test_joiner_belongsto_get()
    {
        $hm= new belongsto(1);
        $this->assertEquals("hasmany", get_class($hm->hasmany), "Wrong object returned for belongsto->joiner->get()");
    }
    
    function test_joiner_hasmany()
    {
        $hm= new hasmany(1);
        $this->assertEquals(2, count($hm->belongsto->find()), "Wrong count coming back for has_many from joiner");
    }
    
    function test_construct()
    {
        $hm= new hasmany(1);
        $this->assertEquals(1, $hm->id, "Could not load a model out of the database");
    }
    
    function test_construct_new()
    {
        $hm= new hasmany();
        $this->assertEquals($hm->id, null, "Could not create and save a new model");
    }
    
    function test_store()
    {
        $bt= new belongsto();
        $bt->store();
        $this->assertEquals(3, $bt->id, "Could not store a new model");
    }*/
    
    static function classname()
    {
        return __CLASS__;
    }
}

?>