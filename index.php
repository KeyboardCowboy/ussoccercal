<?php
/**
 * @file
 * Build the .ics file for the MNT.
 */

require_once __DIR__ . '/src/SoccerCal.php';

try {
  $mnt = new SoccerCal('mnt');
  $mnt->generateCalendar();
  print $mnt->summary();
}
catch (Exception $e) {
  print $mnt->failure();
}
