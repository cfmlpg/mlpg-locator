<?php

/*************************************
 * Test script for MLPG Locator.     *
 ************************************/

// Error reporting
ini_set("display_errors", 1); 
error_reporting(-1);

// Require the script
require('catalog.php');

/**********************
*** Start the magic ***
**********************/

echo '<html><head></head><body>';
// Initialize Catalog class
// Could pass catalog URL here, defaults to whatever's in defines.php.
$catalog = new Catalog();
// Catch exceptions
try
{
	$threads = 
		$catalog
		->getThreads()		// Get all threads from catalog
		->filter()			// Filter results (defaults to THREAD_KEYWORDS constant)
		->sort()			// Sort filtered results (defaults to sort by ID)
		->getResult();		// And get the filtered results!
	// Echo statistics
	echo 'Found ' . $catalog->count() . ' possible general(s) in catalog:<br />';
	foreach ($threads as $thread)
		echo '- Thread ' . $thread->link . ' by ' . $thread->author . ' on ' . $thread->date . '.<br />';
	// Look for marker in each thread
	$markerFound = false;
	foreach ($threads as $thread)
	{
		// Another try-catch block needed so we don't break out of foreach loop
		// incase a single API request fails.
		try
		{
			if ($thread->isMarked())
			{
				$markerFound = true;
				echo 'Marker found from thread ' . $thread->link . '.<br />';
				break; // Break foreach loop if marker was found
			}
		}
		catch (Exception $e)
		{
			echo 'Unexpected error, message returned was: ' . $e->getMessage() . '<br />';
		}
	}
	if (!$markerFound)
		// All threads checked, no marker found
		echo 'No marker found.';
}
catch (Exception $e)
{
	echo 'Unexpected error, message returned was: ' . $e->getMessage() . '<br />';
}
echo '</body></html>';