<?php
/**
 * @file
 * Generate an iCal ffed for US Soccer.
 *
 * @todo: Make sure there is detailed data validation and error reporting.  Log
 *   file and daily emails?
 */

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class SoccerCal.
 */
class SoccerCal {
  const USSOCCER_HOSTNAME = 'http://www.ussoccer.com';

  // URLs to fetch.
  protected static $url = [
    'mnt' => 'http://www.ussoccer.com/mens-national-team/schedule-tickets',
  ];

  protected $team;
  protected $document;
  protected $events = [];

  public function __construct($team) {
    $this->team = $team;
    $this->fetchDocument();
    $this->extractEvents();
  }

  private function fetchDocument() {
    $contents = file_get_contents(static::$url[$this->team]);

    $dom = new DOMDocument();
    @$dom->loadHTML($contents, LIBXML_NOERROR);
    $dom->preserveWhiteSpace = FALSE;
    $dom->normalizeDocument();

    $this->document = $dom;
  }

  public function extractEvents() {
    // @todo: Is there a way to specify this specific table?
    $tables = $this->document->getElementsByTagName('table');

    // @todo: Run a sanity check on the data so we only replace existing data with valid data.

    // Cycle through each table row.
    foreach ($tables as $table) {
      $rows = $table->getElementsByTagName('tr');

      // Pull the data out of the TD elements.
      // @todo: Parse the text.  It's a mess.
      foreach ($rows as $tr) {
        $cells = $tr->getElementsByTagName('td');

        if ($cells->length > 0) {
          // @todo: Create a unique key(s)? from the team and date.
          $this->events[] = new SoccerCalEvent($this, $cells);
        }
      }
    }
  }

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

    // @todo: Prep calendar vars for rendering.
    $calendar = $twig->render('ical.twig', $vars);

    file_put_contents(__DIR__ . "/../calendars/{$this->team}.ics", $calendar);

    $url = "http://" . $_SERVER['HTTP_HOST'] . "/calendars/{$this->team}.ics";
    $link = '<a href="' . $url . '">' . $url . '</a>';

    print "$link<br />";
  }

  public function getTeam() {
    return $this->team;
  }

  public function getUrl() {
    return static::$url[$this->team];
  }

}

/**
 * Class SoccerCalEvent.
 */
class SoccerCalEvent {
  private $cal;

  private $matchup;
  private $venue;
  private $info;
  private $datetime;
  private $links = [];
  private $url;

  // Map data structure to DOM elements.
  protected static $field = [
    'date' => 0,
    'time' => 1,
    'matchup' => 2,
    'venue' => 3,
    'info' => 4,
  ];

  public function __construct($cal, $cells) {
    $this->cal = $cal;
    $this->extractData($cells);
  }

  protected function extractData($cells) {
    $this->datetime = $this->extractDateTime($cells->item(static::$field['date']));
    $this->matchup = $this->extractMatchup($cells->item(static::$field['matchup']));
    $this->venue = $this->extractVenue($cells->item(static::$field['venue']));
    $this->info = $this->extractInfo($cells->item(static::$field['info']));
  }

  protected function extractDateTime(DOMElement $cell) {
    $attributes = $cell->getElementsByTagName('time')->item(0)->attributes;
    $datetime = $attributes->getNamedItem('datetime')->value;

    // The times from the website are listed in EST but still contain the UMT
    // indicator 'Z' so we remove it as we're setting the time zone manually.
    $datetime = str_replace('Z', '', $datetime);

    return $datetime;
  }

  protected function extractMatchup(DOMElement $cell) {
    $attributes = $cell->getElementsByTagName('meta')->item(0)->attributes;
    $value = $attributes->getNamedItem('content')->value;

    // Remove sponsorships.
    list($value,) = explode(',', $value, 2);

    // Store the URL to the event.
    $attributes = $cell->getElementsByTagName('a')->item(0)->attributes;
    $url = $attributes->getNamedItem('href')->value;
    $this->url = SoccerCal::USSOCCER_HOSTNAME . $url;

    return $value;
  }

  protected function extractVenue(DOMElement $cell) {
    // Store links for the description.
    $this->extractLinks($cell);

    // Grab the data from the meta element.
    $attributes = $cell->getElementsByTagName('meta')->item(0)->attributes;
    $venue = $attributes->getNamedItem('content')->value;

    // Exclude anything after a line break.  This is usually links and other
    // junk we don't need.
    list($venue) = explode('<br />', $venue);

    return $venue;
  }

  protected function extractInfo(DOMElement $cell) {
    return $this->getInnerHTML($cell);
  }

  protected function extractLinks(DOMElement $cell) {
    $links = $cell->getElementsByTagName('a');

    $i = 0;
    while ($a = $links->item($i)) {
      $attributes = $a->attributes;
      $href = $attributes->getNamedItem('href')->value;
      $text = $a->textContent;

      $this->links[$href] = $text;

      $i++;
    }
  }

  /**
   * @param $element
   *
   * @return mixed
   */
  private function getInnerHTML($element) {
    return $element->ownerDocument->saveHTML($element);
  }

  protected function formatDateTime($datetime, $full = TRUE) {
    $date = $full ? date('Ymd\THis', $datetime) : date('Ymd', $datetime);

    return 'America/New_York:' . $date;
  }

  public function render() {
    $twig = Renderer::load()->twig;

    $vars = [
      'uid' => $this->getUid(),
      'summary' => $this->getSummary(),
      'description' => $this->getDescription(),
      'location' => $this->getLocation(),
      'url' => $this->getUrl(),
      'startTime' => $this->getStartDate(),
      'endTime' => $this->getEndDate(),
    ];

    return $twig->render('event.twig', $vars);
  }

  public function getUid() {
    return $this->cal->getTeam() . '-' . $this->getStartDate() . '@localhost';
  }

  public function getUrl() {
    return $this->url;
  }

  public function getSummary() {
    return $this->matchup;
  }

  public function getDescription() {
    $out = [];

    // Add extracted URLs.
    foreach ($this->links as $href => $text) {
      $url = SoccerCal::USSOCCER_HOSTNAME . $href;
      $out[] = $text . ':\n' . $url;
    }

    return implode('\n\n', $out);
  }

  public function getLocation() {
    return $this->venue;
  }

  public function getStartDate() {
    $timestamp = $this->getTimeStamp();

    return static::formatDateTime($timestamp, $this->hasEndTime());
  }

  public function hasEndTime() {
    list($date, $time) = explode('T', $this->datetime);

    return ($time !== '00:00:00');
  }

  public function getEndDate() {
    if ($this->hasEndTime()) {
      $start_date = $this->getTimeStamp();
      $end_date = strtotime('+2 hours', $start_date);

      return static::formatDateTime($end_date);
    }
    else {
      return '';
    }
  }

  /**
   * @return false|int
   */
  private function getTimeStamp() {
    return strtotime($this->datetime);
  }

}

class Renderer {
  public $twig;

  public function __construct() {
    // Load the twig renderer.
    $loader = new Twig_Loader_Filesystem(__DIR__ . '/../templates');
    $this->twig = new Twig_Environment($loader);
  }

  public static function load() {
    static $instance;

    if (!isset($instance)) {
      $instance = new static();
    }

    return $instance;
  }

}
