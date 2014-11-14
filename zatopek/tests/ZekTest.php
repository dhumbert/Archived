<?php

require_once 'PHPUnit\Framework\TestCase.php';
require_once '../zek.php';

/**
 * zek test case.
 */
class ZekTest extends PHPUnit_Framework_TestCase {
	
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp ();
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		parent::tearDown ();
	}
	
	public function testBasicGeneration() {
		// create an object and generate a proxy for it
		$obj = new TestClass;
		$cached_obj = zek::generate($obj);
		
		$this->assertType('TestClass', $cached_obj->get_base_object());
		$this->assertEquals($obj, $cached_obj->get_base_object());
	}
	
	public function testGeneratedObjMethodCallNoParams() {
		// test that a generated object method call with no params works on the base obj
		$obj = new TestClass;
		$cached_obj = zek::generate($obj);
		
		$this->assertEquals($cached_obj->getValue(), 'No Value!');
	}
	
	public function testGeneratedObjMethodCallWithParams() {
		// test that a generated object method call with params works on the base obj
		$obj = new TestClass;
		$cached_obj = zek::generate($obj);
		
		$this->assertEquals($cached_obj->add(3, 7), 10);
	}
	
	/**
     * @expectedException zek_call_exception
     */
	public function testGeneratedObjNonExistentMethodCall() {
		// test that a method call for a method that does not exist will fail
		$obj = new TestClass;
		$cached_obj = zek::generate($obj);
		$cached_obj->nonExistentMethod();
	}
	
	public function testGeneratedObjGet() {
		// test that getting a property of a generated object works
		$obj = new TestClass;
		$cached_obj = zek::generate($obj);
		
		$this->assertEquals($cached_obj->name, 'Jon');
	}
	
	/**
     * @expectedException zek_get_exception
     */
	public function testGeneratedObjGetNonExistentPropertyFails() {
		// test that trying to get a non-existent property fails
		$obj = new TestClass;
		$cached_obj = zek::generate($obj);
		
		$cached_obj->non_existent_property;
	}
	
	public function testGeneratedObjSet() {
		// test that setting a property of a generated object works
		$obj = new TestClass;
		$cached_obj = zek::generate($obj);
		
		$cached_obj->name = 'Devin';
		$this->assertEquals($cached_obj->name, 'Devin');
	}
	
	public function testRuntimeCachingOfPropertyGet() {
		$obj = new TestClass;
		$cached_obj = zek::generate($obj);
		
		$value = $cached_obj->name;
		$this->assertTrue($cached_obj->is_property_cached('name'));
		$this->assertEquals($cached_obj->get_cached_property('name'), $value);
		$this->assertEquals($cached_obj->name, $value);
	}
	
	public function testRuntimeCachingOfPropertySet() {
		$obj = new TestClass;
		$cached_obj = zek::generate($obj);
		
		$cached_obj->phone = '999';
		$this->assertTrue($cached_obj->is_property_cached('phone'));
		$this->assertEquals($cached_obj->get_cached_property('phone'), '999');
		$this->assertEquals($cached_obj->phone, '999');
	}
	
	public function testRuntimeCachingOfMethodWithNoParams() {
		$obj = new TestClass;
		$cached_obj = zek::generate($obj);
		
		$value = $cached_obj->getValue();
		$this->assertTrue($cached_obj->is_method_cached('getValue'));
		$this->assertEquals($cached_obj->get_cached_method('getValue'), $value);
		$this->assertEquals($cached_obj->getValue(), $value);
	}
	
	public function testRuntimeCachingOfMethodWithOneParam() {
		$obj = new TestClass;
		$cached_obj = zek::generate($obj);
		
		$value = $cached_obj->anEcho('Test');
		$value2 = $cached_obj->anEcho('Test2');
		
		$this->assertTrue($cached_obj->is_method_cached('anEcho', array('Test')));
		$this->assertTrue($cached_obj->is_method_cached('anEcho', array('Test2')));
		$this->assertFalse($cached_obj->is_method_cached('anEcho', array('BlahBlah'))); // anEcho was never called with param 'BlahBlah', so should not be cached
		$this->assertNotEquals($cached_obj->get_cached_method('anEcho', array('Test')), $cached_obj->get_cached_method('anEcho', array('Test2')));
		$this->assertEquals($cached_obj->get_cached_method('anEcho', array('Test')), 'Test');
		$this->assertEquals($cached_obj->get_cached_method('anEcho', array('Test2')), 'Test2');
	}
	
	public function testRuntimeCachingOfMethodWithTwoParams() {
		$obj = new TestClass;
		$cached_obj = zek::generate($obj);
		
		$value = $cached_obj->add(3, 7);
		$value2 = $cached_obj->add(10, 20);
		
		$this->assertTrue($cached_obj->is_method_cached('add', array(3, 7)));
		$this->assertTrue($cached_obj->is_method_cached('add', array(10, 20)));
		$this->assertFalse($cached_obj->is_method_cached('add', array(11230, 2540)));
		$this->assertNotEquals($cached_obj->get_cached_method('add', array(3, 7)), $cached_obj->get_cached_method('add', array(10, 20)));
		$this->assertEquals($cached_obj->get_cached_method('add', array(3, 7)), 10);
		$this->assertEquals($cached_obj->get_cached_method('add', array(10, 20)), 30);
	}
	
	public function testRuntimeCachingOfMethodWithObjectParam() {
		$obj = new TestClass;
		$cached_obj = zek::generate($obj);
		
		$tmp = new stdClass;
		$tmp->important_value = 'URGENT';
		
		$tmp2 = new stdClass;
		$tmp2->important_value = 'NOT URGENT';
		
		$value = $cached_obj->anEcho($tmp);
		$value2 = $cached_obj->anEcho($tmp2);
		
		$this->assertTrue($cached_obj->is_method_cached('anEcho', array($tmp)));
		$this->assertTrue($cached_obj->is_method_cached('anEcho', array($tmp2)));
		$this->assertNotEquals($cached_obj->get_cached_method('anEcho', array($tmp)), $cached_obj->get_cached_method('anEcho', array($tmp2)));
		$this->assertEquals($cached_obj->get_cached_method('anEcho', array($tmp)), $value);
		$this->assertEquals($cached_obj->get_cached_method('anEcho', array($tmp2)), $value2);
	}
}

// this is the class we'll use to test
class TestClass {
	private $_value;
	public $name = 'Jon';
	
	public function __construct($value = 'No Value!') {
		$this->_value = $value;
	}
	
	public function getValue() {
		return $this->_value;
	}
	
	public function anEcho($param) {
		return $param;
	}
	
	public function add($val1, $val2) {
		return $val1 + $val2;
	}
}