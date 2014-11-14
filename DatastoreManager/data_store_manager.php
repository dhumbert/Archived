<?php defined('BASE_DIR') OR die('No direct access allowed.');

class data_store_manager
{
	private $_attached_stores = array();
	private $_fields = array();
	private $_errors = array();
	private $_aliases = array('store_type_aliases' => array(), 'store_id_aliases' => array());
	private $_bindings = array();
	private $_required_fields = array();
	
	const BIND_POST = '_POST';
	const BIND_GET = '_GET';
	const BIND_SERVER = '_SERVER';
	const BIND_COOKIE = '_COOKIE';
	
	/**
	 * Ctor.
	 */
	public function __construct()
	{
	}
	
	/**
	 * Attach a data store to the manager.
	 *
	 * @param data_store store The data store to add (by reference).
	 */
	public function attach_store(data_store &$store)
	{
		$id = $this->generate_store_identifier($store);
		$store->set_identifier($id);
		
		$this->_attached_stores[$id] = $store;
		return $id;
	}
	
	/**
	 * Count the number of attached data stores.
	 */
	public function count_attached_stores()
	{
		return count($this->_attached_stores);
	}
	
	public function generate_store_identifier($store)
	{
		return sha1(rand() . '' . microtime() . get_class($store));
	}
	
	/**
	 * Get error array.
	 */
	public function get_errors()
	{
		return $this->_errors;
	}
	
	/**
	 * Add a global field.
	 *
	 * @param string name The name of the field.
	 * @param mixed value The value of the field - can be an array for binding.
	 * @param array aliases Aliases for the field.
	 */
	public function add_field($name, $value, $aliases = array())
	{
		//$name = strtoupper($name);
		$this->_fields[$name] = $value;
		
		// check for variable binding
		if (is_array($value))
		{
			$this->bind_field($name, $value);
		}
		
		// check for aliases
		if (count($aliases) > 0)
		{
			foreach ($aliases as $alias)
			{
				if (array_key_exists('store_id', $alias))
				{
					// add an alias for a specific store - this will override store type aliases
					$this->add_store_id_alias($alias['store_id'], $name, $alias['alias']);
				}
				elseif (array_key_exists('store_type', $alias))
				{
					// add an alias for a store type
					$this->add_store_type_alias($alias['store_type'], $name, $alias['alias']);
				}
			}
		}
	}
	
	/**
	 * Shortcut for adding a bound field.
	 *
	 * @param string name The name of the field.
	 * @param array aliases Field aliases.
	 * @param const method The HTTP method (defaults to POST).
	 */
	public function add_bound_field($name, $aliases = array(), $method = self::BIND_POST)
	{
		$this->add_field($name, array($method, $name), $aliases);
	}
	
	/**
	 * Add multiple fields.
	 *
	 * @param array fields The fields to add.
	 */
	public function add_fields($fields)
	{
		foreach ($fields as $field)
		{
			// if aliases are set for the field, pass them along as well.
			if (array_key_exists('aliases', $field))
			{
				$aliases = $field['aliases'];
			}
			else
			{
				$aliases = array();
			}
			
			$this->add_field($field['name'], $field['value'], $aliases);
		}
	}
	
	/**
	 * Add an alias to a field based on the store id. This will override any aliases for
	 * this field set by store_type.
	 *
	 * @param string field The name of the field to add an alias to.
	 * @param string store The store identifier of the store that the alias is for.
	 * @param string alias The alias for the field.
	 */
	public function add_store_id_alias($store_id, $field, $alias)
	{
		//$field = strtoupper($field);
		
		$this->_aliases['store_id_aliases'][$field][] = array(
			'store_id' => $store_id,
			'alias' => $alias,
		);
	}
	
	/**
	 * Add an alias to a field based on the store type.
	 *
	 * @param string field The name of the field to add an alias to.
	 * @param string store The store identifier of the store that the alias is for.
	 * @param string alias The alias for the field.
	 */
	public function add_store_type_alias($store_type, $field, $alias)
	{
		//$field = strtoupper($field);
		
		$this->_aliases['store_type_aliases'][$field][] = array(
			'store_type' => $store_type,
			'alias' => $alias,
		);
	}
	
	public function get_field_name($field, $store_id, $store_type)
	{
		//$field = strtoupper($field);
		
		// check for aliases for this field
		if (count($this->_aliases) > 0
			|| array_key_exists($field, $this->_aliases['store_id_aliases']) 
			|| array_key_exists($field, $this->_aliases['store_type_aliases'])
		)
		{
			// first we check for store_id aliases, as these take priority
			if (array_key_exists($field, $this->_aliases['store_id_aliases']))
			{
				foreach ($this->_aliases['store_id_aliases'][$field] as $alias)
				{
					// if one was found, return it
					$desired_store_id = $alias['store_id'];
					$alias = $alias['alias'];
					if ($store_id == $desired_store_id)
					{
						return $alias;
					}
				}
			}
			elseif (array_key_exists($field, $this->_aliases['store_type_aliases']))
			{
				// otherwise, let's look for store type aliases
				foreach ($this->_aliases['store_type_aliases'][$field] as $alias)
				{
					// if one was found, return it
					$desired_store_type = $alias['store_type'];
					$alias = $alias['alias'];
					if ($store_type == $desired_store_type)
					{
						return $alias;
					}
				}
			}
		}
		
		// no aliases, jsut return the field name
		return $field;
	}
	
