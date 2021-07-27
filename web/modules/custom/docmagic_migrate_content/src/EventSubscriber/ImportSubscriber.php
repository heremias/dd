<?php

namespace Drupal\docmagic_migrate_content\EventSubscriber;

use Drupal\default_content\Event\DefaultContentEvents;
use Drupal\default_content\Event\ImportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ImportSubscriber implements EventSubscriberInterface
{
    const UUID_PAGE_HOME = 'a667cb36-8ad0-4017-9549-0586e52c8332';

    public function onImportComplete(ImportEvent $event)
    {
        $entities = $event->getImportedEntities();
        $pages = \Drupal::configFactory()->get('system.site')->get('page');

        if (isset($pages['front'])
            && isset($entities[static::UUID_PAGE_HOME])
            && is_object($entities[static::UUID_PAGE_HOME])
        ) {
            /** @var \Drupal\node\Entity\Node $homepage */
            $homepage = $entities[static::UUID_PAGE_HOME];

            $pages['front'] = \Drupal::service('path.alias_manager')->getPathByAlias($homepage->url());

            \Drupal::configFactory()->getEditable('system.site')->set('page', $pages)->save();
        }
    }

    public static function getSubscribedEvents()
    {
        $events[DefaultContentEvents::IMPORT][] = array('onImportComplete');
        return $events;
    }
}
