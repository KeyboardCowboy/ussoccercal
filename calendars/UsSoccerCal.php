<?php
/**
 * @file
 * Build calendars for US Soccer.
 */

namespace KeyboardCowboy\CalDom\Calendars;

use CalDom\Calendar\Calendar;
use CalDom\Event\Event;

/**
 * Class USSoccerCal
 *
 * @package USMNTCal
 */
class USSoccerCal extends Calendar {

  /**
   * {@inheritdoc}
   */
  public function processTimezone(array $values, Event $event) {
    $parts = explode(' ', $values[0]);
    $tz = array_pop($parts);

    return $tz;
  }

}
