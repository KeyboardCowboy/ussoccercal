<?php
/**
 * @file
 * Contains \SoccerCalEvent.
 */

/**
 * Build events for US Soccer matches.
 */

/**
 * Class SoccerCalEvent.
 */
class SoccerCalEvent {
  // The SoccerCal object that manages the event.
  private $cal;

  // The matchup data.
  private $matchup;

  // The venue data.
  private $venue;

  // The 'watch' and extra info data.
  private $info;

  // The calculated datetime of the event.
  private $datetime;

  // Links gathered from within match data.
  private $links = [];

  // The URL to the match page.
  private $url;

  // Map data structure to DOM elements.
  protected static $field = [
    'date' => 0,
    'time' => 1,
    'matchup' => 2,
    'venue' => 3,
    'info' => 4,
  ];

  /**
   * SoccerCalEvent constructor.
   *
   * @param \SoccerCal $cal
   * @param \DOMNodeList $cells
   */
  public function __construct(SoccerCal $cal, DOMNodeList $cells) {
    $this->cal = $cal;
    $this->extractData($cells);
  }

  /**
   * Extract data from each cell in the schedule table.
   *
   * @param \DOMNodeList $cells
   */
  private function extractData(DOMNodeList $cells) {
    $this->datetime = $this->extractDateTime($cells->item(static::$field['date']));
    $this->matchup = $this->extractMatchup($cells->item(static::$field['matchup']));
    $this->venue = $this->extractVenue($cells->item(static::$field['venue']));
    $this->info = $this->extractInfo($cells->item(static::$field['info']));
  }

  /**
   * Extract the datetime from the schedule event.
   *
   * @param \DOMElement $cell
   *   The cell containing the date.
   *
   * @return string
   *   The datetime of the event in the format YYYYMMDDTHHMMSS.
   */
  private function extractDateTime(DOMElement $cell) {
    $attributes = $cell->getElementsByTagName('time')->item(0)->attributes;
    $datetime = $attributes->getNamedItem('datetime')->value;

    // The times from the website are listed in EST but still contain the UMT
    // indicator 'Z' so we remove it as we're setting the time zone manually.
    $datetime = str_replace('Z', '', $datetime);

    return $datetime;
  }

  /**
   * Extract matchup info.
   *
   * @param \DOMElement $cell
   *   The cell containing the matchup info.
   *
   * @return string
   *   The event matchup info (title).
   */
  private function extractMatchup(DOMElement $cell) {
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

  /**
   * Extract the venue information.
   *
   * @param \DOMElement $cell
   *   The cell containing the venue info.
   *
   * @return string
   *   The venue info for the event.
   */
  private function extractVenue(DOMElement $cell) {
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

  /**
   * Extract the 'watch' info.
   *
   * @param \DOMElement $cell
   *   The last cell in the table, containing the channel and other info.
   *
   * @return string
   *   Info from the 'watch' cell.
   */
  private function extractInfo(DOMElement $cell) {
    return '';
  }

  /**
   * Extract hyperlinks from within cell data.
   *
   * Some cells in the schedule table have complimentary links with structured
   * data.  We want to pull those links out and list them in the description.
   *
   * @param \DOMElement $cell
   *   A cell from the schedule table.
   */
  private function extractLinks(DOMElement $cell) {
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
   * Format the $datetime parameter for ical usage.
   *
   * @param string $datetime
   *   The event date in the required ical format.
   * @param bool $full
   *   FALSE to print the timestamp with just the date (time TBD).
   *
   * @return string
   *   The timestamp with timezone prepended.
   */
  protected function formatDateTime($datetime, $full = TRUE) {
    $date = $full ? date('Ymd\THis', $datetime) : date('Ymd', $datetime);

    return 'America/New_York:' . $date;
  }

  /**
   * Render the ical event.
   *
   * @return string
   *   The formatted ical event.
   */
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

  /**
   * Get the UID for the calendar event.
   *
   * @return string
   *   A unique ID for the event.
   */
  public function getUid() {
    return $this->cal->getTeam() . '-' . $this->getStartDate() . '@ussoccer.com';
  }

  /**
   * Get the URL for the event page.
   *
   * @return string
   *   The event page URL.
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * Get the ical Summary (title) field.
   *
   * @return string
   *   The matchup info to be used as the summary.
   */
  public function getSummary() {
    return $this->matchup;
  }

  /**
   * Get the ical description info.
   *
   * @return string
   *   Description info for the event.
   */
  public function getDescription() {
    $out = [];

    // Add extracted URLs.
    foreach ($this->links as $href => $text) {
      $url = SoccerCal::USSOCCER_HOSTNAME . $href;
      $out[] = $text . ':\n' . $url;
    }

    return implode('\n\n', $out);
  }

  /**
   * Get the venue/location info for the event.
   *
   * @return string
   *   The event venue.
   */
  public function getLocation() {
    return $this->venue;
  }

  /**
   * Get the start datestamp for the event.
   *
   * @return string
   *   The event start date in ical format.
   */
  public function getStartDate() {
    $timestamp = $this->getTimeStamp();

    return static::formatDateTime($timestamp, $this->hasEndTime());
  }

  /**
   * Determine whether the event has an end time.
   *
   * @return bool
   *   TRUE if an event time is set.
   */
  public function hasEndTime() {
    list($date, $time) = explode('T', $this->datetime);

    return ($time !== '00:00:00');
  }

  /**
   * Get the end datestamp for the event.
   *
   * We add two hours to the start date.
   *
   * @return string
   *   The event end date in ical format.
   */
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
   * Get a unix timestamp for the event datetime.
   *
   * @return false|int
   *   The unix timestamp of the event datetime.
   */
  private function getTimeStamp() {
    return strtotime($this->datetime);
  }

}

