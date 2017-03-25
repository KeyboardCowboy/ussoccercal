<?php

require_once __DIR__ . '/../src/SoccerCal.php';

use \src\Page\Page;

/**
 * Unit tests for SoccerCal class.
 */
class SoccerCalTest extends PHPUnit_Framework_TestCase {
  private $soccerCal;

  public function setUp() {
  }

  public function testExtractData() {
    $this->soccerCal->extractData();
    $no=0;
  }

  public function testRenderCal() {
    Page::parseArgs();
  }

}

class SoccerCalMock extends SoccerCal {

}
