<?php
/**
 * @file
 * Initialize a page.
 */
ini_set('display_errors', '1');
ini_set('error_reporting', E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

define('DOCROOT', $_SERVER['DOCUMENT_ROOT']);
define('CAL_DATA_SOURCE', __DIR__ . '/../calendars/data');
define('CAL_DIR', __DIR__ . '/../calendars');
