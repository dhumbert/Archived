<?php defined('BASE_DIR') OR die('No direct access allowed.');

abstract class relational_database_store extends data_store
{
	protected $_connection = NULL;
	
	/**
	 * Save data to the database.
	 */
	public function save()
	{
		$this->validate();
		
		$con = $this->get_connection();
		$sql = $this->build_sql();
		$result = $this->query($sql);
		
		if (!$result)
		{
			throw new data_store_exception('Error saving to datastore. Server says: ' . $this->error());
		}
		
		$this->close();
		
		return $result;
	}
	
	/**
	 * Connect to the database.
	 */
	protected function get_connection()
	{
		if (!$this->_connection)
		{
			$server = $this->get_option('server');
			$db = $this->get_option('database');
			$user = $this->get_option('user');
			$password = $this->get_option('password');
			
			$this->_connection = $this->connect($server, $user, $password, $db);
			
			if (!$this->_connection)
			{
				throw new data_store_exception('Unable to connect to datastore. Server says: ' . mysql_error());
			}
		}
		
		return $this->_connection;
	}
	
	/**
	 * Build the SQL string.
	 */
	protected function build_sql()
	{
		$sql  = 'INSERT INTO ' . $this->quote_table_name($this->get_option('table'));
		$sql .= ' (' . $this->build_field_sql() . ') VALUES (';
		$sql .= $this->build_value_sql();
		$sql .= ');';
		
		return $sql;
	}
	
	/**
	 * Build the SQL listing fields that we have.
	 */
	protected function build_field_sql()
	{
		$keys = array_map(array($this, 'quote_field_name'), array_keys($this->_fields));
		$sql = implode(', ', $keys);
		
		return $sql;
	}
	
	/**
	 * Build the SQL for the values we want to insert.
	 */
	protected function build_value_sql()
	{
		$values = array_map(array($this, 'quote'), $this->_fields);
		$sql = implode(', ', $values);
		
		return $sql;
	}
	
	/**
	 * Get the last error. Driver-specific.
	 */
	abstract protected function error();
	
	/**
	 * Connect to the DB. Driver-specific.
	 */
	abstract protected function connect($server, $user, $password, $database);
	
	/**
	 * Quote field name. Driver-specific.
	 *
	 * @param string name The field name.
	 */
	abstract protected function quote_field_name($name);
	
	/**
	 * Quote table name. Driver-specific.
	 *
	 * @param string name The table name.
	 */
	abstract protected function quote_table_name($name);
	
	/**
	 * Escape values. Driver-specific.
	 *
	 * @param string value The value to escape.
	 */
	abstract protected function quote($value);
	
	/**
	 * Execute an SQL statement. Driver-specific.
	 *
	 * @param string sql The SQL string to execute.
	 */
	abstract protected function query($sql);
	
	/**
	 * Close the connection. Driver-specific.
	 */
	abstract protected function close();
}