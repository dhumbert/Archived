<?php

require_once("../simpletest/autorun.php");

function __autoload($class)
{
	require_once($class . '.class.php');
}

class ReflectTest extends UnitTestCase
{
	/**
	 * Test correct creation of proxy object.
	 */
	public function testCreationOfProxyObject()
	{
		$product = new Product();
		$pProxy = new Proxifier($product);
	}
	
	/**
	 * Test creating a proxy object without a valid object. Should throw exception.
	 */
	public function testCreationOfProxyWithNonObject()
	{
		try
		{
			$pProxy = new Proxifier("test");
			$this->fail();
		}
		catch (Exception $e)
		{
			$this->pass();
		}
	}
	
	/**
	 * Test accessing base class properties through proxy object.
	 */
	public function testProxyObjectAccessBaseObjectProperty()
	{
		$product = new Product();
		$pProxy = new Proxifier($product);
		
		$this->assertEqual($pProxy->name, $product->name);
	}
	
	/**
	 * Test calling a non-existent method on proxy object. Should throw exception.
	 */
	public function testProxyObjectCallNonExistententMethod()
	{
		$product = new Product();
		$pProxy = new Proxifier($product);
		
		try
		{
			$pProxy->nonExistentMethod123XYZ();
			$this->fail();
		}
		catch (Exception $e)
		{
			$this->pass();
		}
	}
	
	/**
	 * Test calling base object method succesfully.
	 */
	public function testProxyObjectBaseMethodCall()
	{
		$product = new Product();
		$pProxy = new Proxifier($product);
		
		ob_start();
		$pProxy->edit(1);
		$result = ob_get_clean();
		
		$this->assertEqual($result, "Hey! You called Product::edit with ID 1");
	}
	
	/**
	 * Test that base methods still work without proxy class.
	 */
	public function testBaseMethodCall()
	{
		$product = new Product();
		$pProxy = new Proxifier($product);
		
		ob_start();
		$pProxy->edit(1);
		$result = ob_get_clean();
		
		$this->assertEqual($result, "Hey! You called Product::edit with ID 1");
	}
	
	/**
	 * Ensure that when a proxy object is created it is properly logged.
	 */
	public function testLoggingCreationOfProxyObject()
	{
		$product = new Product();
		
		$filename = '/tmp/log.txt';
		if (file_exists($filename)) unlink($filename); // delete if file exists
		
		$logger = new FileLogger($filename);
		
		$pProxy = new Proxifier($product, $logger);
		
		$log_result = trim(file_get_contents($filename));
		
		$this->assertEqual($log_result, "Proxy object created for class 'Product'.");
	}
	
	/**
	 * Ensure that when a property of the proxy object is created it is properly logged.
	 */
	public function testLoggingOfPropertyAccess()
	{
		$product = new Product();
		
		$filename = '/tmp/log.txt';
		if (file_exists($filename)) unlink($filename); // delete if file exists
		
		$logger = new FileLogger($filename);
		
		$pProxy = new Proxifier($product, $logger);
		$tmp = $pProxy->name;
		
		$log_result = trim(file_get_contents($filename));
		
		$this->assertEqual($log_result, "Proxy object created for class 'Product'." . PHP_EOL . "Product::\$name accessed.");
	}
	
	/**
	 * Ensure that when a method of the proxy object is created it is properly logged.
	 */
	public function testLoggingOfMethodAccess()
	{
		$product = new Product();
		
		$filename = '/tmp/log.txt';
		if (file_exists($filename)) unlink($filename); // delete if file exists
		
		$logger = new FileLogger($filename);
		
		$pProxy = new Proxifier($product, $logger);
		$pProxy->edit(1);
		
		$log_result = trim(file_get_contents($filename));
		
		$this->assertEqual($log_result, "Proxy object created for class 'Product'." . PHP_EOL . "Product::edit called with parameters: {1}.");
	}
}
