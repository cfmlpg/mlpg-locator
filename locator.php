<?php

/*** These constants control the behaviour of Locator class ***/

/**
 * Marker's MD5 hash
 */
define('MARKER_MD5', 'YgIC5DRjGYcY2F4I+vJkOw==');

/**
 * URL to 4chan's API, <id> will be replaced with a thread ID
 */
define('API_URL', 'http://api.4chan.org/mlp/res/<id>.json');

/**
 * Catalog URL, used for finding possible threads quickly
 */
define('CATALOG_URL', 'http://boards.4chan.org/mlp/catalog');

/**
 * Keywords which should be searched for in thread's subject/comment
 */
define('THREAD_KEYWORDS', 'MLP General|MLPG|My Little Pony General');

/**
 * 4chan thread URL
 */
define('THREAD_URL', 'http://boards.4chan.org/mlp/res/');

/*************************************/

class Locator
{
	/**
	 * Logger object
	 * @var Logger
	 */
	private $_logger;
	
	/**
	 * Class constructor
	 * @param Logger $logger Logger object
	 */
	public function __construct(Logger $logger)
	{
		$this->_logger = $logger;
	}
	
	/**
	 * Find all possible generals from catalog
	 * @return Thread[] Array of Thread objects
	 * @throws Exception On any unrecoverable error
	 */
	public function findPossibleGenerals()
	{
		$threads = array();
		$catalogMatch = $keywordMatch = null;
		$contents = @file_get_contents(CATALOG_URL);
		if ($contents === false)
			throw new Exception('Error while fetching the catalog (URL: ' . CATALOG_URL . ').');
		if (preg_match('/var catalog = (?P<jsonstr>\{.+\});/', $contents, $catalogMatch))
		{
			$data = json_decode($catalogMatch['jsonstr']);
			if (($data === null) || (!property_exists($data, 'threads')))
				throw new Exception('Error while parsing response from catalog.');
			$regexp = '/(' . THREAD_KEYWORDS . ')/i';
			foreach ($data->threads as $id => $t)
			{
				if ((property_exists($t, 'teaser')) && (preg_match($regexp, $t->teaser, $keywordMatch)))
				{
					$thread = new Thread();
					$thread->id = $id;
					$thread->url = THREAD_URL . $id;
					$thread->title = $t->teaser;
					$thread->replies = (property_exists($t, 'r')) ? $t->r : 0;
					$thread->images = (property_exists($t, 'i')) ? $t->i : 0;
					$threads[] = $thread;
					$this->_logger->log('Thread (<a href="' . $thread->url . '">' . $thread->id . '</a>) found with keyword: ' . $keywordMatch[1] . '.');
				}
			}
			$this->_logger->log('Found ' . count($threads) . ' possible thread(s).'); 
		}
		else
			throw new Exception('Couldn\'t find catalog\'s JSON.');
		return $threads;
	}
	
	/**
	 * Try to find marker from the specified thread
	 * @param int $threadId ID of the thread which should be searched
	 * @return boolean True if marker was found, false otherwise
	 * @throws Exception On failed 4chan API request or on response parse error
	 */
	public function hasMarker($threadId)
	{
		$url = preg_replace('/\<id\>/', $threadId, API_URL);
		$contents = @file_get_contents($url);
		if ($contents === false)
			throw new Exception('Error while fetching thread ' . $threadId . ' from 4chan API.');
		$data = json_decode($contents);
		if (($data === null) || (!property_exists($data, 'posts')))
			throw new Exception('Error while parsing response from 4chan API on thread ' . $threadId . '.');
		foreach ($data->posts as $post)
		{
			if ((property_exists($post, 'md5')) && ($post->md5 === MARKER_MD5))
			{
				$this->_logger->log('Marker found from thread ' . $threadId . '.');
				return true;
			}
		}
		return false;
	}
}