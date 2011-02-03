<?
set_include_path(get_include_path().PATH_SEPARATOR."test/framework/fixtures");

class ModelTest extends core\TestCase
{    
    protected $repository;
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
        $this->repository= new core\Repository($session_db);
        $this->session_db= $session_db;
        core\Model::set_repository($this->repository);
    }
    
    function test_set_repository()
    {
        $this->assertNotEquals(core\Model::get_repository(), null, 'Model::db not being set');
    }
    
    function test_load()
    {
        $model= new core\Model(2);
        $fields= $model->get_fields();
        $this->assertEquals('Jack', $fields['name'], "Can't find Jack!");
    }

    function test_find()
    {
        $session_db= new plugins\DB\SessionDB(
            array('NoCache'=>array(
                        array('id'=>1, 'name'=>'Marcy'),
                        array('id'=>2, 'name'=>'Jack')
                    )
                ),
            array('NoCache'=>array('id'=>'int', 'name'=>'char'))
            );
        $repository= new core\Repository($session_db);
        NoCache::set_repository($repository);
        $results= NoCache::find();
        $this->assertEquals(count($this->session_db->find_rows('NoCache')), count($results));
        
        $results= NoCache::find(array('id'=>1));
        $this->assertEquals(count($this->session_db->find_rows('NoCache', array('id'=>1))), count($results));
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
        $this->assertEquals($this->session_db->get_table_columns('Model'), $model->get_field_types());
    }
    
    function test_no_cache_set_and_get()
    {
        $repository_check= new \core\Repository(
                new \plugins\DB\SessionDB(
                    array('NoCache'=>array(
                                array('id'=>1, 'name'=>'Marcy'),
                                array('id'=>2, 'name'=>'Jack')
                            )
                        ),
                    array('NoCache'=>array('id'=>'int', 'name'=>'char'))
                )
        );
        NoCache::set_repository($repository_check);
        
        $model= new NoCache(1);
        $model->name= 'bar';
        $data= $repository_check->load_row('NoCache', 1);
        $this->assertEquals('bar', $data['name']);
    }
    
    function test_foreign_get()
    {
        $database= new \plugins\DB\SessionDB(
            array(
                'HasMany'=>array(
                        array('id'=>1, 'name'=>'Marcy'),
                        array('id'=>2, 'name'=>'Jack')
                ),
                'BelongsTo'=>array(
                        array('id'=>1, 'name'=>'Tom', 'HasMany_id'=>1),
                        array('id'=>2, 'name'=>'Craig', 'HasMany_id'=>1)
                )
            ),
            array(
                'HasMany'=>array('id'=>'int', 'name'=>'char'),
                'BelongsTo'=>array('id'=>'int', 'name'=>'char', 'HasMany_id'=>'int'),
            )
        );
        $repository_check= new \core\Repository($database);
        HasMany::set_repository($repository_check);
        BelongsTo::set_repository($repository_check);
        
        $model= new HasMany(1);
        $belongs_to= $model->BelongsTo->find();
        $this->assertEquals(2, count($belongs_to));

        $model= new BelongsTo(1);
        $has_many= $model->HasMany;
        $this->assertEquals(1, $has_many->id);
    }
    
    function test_date_get()
    {
        $database= new \plugins\DB\SessionDB(
            array(
                'HasMany'=>array(
                        array('id'=>1, 'dtime'=>'2010-01-01 10:00:00')
                    )
                ),
            array(
                'HasMany'=>array('id'=>'int', 'dtime'=>'date'),
            )
        );
        $repository_check= new \core\Repository($database);
        HasMany::set_repository($repository_check);
        $model= new HasMany(1);
        $this->assertEquals('01/01/2010', $model->dtime);
    }
    
    function test_delete()
    {
        $m= new \core\Model(1);
        $m->delete();
        $this->assertEquals(false, \core\Model::get_repository()->load_row('Model', 1));
    }
    
    function test_store_existing()
    {
        $m= new \core\Model(1);
        $m->name= 'kate';
        $m->store();
        $data= \core\Model::get_repository()->load_row('Model', 1);
        $this->assertEquals('kate', $data['name']);
    }
    
    function test_store_new()
    {
        $m= new \core\Model();
        $m->name= 'marty';
        $m->store();
        $data= \core\Model::get_repository()->load_row('Model', 3);
        $this->assertEquals('marty', $data['name']);
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