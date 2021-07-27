<?php

namespace Drupal\docmagic_site\EventSubscriber;

use Drupal\docmagic_site\Plugin\Block\LoginCookie;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class LoginCookieSubscriber implements EventSubscriberInterface
{
    public function onLoginCookieCheck(GenericEvent $event)
    {
        // @see \Drupal\docmagic_site\Plugin\Block\LoginCookie checkCookie() function
        if (\Drupal::hasContainer() && \Drupal::currentUser()->isAuthenticated()) {
            $event->setArgument('is_logged_in', true);
        }
    }

    public static function getSubscribedEvents() {
        $events[LoginCookie::EVENT_CHECK][] = array('onLoginCookieCheck');
        return $events;
    }
}
