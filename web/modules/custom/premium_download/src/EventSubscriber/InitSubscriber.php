<?php /**
 * @file
 * Contains \Drupal\premium_download\EventSubscriber\InitSubscriber.
 */

namespace Drupal\premium_download\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InitSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::REQUEST => ['onEvent', 0]];
  }

  public function onEvent() {
    if (defined('MAINTENANCE_MODE')) {
      return;
    }
    premium_download_goto();
  }

}
