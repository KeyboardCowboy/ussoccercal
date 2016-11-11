<?php

require_once __DIR__ . '/Renderer.php';
require_once __DIR__ . '/SoccerCal.php';

abstract class Page {
  abstract public function pageTitle();

  abstract public function vars();

  abstract public function content();

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
