<?php

/*************************************
 * 
 * Test script for MLPG Locator.
 * 
 ************************************/

// Error reporting
ini_set("display_errors", 1); 
error_reporting(E_ALL);

// Require needed scripts
require('thread.php'); // Thread class
require('logger.php'); // Logger class
require('locator.php'); // Locator class

/*** Start the magic ***/

// Initialize Logger class.
// Locator uses Logger for informing of found threads/marker.
// Unrecoverable errors won't be in Logger, Locator will throw them instead.
$logger = new Logger();
$locator = new Locator($logger);
// Try-catch block needed incase something unexpected happens.
try
{
	// Find all possible generals from catalog
	$generals = $locator->findPossibleGenerals();
}
catch (Exception $e)
{
	echo $e->getMessage() . '<br />';
	exit;
}
if (count($generals) === 0)
{
	echo 'No generals found.';
	exit;
}
// Sort threads by ID
usort($generals, array('Thread', 'sortThreads'));
// Look for marker in each one
foreach ($generals as $general)
{
	// Catch exceptions on thread-by-thread basis
	try
	{
		if ($locator->hasMarker($general->id))
			break; // Marker found. Just break, log will have more details.
	}
	catch (Exception $e)
	{
		echo $e->getMessage() . '<br />';
	}
}
// Print log
echo $logger;