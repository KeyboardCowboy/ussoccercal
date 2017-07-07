<?php
/**
 * @file
 * Build the .ics files.
 */

namespace KeyboardCowboy\CalDom;

use KeyboardCowboy\CalDom\Calendars;
use KeyboardCowboy\CalDom\Components\ReportBuilder;

require_once __DIR__ . '/init.php';

$reports = new ReportBuilder();

if ($cal = Calendars\USSoccerCal::load(CAL_DATA_SOURCE . '/usmnt.yml')) {
  $cal->generateCalendar();
  $reports->addReport($cal);
}

if ($cal = Calendars\USSoccerCal::load(CAL_DATA_SOURCE . '/uswnt.yml')) {
  $cal->generateCalendar();
  $reports->addReport($cal);
}

if ($cal = Calendars\USSoccerCal::load(CAL_DATA_SOURCE . '/ussoccer.yml')) {
  $cal->generateCalendar();
  $reports->addReport($cal);
}

if ($cal = Calendars\GoldCup2017::load(CAL_DATA_SOURCE . '/goldcup2017.yml')) {
  $cal->generateCalendar();
  $reports->addReport($cal);
}

$reports->writeReport();
