<?php

class Thread
{
	/**
	 * Thread ID
	 * @var int 
	 */
	private $_id;
	
	/**
	 * Thread title
	 * @var string 
	 */
	private $_title;
	
	/**
	 * Thread author
	 * @var string 
	 */
	private $_author;
	
	/**
	 * Thread author's trip
	 * @var string
	 */
	private $_trip;
	
	/**
	 * Thread's date as UNIX timestamp
	 * @var int
	 */
	private $_date;
	
	/**
	 * Reply count
	 * @var int 
	 */
	private $_replies;
	
	/**
	 * Image count
	 * @var int 
	 */
	private $_images;
	
	/**
	 * Class constructor
	 * @param int $id Thread ID
	 * @param string $title Thread title
	 * @param string $author Thread author
	 * @param string $trip Thread author's trip
	 * @param int $date Thread's date as UNIX timestamp
	 * @param int $replies Reply count
	 * @param int $images Image count
	 */
	public function __construct($id = 0, $title = '', $author = '', $trip = '', $date = 0, $replies = 0, $images = 0)
	{
		$this->_id = $id;
		$this->_title = $title;
		$this->_author = $author;
		$this->_trip = $trip;
		$this->_date = $date;
		$this->_replies = $replies;
		$this->_images = $images;
	}
	
	/**
	 * Try to find marker from the thread
	 * @return boolean True if marker was found, false otherwise
	 * @throws Exception On failed 4chan API request or on response parse error
	 */
	public function isMarked()
	{
		$url = preg_replace('/\<id\>/', $this->_id, API_URL);
		$contents = @file_get_contents($url);
		if ($contents === false)
			throw new Exception('Error while fetching thread ' . $this->_id . ' from 4chan API.');
		$data = json_decode($contents);
		if (($data === null) || (!property_exists($data, 'posts')))
			throw new Exception('Error while parsing response from 4chan API on thread ' . $this->_id . '.');
		foreach ($data->posts as $post)
			if ((property_exists($post, 'md5')) && ($post->md5 === MARKER_MD5))
				return true;
		return false;
	}
	
	/**
	 * Combines thread author's name and trip
	 * @return string Formatted author name and trip
	 */
	private function formatAuthor()
	{
		return trim($this->_author . ' ' . $this->_trip);
	}
	
	/**
	 * Formats thread's timestamp to 4chan style date string
	 * @return string Date in 4chan style format
	 */
	private function formatDate()
	{
		$dt = new DateTime('@' . $this->_date);
		return $dt->format('m/d/y(D)H:i') . ' (UTC)';
	}
	
	/**
	 * Formats thread's URL
	 * @return string Thread URL
	 */
	private function formatURL()
	{
		return THREAD_URL . $this->_id;
	}
	
	/**
	 * Magic method for settings members
	 * @param string $name Member name
	 * @param mixed $value Member value
	 */
	public function __set($name, $value)
	{
		// Only allow setting members which have already been introduced in the class
		if (isset($this->{'_' . $name}))
			$this->{'_' . $name} = $value;
	}
	
	/**
	 * Magic method for getting members, lazy coder's version
	 * @param string $name Member name
	 * @return mixed|null Member value or null
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'url':
				$out = $this->formatURL();
				break;
			case 'link':
				$out = '<a href="' . $this->formatURL() . '">' . $this->_id . '</a>';
				break;
			case 'author':
				$out = $this->formatAuthor();
				break;
			case 'date':
				$out = $this->formatDate();
				break;
			default:
				$out = (isset($this->{'_' . $name})) ? $this->{'_' . $name} : null;
		}
		return $out;
	}
	
	/**
	 * Custom toString override
	 * @return string
	 */
	public function __toString()
	{
		return '';
	}
	
	/**
	 * Create a Thread object from catalog's JSON format
	 * @param int $id Thread ID
	 * @param object $json JSON format object
	 * @return \Thread
	 */
	public static function fromCatalogJSON($id, $json)
	{
		$thread = new Thread();
		$thread->id = $id;
		$thread->title = (property_exists($json, 'teaser')) ? $json->teaser : '';
		$thread->author = (property_exists($json, 'author')) ? $json->author : '';
		$thread->trip = (property_exists($json, 'trip')) ? $json->trip : '';
		$thread->date = (property_exists($json, 'date')) ? $json->date : 0;
		$thread->replies = (property_exists($json, 'r')) ? $json->r : 0;
		$thread->images = (property_exists($json, 'i')) ? $json->i : 0;
		return $thread;
	}
	
	/**
	 * Custom sort method for threads, comparison is done on thread IDs
	 * and thread with highest ID comes first
	 * @param Thread $first First thread to compare
	 * @param Thread $second Second thread to compare
	 * @return int 1, 0 or -1
	 */
	public static function sortThreads(Thread $first, Thread $second)
	{
		if ($first->id === $second->id)
			return 0;
		return ($first->id < $second->id) ? 1 : -1;
	}
}