<?php defined('BASE_DIR') OR die('No direct access allowed.');

class mssql_store extends relational_database_store
{
	/**
	 * Connect to the database.
	 */
	function connect($server, $user, $password, $database)
	{
		$result = @mssql_connect($server, $user, $password);
		@mssql_select_db($database);
		
		return $result;
	}
	 
	/** 
	 * Get last error message.
	 */
	protected function error()
	{
		return mssql_get_last_message();
	}
	
	/**
	 * Quote table or field name.
	 *
	 * @param string name The table or field name.
	 */
	protected function quote_field_name($name)
	{
		return '' . $name . '';
	}
	
	/**
	 * Quote table name.
	 *
	 * @param string name The table name.
	 */
	protected function quote_table_name($name)
	{
		// same as quoting field name for MySQL
		return '[' . $name . ']';
	}

	/**
	 * Escape values.
	 * Based on http://stackoverflow.com/questions/574805/how-to-escape-strings-in-mssql-using-php.
	 *
	 * @param string value The value to escape.
	 */
	protected function quote($value)
	{
		if ( !isset($value) or empty($value) ) return '';
        if ( is_numeric($value) ) return $value;

        $non_displayables = array(
            '/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
            '/%1[0-9a-f]/',             // url encoded 16-31
            '/[\x00-\x08]/',            // 00-08
            '/\x0b/',                   // 11
            '/\x0c/',                   // 12
            '/[\x0e-\x1f]/'             // 14-31
        );
        foreach ( $non_displayables as $regex )
            $value = preg_replace( $regex, '', $value );
        $value = str_replace("'", "''", $value );
        return "'" . $value . "'";
	}

	/**
	 * Execute an SQL statement.
	 *
	 * @param string sql The SQL string to execute.
	 */
	protected function query($sql)
	{
		return @mssql_query($sql, $this->_connection);
	}

	/**
	 * Close the connection.
	 */
	protected function close()
	{
		@mssql_close($this->_connection);
	}
}