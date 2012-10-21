<?php

// Error reporting
ini_set("display_errors", 1); 
error_reporting(E_ALL);

define('MARKER_MD5', 'YgIC5DRjGYcY2F4I+vJkOw==');
define('API_URL', 'https://api.4chan.org/mlp/res/<id>.json');
define('CATALOG_URL', 'http://catalog.mlpg.co/mlp/');
define('THREAD_KEYWORDS', 'MLP General|MLPG|My Little Pony General');
define('THREAD_URL', 'https://boards.4chan.org/mlp/res/');

class Thread
{
	public $id;
	public $url;
	public $title;
	public $replies;
	public $images;
}

class Crawler
{
	public static function FindGenerals()
	{
		$threads = array();
		$contents = file_get_contents(CATALOG_URL);
		if ($contents !== false)
		{
			if (preg_match('/var catalog = (?P<jsonstr>\{.+\});/', $contents, $jsonMatch))
			{
				$data = json_decode($jsonMatch['jsonstr']);
				if ($data !== null)
				{
					$regexp = '/(' . THREAD_KEYWORDS . ')/i';
					foreach ($data->{'threads'} as $id => $t)
					{
						if (preg_match($regexp, $t->{'teaser'}, $match))
						{
							$thread = new Thread();
							$thread->id = $id;
							$thread->url = THREAD_URL . $id;
							$thread->title = $t->{'teaser'};
							$thread->replies = $t->{'r'};
							$thread->images = $t->{'i'};
							$threads[] = $thread;
							Logger::AddLine('Thread (<a href="' . $thread->url . '">' . $thread->id . '</a>) found with keyword: ' . $match[1]);
						}
					}
					Logger::Addline('Found ' . count($threads) . ' possible threads.');
				}
				else
					Logger::Addline('Error while parsing the Catalog JSON.');
			}
			else
				Logger::Addline('Couldn\'t find Catalog\'s JSON.');
		}
		else
			Logger::Addline('Error while connecting to the Catalog.');
		return $threads;
	}

	public static function FindMarker($threadId)
	{
		$url = preg_replace('/\<id\>/', $threadId, API_URL);
		$contents = file_get_contents($url);
		if ($contents !== false)
		{
			$data = json_decode($contents);
			foreach ($data->{'posts'} as $post)
			{
				if ((property_exists($post, 'md5')) && ($post->{'md5'} === MARKER_MD5))
				{
					Logger::AddLine('Marker found from thread ' . $threadId);
					return true;
				}
			}
		}
		return false;
	}
}

class Logger
{
	private static $LOG = array();
	
	public static function AddLine($line)
	{
		self::$LOG[] = $line;
	}
	
	public static function PrintLog()
	{
		echo '<div>Log:<br />';
		foreach (self::$LOG as $line)
			echo $line . '<br />';
		echo '</div>';
	}
}

function sortThreads($first, $second)
{
	return $first->id < $second->id;
}

/* TEST SCRIPT */

$threads = Crawler::FindGenerals();
if (count($threads) > 0)
{
	$markedThread = null;
	usort($threads, 'sortThreads');
	foreach ($threads as $thread)
	{
		if (Crawler::FindMarker($thread->id))
		{
			$markedThread = $thread;
			break;
		}
	}
	echo '<div>';
	if (isset($markedThread))
	{
		echo 'Marker found from thread <a href="' . $markedThread->url . '">' . $markedThread->id . '</a>!<br />';
		echo 'Thread title: ' . $markedThread->title . '<br />';
		echo 'Replies: ' . $markedThread->replies . ', images: ' . $markedThread->images . '.<br />';
	}
	else
	{
		echo '<p>Couldn\'t find a marked thread, listing all possible generals:</p>';
		foreach ($threads as $thread)
		{
			echo '<a href="' . $thread->url . '">Unmarked general with ID ' . $thread->id . ' (' . $thread->replies . ' replies, ' . $thread->images . ' images)<br />';
		}
	}
	echo '</div>';
	echo '<br /><br /><br />';
	Logger::PrintLog();
}
else
{
	echo '<p>No threads found.</p>';
}

