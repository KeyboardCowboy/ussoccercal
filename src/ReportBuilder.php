<?php
/**
 * @file
 * Utility to build a status report for calendar builds.
 */

namespace KeyboardCowboy\CalDom\Components;

use CalDom\Calendar\Calendar;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ReportBuilder
 *
 * @package KeyboardCowboy\CalDom\Components
 */
class ReportBuilder {
  // Where we are storing the report.
  const REPORT_LOC = CAL_DIR . '/status.yml';

  // The calendar report.
  private $report = [];

  /**
   * Add a new report for a calendar.
   *
   * @param \CalDom\Calendar\Calendar $cal
   */
  public function addReport(Calendar $cal) {
    $path = $cal->getCalPath();
    $name = $cal->getCalInfo('name');

    $this->report[$name] = [
      'title' => $cal->getCalInfo('title'),
      'source' => $cal->getCalInfo('url'),
      'last_attempt' => time(),
    ];

    // If the file exists, get the time it was last generated.
    if (file_exists($path)) {
      $this->report[$name]['generated'] = filemtime($path);
      $this->report[$name]['url'] = $this->processUrl($path);
    }
  }

  /**
   * Get just the local path to the file.
   *
   * @param string $path
   *   The full file system path.
   *
   * @return string
   *   The path relative to the docroot.
   */
  private function processUrl($path) {
    // Remove the DOCROOT.
    return str_replace(DOCROOT, '', realpath($path));
  }

  /**
   * Write the status.yml file.
   */
  public function writeReport() {
    $yml = Yaml::dump($this->report, 2, 2);
    file_put_contents(self::REPORT_LOC, $yml);
  }

  /**
   * Load the existing report.
   *
   * @return array|mixed
   */
  public static function getReport() {
    return file_exists(self::REPORT_LOC) ? Yaml::parse(file_get_contents(self::REPORT_LOC)) : [];
  }

}
