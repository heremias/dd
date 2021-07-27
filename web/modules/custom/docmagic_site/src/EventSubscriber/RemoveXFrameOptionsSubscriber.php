<?php

namespace Drupal\docmagic_site\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RemoveXFrameOptionsSubscriber implements EventSubscriberInterface {

    public function RemoveXFrameOptions(FilterResponseEvent $event) {
        $request = $event->getRequest();
        $uri = $request->getUri();
        if (false !== stripos($uri, '_partial/header') || false !== stripos($uri, '_partial/footer')) {
            $response = $event->getResponse();
            $response->headers->remove('X-Frame-Options');
        }
    }

    public static function getSubscribedEvents() {
        $events[KernelEvents::RESPONSE][] = array('RemoveXFrameOptions', -10);
        return $events;
    }
}
