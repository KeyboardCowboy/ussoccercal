<?php

class HomePage extends Page {
  public function pageTitle() {
    return 'Subscribe';
  }

  public function vars() {
    return [];
  }

  public function content() {
    return SoccerCal::renderCalendars();
  }

}


