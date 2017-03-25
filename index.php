<?php

require_once __DIR__ . '/vendor/autoload.php';

$args = array_filter(explode('/', trim($_SERVER['REQUEST_URI'], '/')));
$page = empty($args) ? 'home' : array_shift($args);
$class = ucwords(strtolower($page)) . 'Page';
$file = __DIR__ . "/pages/{$class}.php";

if (file_exists($file)) {
  require_once __DIR__ . '/src/Page.php';
  require_once $file;

  $page = new $class();
  print $page->render();
}
else {
  exit(1);
}
