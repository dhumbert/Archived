<?php

class FileLogger implements LogProvider
{
	/**
	 * Filename of the log file.
	 *
	 * @access private
	 */
	private $_filename;
	
	/**
	 * Class constructor.
	 * 
	 * @access public
	 * @param string $filename
	 */
	public function __construct($filename)
	{
		$this->_filename = $filename;
	}
	
	/**
	 * Log any messages.
	 * 
	 * @access public
	 * @param string $message
	 */
	public function logMessage($message)
	{
		file_put_contents($this->_filename, $message . PHP_EOL, FILE_APPEND);
	}
}
