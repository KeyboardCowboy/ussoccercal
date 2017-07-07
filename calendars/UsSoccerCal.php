<?php
/**
 * @file
 * Build calendars for US Soccer.
 */

namespace KeyboardCowboy\CalDom\Calendars;

use CalDom\Event\Event;

/**
 * Class USSoccerCal
 *
 * @package USMNTCal
 */
class USSoccerCal extends CalDomCal {

  /**
   * {@inheritdoc}
   */
  public function processTimezone(array $values, Event $event) {
    $parts = explode(' ', $values[0]);
    $tz = array_pop($parts);

    return $tz;
  }

}
