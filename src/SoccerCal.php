<?php
/**
 * @file
 * Generate an iCal ffed for US Soccer.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/SoccerCalEvent.php';
require_once __DIR__ . '/Renderer.php';

/**
 * Class SoccerCal.
 */
class SoccerCal {
  // Hostname for the US Soccer website.
  const USSOCCER_HOSTNAME = 'http://www.ussoccer.com';

  // URLs to fetch.
  protected static $url = [
    'mnt' => 'http://www.ussoccer.com/mens-national-team/schedule-tickets',
  ];

  // THe team shortname we are building.
  protected $team;

  // THe retrieved document object from ussoccer.com.
  protected $document;

  // Event objects extracted from a ussoccer.com schedule page.s
  protected $events = [];

  /**
   * SoccerCal constructor.
   *
   * @param string $team
   *   The team shortname for the schedule to fetch.
   *   Ex. 'mnt' or 'wnt'
   */
  public function __construct($team) {
    $this->team = $team;
    $this->fetchDocument();
    $this->extractEvents();
  }

  /**
   * Fetch the DOM of a schedule page.
   */
  private function fetchDocument() {
    if ($contents = file_get_contents(static::$url[$this->team])) {
      $dom = new DOMDocument();
      @$dom->loadHTML($contents, LIBXML_NOERROR);
      $dom->preserveWhiteSpace = FALSE;
      $dom->normalizeDocument();

      $this->document = $dom;
    }
    else {
      throw new Exception("Failed to fetch data from url.");
    }
  }

  /**
   * Extract events from the schedule DOM.
   */
  private function extractEvents() {
    $tables = $this->document->getElementsByTagName('table');

    // Cycle through each table row.
    foreach ($tables as $table) {
      // Validate the table element.
      if (!$this->validateTable($table)) {
        continue;
      }

      $rows = $table->getElementsByTagName('tr');

      // Pull the data out of the TD elements.
      foreach ($rows as $tr) {
        $cells = $tr->getElementsByTagName('td');

        if ($cells->length > 0) {
          $this->events[] = new SoccerCalEvent($this, $cells);
        }
      }
    }

    if (empty($this->events)) {
      throw new Exception("Failed to extract events from the schedule.");
    }
  }

  /**
   * Verify that the table element is a valid match table.
   *
   * @param \DOMElement $table
   *   A table element from the schedule page.
   *
   * @return bool
   *   TRUE if the table has the appropriate class.
   */
  private function validateTable(DOMElement $table) {
    $classes = explode(' ', $table->getAttribute('class'));

    return in_array('match-table', $classes);
  }

  /**
   * Render the ical file.
   */
  public function render() {
    $twig = Renderer::load()->twig;
    $vars = [];

    // Calendar title.
    // @todo: Set title dynamically.
    $vars['title'] = "US Men's National Team";

    // Build events.
    foreach ($this->events as $event) {
      $vars['events'][] = $event->render();
    }

    return $twig->render('ical.twig', $vars);
  }

  /**
   * Get the team shortcode.
   *
   * @return string
   *   The team shortcode.
   */
  public function getTeam() {
    return $this->team;
  }

  /**
   * Get the URL for the team schedule.
   *
   * @return string
   *   The URL for the team schedule.
   */
  public function getUrl() {
    return static::$url[$this->team];
  }

  /**
   * Get the HTTP HOST value.
   *
   * @return string
   *   The value of $_SERVER['HTTP_HOST'] or localhost if not set.
   */
  private function httpHost() {
    return isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
  }

  /**
   * Create the subscribable calendar file.
   */
  public function generateCalendar() {
    $calendar = $this->render();
    file_put_contents(__DIR__ . "/../calendars/{$this->team}.ics", $calendar);
  }

  /**
   * Print a summary and URL for the calendar.
   */
  public function summary() {
    $url = "http://{$this->httpHost()}/calendars/{$this->team}.ics";
    $link = '<a href="' . $url . '">' . $url . '</a>';

    return date('c') . " - Calendar rendered<br />{$link}";
  }

  /**
   * Return an error message if the build fails.
   *
   * @return string
   *   A nice error message.
   */
  public function failure() {
    return 'Error building calendar for ' . $this->team;
  }

}
