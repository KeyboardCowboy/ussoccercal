<?php
/**
 * @file
 * Contains \CalDomCal.
 */

namespace KeyboardCowboy\CalDom\Calendars;

use CalDom\Calendar\Calendar;
use CalDom\Event\Event;

/**
 * Class CalDomCal
 *
 * @package KeyboardCowboy\CalDom\Calendars
 */
class CalDomCal extends Calendar {

  /**
   * {@inheritdoc}
   */
  public function processDescription(array $values, Event $event) {
    // There must be a better way to handle adding links to the description.
    $values[] = '<a href="http://' . $_SERVER['HTTP_HOST'] . '">US Soccer Calendars</a>';
    return parent::processDescription($values, $event);
  }

}
