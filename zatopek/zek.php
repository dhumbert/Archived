<?php
/**
 * 
 * @todo Set default config, as well as a zek::config method, and per-generation override (i.e. zek:generate($obj, array('option1' => 'test', ...))).
 * @todo allow per-request caching AND/OR persistent caching
 * @todo allow to specify when generating what methods/props should or should not be cached
 * @todo allow to get uncached, e.g. $cached_obj->uncached()->method(); - uncached() is just alias for get_base_obj().
 * @author Devin
 *
 */
class zek {
	private $_base_obj;
	private $_cached_properties;
	private $_cached_methods;
	
	public static function generate($obj) {
		return new self($obj);
	}
	
	/**
	 * __construct
	 * Constructor. Private, must use zek::generate.
	 * @param mixed $obj The object to proxy
	 */
	private function __construct($obj) {
		$this->_base_obj = $obj;
		$this->_cached_properties = array();
		$this->_cached_methods = array();
	}
	
	/**
	 * get_base_object
	 * Get the base object.
	 */
	public function get_base_object() {
		return $this->_base_obj;
	}
	
	/**
	 * __call
	 * Magic method to call methods on the base object.
	 * @param string $method
	 * @param array $args
	 * @throws zek_call_exception if the method does not exist in the base object
	 */
	public function __call($method, $args) {
		if ($this->is_method_cached($method, $args)) {
			return $this->get_cached_method($method, $args);
		}
		
		$object = $this->get_base_object();
		
		if (!method_exists($object, $method))
			throw new zek_call_exception("Method '" . $method . "' does not exist in base object");
		
		$value = call_user_func_array(array($object, $method), $args);
		$this->set_cached_method($method, $args, $value);
		return $value;
	}
	
	/**
	 * __get
	 * Magic method to get a property of the base object.
	 * @param string $property
	 * @throws zek_get_exception when the property does not exist in the base object
	 */
	public function __get($property) {
		if ($this->is_property_cached($property)) {
			return $this->get_cached_property($property);
		}
		
		$object = $this->get_base_object();
		
		if (!property_exists($object, $property))
			throw new zek_get_exception("Property '" . $property . "' does not exist in base object");

		$this->set_cached_property($property, $object->$property);
		return $object->$property;
	}
	
	/**
	 * __set
	 * Magic method to set properties of the base object.
	 * @param string $property
	 * @param mixed $value
	 */
	public function __set($property, $value) {
		$object = $this->get_base_object();
		$object->$property = $value;
		$this->set_cached_property($property, $value);
	}
	
	/**
	 * is_property_cached
	 * Checks whether a property has been cached.
	 * @param string $key
	 */
	public function is_property_cached($key) {
		return array_key_exists($key, $this->_cached_properties);
	}
	
	/**
	 * set_cached_property
	 * Cache a property value.
	 * @param string $property
	 * @param mixed $value
	 */
	public function set_cached_property($property, $value) {
		$this->_cached_properties[$property] = $value;
	}
	
	/**
	 * get_cached_property
	 * Get a cached property value.
	 * @param string $property
	 */
	public function get_cached_property($property) {
		return $this->_cached_properties[$property];
	}
	
	/**
	 * is_method_cached
	 * Checks whether a method has been cached.
	 * @param string $key
	 * @param array $args The method arguments
	 */
	public function is_method_cached($key, $args = array()) {
		$arg_hash = $this->_get_method_argument_string($args);
		return array_key_exists($key.$arg_hash, $this->_cached_methods);
	}
	
	/**
	 * set_cached_method
	 * Set a cached method return value.
	 * @param string $method
	 * @param array $args
	 * @param mixed $value
	 */
	public function set_cached_method($method, $args = array(), $value) {
		$arg_hash = $this->_get_method_argument_string($args);
		$this->_cached_methods[$method.$arg_hash] = $value;
	}
	
	/**
	 * get_cached_method
	 * Get the cached return value of a method.
	 * @param string $method
	 * @param array $args
	 */
	public function get_cached_method($method, $args = array()) {
		$arg_hash = $this->_get_method_argument_string($args);
		return $this->_cached_methods[$method.$arg_hash];
	}
	
	/**
	 * _get_method_argument_string
	 * To store method return values, we serialize and hash their arguments.
	 * @param array $args
	 */
	private function _get_method_argument_string($args) {
		return md5(serialize($args));
	}
}

class zek_exception extends Exception {}
class zek_call_exception extends zek_exception {}
class zek_get_exception extends zek_exception {}