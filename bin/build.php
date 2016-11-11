<?php
/**
 * @file
 * Build the .ics file for the MNT.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Renderer.php';
require_once __DIR__ . '/../src/SoccerCal.php';

// Load the calendar info.
$json = SoccerCal::loadCalendarInfo();

// Build each calendar.
foreach ($json->calendars as &$cal_info) {
  try {
    $calendar = new SoccerCal($cal_info);
    $calendar->generateCalendar();

    // Log the results.
    $vars = [
      'last_attempt' => time(),
      'generated' => time(),
      'message' => 'Calendar successfully updated!',
      'status' => 1,
    ];
    SoccerCal::setStatus($cal_info->name, $vars);

    print "Updated {$cal_info->title}.";
  }
  catch (Exception $e) {
    // Log the error.
    $vars = [
      'last_attempt' => time(),
      'status' => 0,
      'message' => $e->getMessage(),
    ];

    SoccerCal::setStatus($cal_info->name, $vars);

    print "Failed to update {$cal_info->title}.";
  }
}

