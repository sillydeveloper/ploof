<?

class MysqliConnectorTest extends \core\TestCase
{    
    public $conn;
    
    function setUp()
    {
        $this->conn= new \plugins\DB\MysqliConnector('root', null, 'ione', '127.0.0.1');
    }
        
    function test_table_columns()
    {
        $this->assertEquals(array('id'=>'int(11)'), $this->conn->get_table_columns('Customer'));   
    }
    
    function test_store_new_row()
    {
        $this->conn->query('truncate table Phone');
        $this->conn->store_row('Phone', array('Customer_id'=>1, 'area_code'=>'503'));
        $row= $this->conn->find_rows('Phone', array('Customer_id'=>1));
        $this->assertEquals($row[0]['id'], 1);
        
        $this->conn->store_row('Phone', array('id'=>1, 'Customer_id'=>2, 'area_code'=>'577'));
        $row= $this->conn->find_rows('Phone', array('id'=>1));
        $this->assertEquals($row[0]['Customer_id'], 2);
    }

}