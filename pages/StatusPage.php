<?php

class StatusPage extends Page {
  public function pageTitle() {
    return 'Status Report';
  }

  public function vars() {
    return [];
  }

  public function content() {
    return SoccerCal::renderStatusReports();
  }

}

