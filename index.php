<?php
/**
 * @file
 * Build the .ics file for the MNT.
 */
require_once __DIR__ . '/src/SoccerCal.php';

$mnt = new SoccerCal('mnt');
$mnt->generateCalendar();
print $mnt->summary();
