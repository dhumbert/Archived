<?php
require_once("/home/devin/libs/simpletest/autorun.php");

class ContainerTest extends UnitTestCase
{
	public function testRegisteringComponentThatDoesntExist()
	{
		$container = Container::getInstance();
		$container->clear();
		
		try
		{
			$container->register('testInvalidClass', 'madeUpClass');
			$this->fail();
		}
		catch(Exception $e)
		{
			$this->assertEqual($container->countRegistered(), 0);
		}
	}
	
	public function testRegisteringComponentThatDoesntImplementInterface()
	{
		$container = Container::getInstance();
		$container->clear();
		
		try
		{
			$container->register('testInvalidImplementation', 'ExampleClass', 'INullInterface');
			$this->fail();
		}
		catch(Exception $e)
		{
			$this->assertEqual($container->countRegistered(), 0);
		}
	}
	
	public function testResolvingComponents()
	{
		$container = Container::getInstance();
		$container->clear();
		
		$container->register('testDependentClass', 'ExampleClass');
		$container->register('testDependency', 'ImplementInterface', 'ITestInterfaceOne');
		
		$obj = $container->resolve('testDependentClass');
		$this->assertIsA($obj, 'ExampleClass');
		$this->assertEqual('TOM', $obj->action());
	}
}


class ExampleClass
{
	private $_interfaceOne;
	
	public function __construct(ITestInterfaceOne $interfaceOne)
	{
		$this->_interfaceOne = $interfaceOne;
	}
	
	public function action()
	{
		return $this->_interfaceOne->getName();
	}
}

interface ITestInterfaceOne
{
	public function getName();
}

class ImplementInterface implements ITestInterfaceOne
{
	public function getName()
	{
		return 'TOM';
	}
}