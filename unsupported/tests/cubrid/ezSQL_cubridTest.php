<?php
require_once('ez_sql_loader.php');

require 'vendor/autoload.php';
use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2018-03-08 at 03:16:41.
 */
class ezSQL_cubridTest extends TestCase
{
        /**
     * constant string user name 
     */
    const TEST_DB_USER = 'ez_test';
    
    /**
     * constant string password 
     */
    const TEST_DB_PASSWORD = 'ezTest';
    
    /**
     * constant database name 
     */
    const TEST_DB_NAME = 'ez_test';
    
    /**
     * constant database host
     */
    const TEST_DB_HOST = 'localhost';
    
    /**
     * constant database port 
     */
    const TEST_DB_PORT = 33000;
    
    /**
     * @var ezSQL_cubrid
     */
    protected $object;
    private $errors;
 
    function errorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
        $this->errors[] = compact("errno", "errstr", "errfile",
            "errline", "errcontext");
    }

    function assertError($errstr, $errno) {
        foreach ($this->errors as $error) {
            if ($error["errstr"] === $errstr
                && $error["errno"] === $errno) {
                return;
            }
        }
        $this->fail("Error with level " . $errno .
            " and message '" . $errstr . "' not found in ", 
            var_export($this->errors, TRUE));
    }   

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        if (!extension_loaded('cubrid')) {
            $this->markTestSkipped(
              'The cubrid Lib is not available.'
            );
        }
        $this->object = new ezSQL_cubrid(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->object->query('DROP TABLE IF EXISTS unit_test');
        $this->object = null;
    }

    /**
     * @covers ezSQL_cubrid::quick_connect
     */
    public function testQuick_connect() {
        $this->assertTrue($this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME));
    } // testQuick_connect

    /**
     * @covers ezSQL_cubrid::connect
     * 
     */
    public function testConnect() {        
        $this->errors = array();
        set_error_handler(array($this, 'errorHandler')); 
         
        $this->assertFalse($this->object->connect('',''));  
        $this->assertFalse($this->object->connect('self::TEST_DB_USER', 'self::TEST_DB_PASSWORD',' self::TEST_DB_NAME'));  
        
        $this->assertTrue($this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT));
    } // testConnect
    
    /**
     * @covers ezSQL_cubrid::escape
     */
    public function testEscape() {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);
        $result = $this->object->escape("This is'nt escaped.");

        $this->assertEquals("This is''nt escaped.", $result);
    } // testEscape

    /**
     * @covers ezSQL_cubrid::sysdate
     */
    public function testSysdate() {
        $this->assertEquals('NOW()', $this->object->sysdate());
    } // testSysdate
    
    /**
     * @covers ezSQLcore::get_var
     */
    public function testGet_var() { 
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME); 
        // Demo of getting a single variable from the db
        // (and using abstracted function sysdate)   
        $current_time = $this->object->get_var("SELECT " . $this->object->sysdate());
        $this->assertNotNull($current_time);
    } // testGet_var

    /**
     * @covers ezSQLcore::get_results
     */
    public function testGet_results() {           
    $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);    
    
	// Get list of tables from current database..
	$my_tables = $this->object->get_results("SHOW TABLES",ARRAY_N);
    $this->assertNotNull($my_tables);
    
	// Loop through each row of results..
	foreach ( $my_tables as $table )
        {
            // Get results of DESC table..
            $this->assertNotNull($this->object->get_results("DESC $table[0]"));
        }
    } // testGet_results
    
    /**
     * @covers ezSQL_cubrid::query
     */
    public function testQuery() {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME); 
        $this->assertNotFalse($this->object->query('CREATE TABLE unit_test(id int, test_key varchar(50), PRIMARY KEY (ID))'));
        $this->assertEquals($this->object->query('INSERT INTO unit_test(id, test_key) VALUES(1, \'test 1\')'), 1);
        
        $this->object->dbh = null;
        $this->assertEquals($this->object->query('INSERT INTO unit_test(id, test_key) VALUES(2, \'test 2\')'),1);
        $this->object->disconnect();
        $this->assertFalse($this->object->query('INSERT INTO unit_test(id, test_key) VALUES(3, \'test 3\')'));    
    } // testQuery

    /**
     * @covers ezSQLcore::insert
     */
    public function testInsert()
    {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);  
        $this->object->query('DROP TABLE IF EXISTS unit_test');  
        $this->object->query('CREATE TABLE unit_test(id int, test_key varchar(50), PRIMARY KEY (ID))');
        
        $result = $this->object->insert('unit_test', array('id'=>'1', 'test_key'=>'test 1' ));
        $this->assertEquals(0, $result);
    }
       
    /**
     * @covers ezSQLcore::update
     */
    public function testUpdate()
    {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);    
        $this->object->query('CREATE TABLE unit_test(id int, test_key varchar(50), PRIMARY KEY (ID))');
        $this->object->insert('unit_test', array('id'=>'1', 'test_key'=>'test 1' ));
        $this->object->insert('unit_test', array('id'=>'2', 'test_key'=>'test 2' ));
        $this->object->insert('unit_test', array('id'=>'3', 'test_key'=>'test 3' ));
        $unit_test['test_key'] = 'testing';
        $where="id  =  1";
        $this->assertEquals($this->object->update('unit_test', $unit_test, $where), 1);
        $this->assertEquals($this->object->update('unit_test', $unit_test, eq('test_key','test 3', _AND), eq('id','3')), 1);
        $this->assertEquals($this->object->update('unit_test', $unit_test, "id = 4"), 0);
        $this->assertEquals($this->object->update('unit_test', $unit_test, "test_key  =  test 2  and", "id  =  2"), 1);
    }
    
    /**
     * @covers ezSQLcore::delete
     */
    public function testDelete()
    {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);    
        $this->object->query('CREATE TABLE unit_test(id int, test_key varchar(50), PRIMARY KEY (ID))');
        $unit_test['id'] = '1';
        $unit_test['test_key'] = 'test 1';
        $this->object->insert('unit_test', $unit_test );
        $unit_test['id'] = '2';
        $unit_test['test_key'] = 'test 2';
        $this->object->insert('unit_test', $unit_test );
        $unit_test['id'] = '3';
        $unit_test['test_key'] = 'test 3';
        $this->object->insert('unit_test', $unit_test );
        $where='1';
        $this->assertEquals($this->object->delete('unit_test', array('id','=','1')), 1);
        $this->assertEquals($this->object->delete('unit_test', 
            array('test_key','=',$unit_test['test_key'],'and'),
            array('id','=','3')), 1);
        $this->assertEquals($this->object->delete('unit_test', array('test_key','=',$where)), 0);
        $where="id  =  2";
        $this->assertEquals($this->object->delete('unit_test', $where), 1);
    }  

    /**
     * @covers ezSQLcore::selecting
     */
    public function testSelecting()
    {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);    
        $this->object->query('CREATE TABLE unit_test(id int, test_key varchar(50), PRIMARY KEY (ID))');
        $this->object->insert('unit_test', array('id'=>'1', 'test_key'=>'testing 1' ));
        $this->object->insert('unit_test', array('id'=>'2', 'test_key'=>'testing 2' ));
        $this->object->insert('unit_test', array('id'=>'3', 'test_key'=>'testing 3' ));
        
        $result = $this->object->selecting('unit_test');
        $i = 1;
        foreach ($result as $row) {
            $this->assertEquals($i, $row->id);
            $this->assertEquals('testing ' . $i, $row->test_key);
            ++$i;
        }
        
        $where=eq('test_key','testing 2');
        $result = $this->object->selecting('unit_test', 'id', $where);
        foreach ($result as $row) {
            $this->assertEquals(2, $row->id);
        }
        
        $result = $this->object->selecting('unit_test', 'test_key', eq( 'id','3' ));
        foreach ($result as $row) {
            $this->assertEquals('testing 3', $row->test_key);
        }
        
        $result = $this->object->selecting('unit_test', array ('test_key'), "id  =  1");
        foreach ($result as $row) {
            $this->assertEquals('testing 1', $row->test_key);
        }
    } 
	
    /**
     * @covers ezSQL_cubrid::disconnect
     */
    public function testDisconnect() {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);    
        $this->object->disconnect();
        $this->assertFalse($this->object->isConnected());
    } // testDisconnect
    
    /**
     * @covers ezSQL_cubrid::__construct
     */
    public function test__Construct() {   
        $this->errors = array();
        set_error_handler(array($this, 'errorHandler'));    
        
        $cubrid = $this->getMockBuilder(ezSQL_cubrid::class)
        ->setMethods(null)
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->assertNull($cubrid->__construct());  
    } 
}
