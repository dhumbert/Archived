<?php defined('BASE_DIR') OR die('No direct access allowed.');

abstract class data_store
{
	protected $_fields = array();
	protected $_options = array();
	protected $_identifier = NULL;
	
	public static function factory($type, $options = array())
	{
		$class_name = strtolower($type) . '_store';
		if (class_exists($class_name))
		{
			$obj = new $class_name;
			// we can set some options here for convenience
			foreach ($options as $name => $value)
			{
				$obj->set_option($name, $value);
			}
			return $obj;
		}
		else
		{
			throw new data_store_exception('Datastore type not valid.');
		}
	}
	
	/**
	 * Set identifier. This is used by data store manager to reference seperate instances
	 * of the same data store class.
	 *
	 * @param string value The identifier.
	 */
	public function set_identifier($value)
	{
		$this->_identifier = $value;
	}
	
	/**
	 * Get the identifier.
	 */
	public function get_identifier()
	{
		return $this->_identifier;
	}
	
	/**
	 * Add a field to the data store.
	 *
	 * @param string name The name of the field.
	 * @param string value The value of the field.
	 */
	public function add_field($name, $value)
	{
		$key = $name;
		if (!array_key_exists($key, $this->_fields))
		{
			if (is_array($value))
			{
				$value = implode('; ', $value);
			}
			
			$this->_fields[$key] = $value;
		}
		else
		{
			// fail silently - this is so that the data store manager cannot override values.
			return;
		}
	}
	
	/**
	 * Add an array of fields to the data store.
	 *
	 * @param array fields The associate array of values to add.
	 */
	public function add_fields(array $fields)
	{
		foreach ($fields as $name => $value)
		{
			$this->add_field($name, $value);
		}
	}
	
	/**
	 * Get the value of a field.
	 *
	 * @param string name The name of the field.
	 */
	public function get_field($name)
	{
		return $this->_fields[$name];
	}
	
	/**
	 * Count the fields.
	 */
	public function count_fields()
	{
		return count($this->_fields);
	}
	
	/**
	 * Set an option. This is used for options that are store-specific (e.g. table names for SQL stores).
	 *
	 * @param string name The name of the option.
	 * @param string value The value of the option.
	 */
	public function set_option($name, $value)
	{
		$this->_options[$name] = $value;
	}
	
	/**
	 * Get the value of an option.
	 *
	 * @param string name The name of the option.
	 */
	public function get_option($name)
	{
		return $this->_options[$name];
	}
	
	/**
	 * Allow child classes to validate that they are ready to save.
	 */
	protected function validate()
	{
	}
	
	/**
	 * Save to the data store. This will be left to child classes to implement.
	 */
	abstract public function save();
}

class data_store_exception extends Exception {}