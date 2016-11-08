<?php

require_once __DIR__ . '/../src/SoccerCal.php';

/**
 * Unit tests for SoccerCal class.
 */
class SoccerCalTest extends PHPUnit_Framework_TestCase {
  private $soccerCal;

  public function setUp() {
    $this->soccerCal = new SoccerCalMock('mnt');
  }

  public function testExtractData() {
    // $this->soccerCal->extractData();
    $no=0;
  }

  public function testRenderCal() {
    $this->soccerCal->render();
  }

}

class SoccerCalMock extends SoccerCal {

}
