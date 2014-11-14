<?php
/**
 * Proxifier class
 * 
 * This class creates a proxy object that logs all method calls to the base object.
 * 
 * @version 1.0
 * @author Devin Humbert <devin.humbert@gmail.com>
 */
class Proxifier
{
	/**
	 * The instance of the base object.
	 * 
	 * @access private
	 * @var object
	 */
	private $_base_object_instance;
	
	/**
	 * Logger.
	 *
	 * @access private
	 * @var object
	 */
	private $_logger;
	
	
	/**
	 * Class constructor.
	 * @access public
	 * @param object $o
	 * @param object $logger
	 */
	public function __construct($o, LogProvider $logger = null)
	{
		if (!is_object($o))
			throw new ProxifierException("Cannot create proxy object with non-object");
		
		$this->set_base_object_instance($o);
		$this->set_logger($logger);
		
		$this->logMessage("Proxy object created for class '" . get_class($o) . "'.");
	}
	
	/**
	 * Set the base object instance.
	 * 
	 * @access private
	 * @param object $o
	 */ 
	private function set_base_object_instance($o)
	{
		$this->_base_object_instance = $o;
	}
	
	/**
	 * Get the base object instance.
	 * 
	 * @access private
	 * @return object
	 */
	private function get_base_object_instance()
	{
		return $this->_base_object_instance;
	}
	
	/**
	 * Set the logger.
	 * 
	 * @access private
	 * @param object $logger
	 */
	private function set_logger($logger)
	{
		$this->_logger = $logger;
	}
	
	/**
	 * Send a message to the logger.
	 * 
	 * @access private
	 * @param string $message
	 */
	private function logMessage($message)
	{
		if ($this->_logger != null)
			$this->_logger->logMessage($message);
	}
	
	/**
	 * Magic method to call base object methods and log the call.
	 * 
	 * @access public
	 * @param string $method
	 * @param array $args
	 */
	public function __call($method, $args)
	{
		$object = $this->get_base_object_instance();
		
		if (!method_exists($object, $method))
			throw new ProxifierException("Method '" . $method . "' does not exist in base object!");
		
		$this->logMessage(get_class($object) . '::' . $method . ' called with parameters: {' . implode(', ', $args) . '}.');
		
		return call_user_func_array(array($object, $method), $args);
	}
	
	/**
	 * Magic method to access base object properties and log the access.
	 * 
	 * @access public
	 * @param string $property
	 */
	public function __get($property)
	{
		$object = $this->get_base_object_instance();
		
		if (!property_exists($object, $property))
			throw new ProxifierException("Property '" . $property . "' does not exist in base object!");
		
		$this->logMessage(get_class($object) . '::$' . $property . ' accessed.');
		
		return $object->$property;
	}
}

/**
 * ProxifierException
 * 
 * A custom exception type for Proxifier.
 */
class ProxifierException extends Exception {}
