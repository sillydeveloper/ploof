<?

class SessionCacheTest extends \core\TestCase
{    

    protected $key = 'test';
    protected $value = 'value';
    protected $cache;
    
    public function setUp()
    {
        $this->cache= new \plugins\cache\SessionCache();
    }

    public function test_all()
    {
        print('Setting and getting from cache.............................................');
        $this->cache->set_in_cache($this->key, $this->value);
        $check = $this->cache->get_from_cache($this->key);
        $this->assertEquals($this->value, $check, 'set_in_cache() and get_in_cache() failed!');
        print("OK \n");

        print('Adding to cache with a pre-existing key should return false................');
        $check = $this->cache->add_to_cache($this->key, $this->value);
        $this->assertFalse($check, 'add_to_cache() did not return false.');
        print("OK \n");

        print('Deleting key from cache and then getting it should return false............');
        $this->cache->delete_from_cache($this->key);
        $check = $this->cache->get_from_cache($this->key);
        $this->assertFalse($check, 'delete_from_cache() failed!');
        print("OK \n");

        print('Setting in cache, flushing it, and then getting should return false........');
        $this->cache->set_in_cache($this->key, $this->value);
        $this->cache->flush_cache();
        $check = $this->cache->get_from_cache($this->key);
        $this->assertFalse($check, 'flush_cache() failed!');
        print("OK \n");

        print('Setting in cache, replacing it, and then getting it........................');
        $replaced_value = 'replaced';
        $this->cache->set_in_cache($this->key, $this->value);
        $this->cache->replace_in_cache($this->key, $replaced_value);
        $check = $this->cache->get_from_cache($this->key);
        $this->assertEquals($replaced_value, $check, 'replace_in_cache() failed!');
        print("OK \n");
    }

}
?>
