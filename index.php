<?php
/**
 * @file
 * Traffic director.
 */

namespace KeyboardCowboy\CalDom;

use KeyboardCowboy\CalDom\Pages\Page;

require_once __DIR__ . '/bin/init.php';

$args = array_filter(explode('/', trim($_SERVER['REQUEST_URI'], '/')));
$page_arg = empty($args) ? 'home' : array_shift($args);

$page = new Page($page_arg);
print $page->render();
