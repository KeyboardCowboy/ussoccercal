<?php
/**
 * @file
 * Build calendars for the Gold Cup tournament.
 */

namespace KeyboardCowboy\CalDom\Calendars;

use CalDom\Calendar\Calendar;
use CalDom\Event\Event;
use Artack\DOMQuery\DOMQuery;

/**
 * Class GoldCup2017
 */
class GoldCup2017 extends Calendar {

  /**
   * {@inheritdoc}
   */
  protected function prepareDocument(DOMQuery $document) {
    $date = '';

    // Add the date from the preceding table header to each match within the
    // subsequent tbody element.
    foreach ($document->find('table.wisbb_scheduleTable > *') as $table_element) {
      $tag = $table_element->getNodes()[0]->tagName;

      // If we hit a header, store the date.
      if ($tag === 'thead') {
        $date = $table_element->find('th')->getNodes()[0]->textContent;
        continue;
      }

      // If we hit a body, add the date to each match's time.
      if ($tag === 'tbody') {
        foreach ($table_element->find($this->calInfo['events']['starttime']['selector']) as &$time) {
          $time->replaceInner($date . ', ' . $time->getInnerHtml());
        }
      }
    }

    return parent::prepareDocument($document);
  }

  /**
   * {@inheritdoc}
   */
  public function processTimezone(array $values, Event $event) {
    $parts = explode(' ', $values[0]);
    $tz = array_pop($parts);

    return $tz;
  }

  /**
   * {@inheritdoc}
   */
  public function processStarttime(array $values, Event $event) {
    $parts = explode(' ', $values[0]);

    // Remove the timezone.
    array_pop($parts);
    $date = implode(' ', $parts);

    // Make sure the ToD is am or pm, and not the shorthand.
    if (substr($date, -1) !== 'm') {
      $date .= 'm';
    }

    // Break it down into a timestamp.
    return strtotime($date);
  }

}
