<?php

namespace Drupal\docmagic_site\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 *
 * @Block(
 *   id = "login_cookie",
 *   admin_label = @Translation("Login cookie"),
 *   category = @Translation("Docmagic"),
 * )
 */
class LoginCookie extends BlockBase {

  const EVENT_CHECK = 'docmagic.login_cookie.check';

  /**
   * {@inheritdoc}
   */
  public function build() {
    \Drupal::service('page_cache_kill_switch')->trigger();
    
    $cookie = checkCookie();

    return array(
      '#markup' => $this->t('<script type="text/javascript">var dmcheckd6="@cookie";</script>', ['@cookie' => $cookie]),
      '#cache' => array(
        'max-age' => 0,
      )
    );
  }  
}

function checkCookie() {
  if (isset($_COOKIE['DMLOGGEDIN']) && $_COOKIE['DMLOGGEDIN'] == 'loggedin') {
      return "loggedin";
  }

  if (\Drupal::hasService('event_dispatcher')) {
      $event = new \Symfony\Component\EventDispatcher\GenericEvent();
      /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher */
      $eventDispatcher = \Drupal::service('event_dispatcher');
      $event = $eventDispatcher->dispatch(\Drupal\docmagic_site\Plugin\Block\LoginCookie::EVENT_CHECK, $event);
      if ($event->hasArgument('is_logged_in') && $event->getArgument('is_logged_in')) {
          return 'loggedin';
      }
  }

  return "false";
}
