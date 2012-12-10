<?php

/**
 * Simple logging class
 */
class Logger
{
	/**
	 * Log entries as an array
	 * @var string[] 
	 */
	private $_log;
	
	/**
	 * Class constructor
	 */
	public function __construct()
	{
		$this->_log = array();
	}
	
	/**
	 * Add a line to log
	 * @param string $str Line to add to log
	 */
	public function log($str)
	{
		$this->_log[] = $str;
	}
	
	/**
	 * Get log line count
	 * @return int Lines in log
	 */
	public function count()
	{
		return count($this->_log);
	}
	
	/**
	 * String representation of instance
	 * @return string Log as string
	 */
	public function __toString()
	{
		$out = '';
		$out .= '<div>Log:<br />';
		foreach ($this->_log as $line)
			$out .= $line . '<br />';
		$out .= '</div>';
		return $out;
	}
}