	/**
	 * Get the value of a field.
	 *
	 * @param string field The name of the field.
	 */
	public function get_field_value($field)
	{
		//$field = strtoupper($field);		
		if ($this->is_bound($field))
		{
			return $this->get_bound_value($field);
		}
		else
		{
			return $this->_fields[$field];
		}
	}
	
	/**
	 * Check if a field is bound.
	 *
	 * @param string field The name of the field.
	 */
	private function is_bound($field)
	{
		if (array_key_exists($field, $this->_bindings))
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Bind a field to $_POST, $_GET, $_SERVER, $_COOKIE.
	 *
	 * @param string field The field name.
	 * @param array bind_properties An array with the variable and key to bind to.
	 * @example $data->bind_field(data_store_manager::BIND_POST, 'name');
	 */
	public function bind_field($field, array $bind_properties)
	{
		// bind should have a bind variable (BIND_POST, BIND_GET, etc... consts above in this class) and a key
		if (count($bind_properties) < 2)
		{
			throw new data_store_exception('Invalid bind!');
		}
		
		$variable = $bind_properties[0];
		$key = $bind_properties[1];
		if (!in_array($variable, array(self::BIND_POST, self::BIND_GET, self::BIND_SERVER, self::BIND_COOKIE)))
		{
			throw new data_store_exception('Invalid bind!');
		}
		
		$this->add_bind($field, $variable, $key);
	}
	
	/**
	 * Add the bind.
	 *
	 * @param string field The field name.
	 * @param string variable The variable to bind to.
	 * @param string key The variable key to bind to.
	 */
	private function add_bind($field, $variable, $key)
	{
		$this->_bindings[$field] = array($variable, $key);
	}
	
	/**
	 * Get the bound value of a field.
	 *
	 * @param string field The name of the field.
	 */
	private function get_bound_value($field)
	{
		if (!array_key_exists($field, $this->_bindings))
		{
			return NULL;
		}
		
		$variable = $this->_bindings[$field][0];
		$key = $this->_bindings[$field][1];
		
		return $this->evaluate_bind($variable, $key);
	}
	
	/**
	 * Evaluate a binding.
	 *
	 * @param const variable The variable to bind to (BIND_POST, BIND_GET, etc.)
	 * @param string key The variable key.
	 */
	private function evaluate_bind($variable, $key)
	{
		switch ($variable)
		{
			case self::BIND_POST:
				return (isset($_POST[$key])) ? $_POST[$key] : '';
			case self::BIND_GET:
				return (isset($_GET[$key])) ? $_GET[$key] : '';
			case self::BIND_SERVER:
				return (isset($_SERVER[$key])) ? $_SERVER[$key] : '';
			case self::BIND_COOKIE:
				return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : '';
		}
	}
	
	/**
	 * Count global fields.
	 */
	public function count_fields()
	{
		return count($this->_fields);
	}
	
	/**
	 * Mark some fields as required.
	 *
	 * @param array fields A list of field names.
	 */
	public function required_fields(array $fields)
	{
		if (count($fields) > 0)
		{
			foreach ($fields as $field)
			{
				$this->_required_fields[$field] = TRUE;
			}
		}
	}
	
	/**
	 * Run validation.
	 */
	public function validate()
	{
		$errors = $this->validate_required_fields();
		
		return $errors;
	}
	
	/**
	 * Validate that all required fields have been entered.
	 */
	private function validate_required_fields()
	{
		$errors = array();
		
		foreach (array_keys($this->_required_fields) as $field)
		{
			$value = $this->get_field_value($field);
			
			if (!validate::required($value))
			{
				$errors[$field] = 'Required';
			}
		}
		
		return $errors;
	}
	
	/**
	 * Save data to each data store.
	 */
	public function save(&$errors = array())
	{
		$this->_errors = $errors;
		
		// validate fields
		$this->_errors = array_merge($this->_errors, $this->validate());

		if (count($this->_errors) == 0)
		{
			// iterate through attached data stores
			foreach ($this->_attached_stores as $store)
			{
				// add global values
				foreach ($this->_fields as $name => $value)
				{
					// make sure we evaluate binds, if necessary
					$value = $this->get_field_value($name);
					
					// check for aliases
					$name = $this->get_field_name($name, $store->get_identifier(), get_class($store));
					
					$store->add_field($name, $value);
				}
				
				// save
				try
				{
					$result = $store->save();
				}
				catch (Exception $e)
				{
					if (!array_key_exists('server', $this->_errors))
					{
						$this->_errors['server'] = array();
					}
					
					$this->_errors['server'][] = 'Error saving to store ' . get_class($store) . '. Message: ' . $e->getMessage();
					continue;
				}
			}
		}
		
		if (count($this->_errors) == 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
}