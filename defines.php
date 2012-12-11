<?php

/*
 * This serves as the script's config file (for now...)
 */

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