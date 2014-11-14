<?php defined('BASE_DIR') OR die('No direct access allowed.');

class mysql_store extends relational_database_store
{
	/**
	 * Connect to the database.
	 */
	public function connect($server, $user, $password, $database)
	{
		$result = @mysql_connect($server, $user, $password);
		@mysql_select_db($database);
		
		return $result;
	}
	 
	/** 
	 * Get last error message.
	 */
	protected function error()
	{
		return mysql_error($this->_connection);
	}
	
	/**
	 * Quote table or field name.
	 *
	 * @param string name The table or field name.
	 */
	protected function quote_field_name($name)
	{
		return '`' . $name . '`';
	}
	
	/**
	 * Quote table name.
	 *
	 * @param string name The table name.
	 */
	protected function quote_table_name($name)
	{
		// same as quoting field name for MySQL
		return $this->quote_field_name($name);
	}

	/**
	 * Escape values.
	 *
	 * @param string value The value to escape.
	 */
	protected function quote($value)
	{
		return "'" . mysql_real_escape_string($value, $this->_connection) . "'";
	}

	/**
	 * Execute an SQL statement.
	 *
	 * @param string sql The SQL string to execute.
	 */
	public function query($sql)
	{
		return mysql_query($sql, $this->_connection);
	}

	/**
	 * Close the MySQL connection.
	 */
	protected function close()
	{
		@mysql_close($this->_connection);
	}
}