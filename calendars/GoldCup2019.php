<?php
/**
 * @file
 * Build calendars for the Gold Cup tournament.
 */

namespace KeyboardCowboy\CalDom\Calendars;

use CalDom\Event\Event;
use Artack\DOMQuery\DOMQuery;

/**
 * Class GoldCup2017
 */
class GoldCup2019 extends CalDomCal {

  /**
   * {@inheritdoc}
   */
  protected function prepareDocument(DOMQuery $document) {
    foreach ($document->find('table.genericTable') as $table) {
      // Remove the header row from each table.
      $table->find('tr')->get(0)->remove();

      /** @var DOMQuery $row */
      foreach ($table->find('tr') as $row) {
        $date_cell = $row->find('td')->get(0);
        $date      = strip_tags($date_cell->getInnerHtml()) . ' 2019';

        $time_cell  = $row->find('td')->get(2);
        $time_parts = explode('|', strip_tags($time_cell->getInnerHtml()));
        $time_clean = trim($time_parts[0]);

        $time_parts = explode(' ', $time_clean);
        $ampm = array_pop($time_parts);
        $time_parts_2 = explode(':', $time_parts[0]);

        $hour = $ampm == 'p.m.' ? (int) $time_parts_2[0] + 12 : $time_parts_2[0];
        $min = isset($time_parts_2[1]) ? $time_parts_2[1] : 0;

        $time = "{$hour}:{$min}:00";

        $datetime_string = $date . 'T' . $time;
        $timestamp = strtotime($datetime_string);

        // Create a datetime stamp.
        $row->find('td')->get(0)->setAttribute('content', date('c', $timestamp));
      }
    }

    return parent::prepareDocument($document);
  }

  /**
   * {@inheritdoc}
   */
  public function processTimezone(array $values, Event $event) {
    return 'ET';
  }

  /**
   * {@inheritdoc}
   */
  public function processUrl(array $values, Event $event) {
    return 'https://www.ussoccer.com/mens-national-team/tournaments/2019-concacaf-gold-cup#tab-4';
  }

}
