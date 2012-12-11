<?php

// Require needed scripts
require('defines.php');
require('thread.php');

/**
 * Class for abstracting a 4chan catalog
 */
class Catalog
{
	/**
	 * URL to catalog
	 * @var string 
	 */
	private $_url;
	
	/**
	 * Array of Thread objects, parsed from catalog
	 * @var Thread[]
	 */
	private $_threads;
	
	/**
	 * Temporary variable for storing threads
	 * @var Thread[]
	 */
	private $_temp;
	
	/**
	 * Constructor
	 * @param string $url URL to catalog
	 */
	public function __construct($url = CATALOG_URL)
	{
		$this->_url = $url;
	}
	
	/**
	 * Populates internal Thread object array with data from catalog
	 * @return \Catalog For method chaining
	 * @throws Exception On any unrecoverable error
	 */
	public function getThreads()
	{
		// Threads need to be retrieved only once
		if (isset($this->_threads))
			return $this;
		$threads = array();
		$match = null;
		$contents = @file_get_contents($this->_url);
		if ($contents === false)
			throw new Exception('Error while fetching the catalog (URL: ' . $this->_url . ').');
		// Locate JavaScript variable which contains all threads in JSON format from page source
		if (preg_match('/var catalog = (?P<jsonstr>\{.+\});/', $contents, $match))
		{
			$data = json_decode($match['jsonstr']);
			// Verify decode succeeded and that the JSON is valid
			if (($data === null) || (!property_exists($data, 'threads')))
				throw new Exception('Error while parsing response from catalog.');
			foreach ($data->threads as $id => $thread)
				$threads[] = Thread::fromCatalogJSON($id, $thread);
		}
		else
			throw new Exception('Error while parsing response from catalog.');
		$this->_threads = $threads;
		$this->_temp = $this->_threads;
		return $this;
	}
	
	/**
	 * Filters threads which match the given keywords (match performed on thread title)
	 * @param string|string[] $keywords Keywords as a pipe-delimited string or an array
	 * @return \Catalog For method chaining
	 */
	public function filter($keywords = THREAD_KEYWORDS)
	{
		if (isset($this->_threads))
		{
			if (is_array($keywords))
				$keywords = join('|', $keywords);
			$threads = array();
			$regexp = '/(' . $keywords . ')/i';
			foreach ($this->_threads as $thread)
				if (preg_match($regexp, $thread->title))
					$threads[] = $thread;
			$this->_temp = $threads;
		}
		return $this;
	}
	
	/**
	 * Sort the thread list
	 * @param callable $func Sorting function
	 * @return \Catalog For method chaining
	 */
	public function sort(callable $func = null)
	{
		if (is_null($func))
			$func = array('Thread', 'sortThreads');
		if (isset($this->_temp))
			usort($this->_temp, $func);
		return $this;
	}
	
	/**
	 * Reset internal temporary thread list
	 * @return \Catalog For method chaining
	 */
	public function reset()
	{
		$this->_temp = $this->_threads;
		return $this;
	}
	
	/**
	 * Returns the count of items in thread collection
	 * @return int Items in collection
	 */
	public function count()
	{
		return (isset($this->_temp)) ? count($this->_temp) : 0;
	}
	
	/**
	 * Get the result set
	 * @return Thread[] Array of Thread objects
	 */
	public function getResult()
	{
		return (isset($this->_temp)) ? $this->_temp : array();
	}
}