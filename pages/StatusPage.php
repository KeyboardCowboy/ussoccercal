<?php

namespace KeyboardCowboy\CalDom\Pages;

use KeyboardCowboy\CalDom\Components\Page;

class StatusPage extends Page {
  public function pageTitle() {
    return 'Status Report';
  }

  public function vars() {
    return [];
  }

  public function content() {
    // return SoccerCal::renderStatusReports();
  }

}

