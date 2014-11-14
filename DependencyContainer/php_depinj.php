<?php

class Container
{
	private static $_instance;
	private $_registered_components;
	private $_instantiated_components;
	
	private function __construct()
	{
		$this->clear();
	}
	
	public function getInstance()
	{
		if (!self::$_instance)
		{
			self::$_instance = new self();
		}
		
		return self::$_instance;
	}
	
	public function register($key, $concreteImplementation, $interface = null)
	{
		if (!class_exists($concreteImplementation))
		{
			throw new Exception("Class '" . $concreteImplementation . "' does not exist!");
		}
		
		if ($interface != null)
		{
			$ifaces = class_implements($concreteImplementation, true);
			if (!in_array($interface, $ifaces))
			{
				throw new Exception("Class '" . $concreteImplementation . "' does not implement the interface '" . $interface . "'!");
			}
		}
		
		$this->_registered_components[$key] = array(
												'concreteImplementation' 	=> $concreteImplementation,
												'interface'					=> $interface,
												);
	}
	
	public function resolve($key)
	{
		if (!$this->isComponentKeyRegistered($key)) throw new Exception("Component '" . $key . "' is not registered!");

		return $this->getInstantiatedComponent($key);	
	}
	
	public function countRegistered()
	{
		return count($this->_registered_components);
	}
	
	public function clear()
	{
		$this->_registered_components = array();
		$this->_instantiated_components = array();
	}
	
	private function getRegisteredConcreteImplementationName($key)
	{
		return $this->_registered_components[$key]['concreteImplementation'];
	}
	
	private function getRegisteredInterfaceName($key)
	{
		return $this->_registered_components[$key]['interface'];
	}
	
	private function isComponentKeyRegistered($key)
	{
		return array_key_exists($key, $this->_registered_components);
	}
	
	private function isComponentInterfaceRegistered($interface)
	{
		foreach ($this->_registered_components as $key => $array)
		{
			if ($array['interface'] == $interface) return true;
		}
		
		return false;
	}
	
	private function isComponentInstantiated($key)
	{
		return array_key_exists($key, array_keys($this->_instantiated_components));
	}
	
	private function getInstantiatedComponent($key)
	{
		if (!$this->isComponentInstantiated($key))
		{
			$this->instantiateComponent($key);
		}
		
		return $this->_instantiated_components[$key];
	}
	
	private function instantiateComponent($key)
	{
		$concreteImplementation = $this->getRegisteredConcreteImplementationName($key);
		
		$object = null;
		
		if ($this->classHasConstructor($concreteImplementation))
		{
			$ctorParameters = $this->getConstructorArguments($concreteImplementation);
			$reflector = new ReflectionClass($concreteImplementation);
			$object = $reflector->newInstanceArgs($ctorParameters);
		}
		else
		{
			$object = new $concreteImplementation();
		}
		
		$this->_instantiated_components[$key] = $object;
	}
	
	private function getConstructorArguments($className)
	{
		$arguments = array();
		
		$reflector = new ReflectionClass($className);
		$ctor = $reflector->getConstructor();
		
		foreach ($ctor->getParameters() as $parameter)
		{
			//get class name
			$paramType = $parameter->getClass();
			
			//if (no class name or class is not registered) AND param not optional
			if ((!$paramType || !$this->isComponentInterfaceRegistered($paramType->getName())) && !$parameter->isOptional())
			{
				throw new Exception("Not all required params of " . $className . "::__construct() have been registered properly");
			}
			
			$key = $this->getComponentInterfaceKey($paramType->getName());
			
			$arguments[] = $this->getInstantiatedComponent($key);
		}
		
		return $arguments;
	}
	
	private function classHasConstructor($className)
	{
		$reflector = new ReflectionClass($className);
		return !is_null($reflector->getConstructor());
	}
	
	private function getComponentInterfaceKey($interfaceName)
	{
		foreach ($this->_registered_components as $key => $array)
		{
			if ($array['interface'] == $interfaceName) return $key;
		}
		
		return '';
	}
}
?>