<?php

require_once __DIR__ . '/src/SoccerCal.php';

$mnt = new SoccerCal('mnt');
$mnt->render();
print date('c') . ' - Calendar rendered';
