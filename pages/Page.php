<?php

namespace KeyboardCowboy\CalDom\Pages;

use KeyboardCowboy\CalDom\Components\Renderer;
use KeyboardCowboy\CalDom\Components\ReportBuilder;

/**
 * Class Page
 *
 * @package KeyboardCowboy\CalDom
 */
class Page {
  // The page we're loading.
  private $page;

  public function __construct($page) {
    $this->page = $page;
  }


  public function pageTitle() {
    switch ($this->page) {
      case 'home':
        return 'Subscribe';

      case 'status':
        return 'Status Report';

      default:
        return 'Page not Found';
    }
  }

  public function vars() {
    return [];
  }

  public function content() {
    switch ($this->page) {
      case 'home':
        $renderer = Renderer::load();
        $output = ['<p>Calendar feeds are automatically updated from their source sites.</p>'];
        $output[] = '<p>Due to a recent redesign of the US Soccer website, calendar feeds are not working.  The best alternatives I have found so far are at <a href="https://foxsports.calreplyapp.com/">https://foxsports.calreplyapp.com/</a></p>';

        foreach (ReportBuilder::getReport() as $cal_info) {
          $cal_info['icon'] = @file_get_contents(DOCROOT . '/images/cal.svg');
          $cal_info['url'] = 'webcal://' . $_SERVER['HTTP_HOST'] . '/' . $cal_info['url'];
          $output[] = $renderer->twig->render('cal-download.twig', $cal_info);
        }

        return implode('<br />', $output);

      case 'status':
        $renderer = Renderer::load();
        $output = [];

        foreach (ReportBuilder::getReport() as $cal_info) {
          $cal_info['url'] = 'http://' . $_SERVER['HTTP_HOST'] . $cal_info['url'];
          $output[] = $renderer->twig->render('summary.twig', $cal_info);
        }

        return implode('<br />', $output);

      default:
        return '';
    }
  }

  /**
   * Render a page.
   *
   * @return string
   *   The rendered page.
   */
  public function render() {
    $renderer = Renderer::load();
    $vars = ['title' => $this->pageTitle()];
    $vars += $this->vars();

    $page_vars = [
      'head' => $renderer->twig->render('page--head.twig', $vars),
      'header' => $renderer->twig->render('page--header.twig', $vars),
      'main' => $this->content(),
      'footer' => $renderer->twig->render('page--footer.twig', $vars),
    ];

    return $renderer->twig->render('page.twig', $page_vars);
  }

}
