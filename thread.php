<?php

class Thread
{
	/**
	 * Thread ID
	 * @var int 
	 */
	public $id;
	
	/**
	 * Full URL to thread
	 * @var string 
	 */
	public $url;
	
	/**
	 * Thread title
	 * @var string 
	 */
	public $title;
	
	/**
	 * Reply count
	 * @var int 
	 */
	public $replies;
	
	/**
	 * Image count
	 * @var int 
	 */
	public $images;
	
	/**
	 * Class constructor
	 * @param int $id Thread ID
	 * @param string $url Thread URL
	 * @param string $title Thread title
	 * @param int $replies Reply count
	 * @param int $images Image count
	 */
	public function __construct($id = 0, $url = '', $title = '', $replies = 0, $images = 0)
	{
		$this->id = $id;
		$this->url = $url;
		$this->title = $title;
		$this->replies = $replies;
		$this->images = $images;
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