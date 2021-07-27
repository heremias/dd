<?php

namespace Drupal\docmagic_migrate\EventSubscriber;

use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\migrate\Event\MigratePreRowSaveEvent;
//use Drupal\replication\Event\ReplicationEvent;
//use Drupal\replication\Event\ReplicationEvents;
use Drupal\migrate\MigrateSkipRowException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MigrateSubscriber implements EventSubscriberInterface
{
    public function onPreReplication(/*ReplicationEvent*/ $event)
    {
        if (!defined('SKIP_MIGRATION_PHP_EVAL')) {
            // importing with the "deploy" module seems to serialize, pre-render, and process the text
            // so skip php_code format evaluation to avoid halting migrations
            // @see \Drupal\docmagic_migrate\Mock\PhpDatabaseContentMock::php_eval()
            define('SKIP_MIGRATION_PHP_EVAL', true);
        }
    }

    public function onPreImport(MigrateImportEvent $event)
    {
        if (!defined('SKIP_MIGRATION_PHP_EVAL')) {
            // importing with the "deploy" module seems to serialize, pre-render, and process the text
            // so skip php_code format evaluation to avoid halting migrations
            // @see \Drupal\docmagic_migrate\Mock\PhpDatabaseContentMock::php_eval()
            define('SKIP_MIGRATION_PHP_EVAL', true);
        }
    }

    public function updateBlockTheme(MigratePreRowSaveEvent $event)
    {
        $migration = $event->getMigration();
        $row = $event->getRow();

        if ('d6_block' == $migration->id()) {
            $replaceThemes = array(
                'bluemarine' => 'docmagic',
                'chameleon' => 'docmagic',
                'garland' => 'docmagic',
                'plain' => 'docmagic',
                'pushbutton' => 'docmagic',
            );
            $theme = $row->getSourceProperty('theme');

            if (isset($replaceThemes[$theme])) {
                $row->setDestinationProperty('theme', $replaceThemes[$theme]);
            }
        }
    }

    public function updateTaxonomyVocabulary(MigratePreRowSaveEvent $event)
    {
        $migration = $event->getMigration();
        $row = $event->getRow();

        if ('d6_taxonomy_vocabulary' == $migration->id()) {
            $vid = $row->getDestinationProperty('vid');

            if ('faq_' == $vid) {
                $row->setDestinationProperty('vid', 'faq');
            }
        }
    }

    public function updateUrlAlias(MigratePreRowSaveEvent $event)
    {
        $migration = $event->getMigration();
        $row = $event->getRow();

        if ('d6_url_alias' == $migration->id()) {
            throw new MigrateSkipRowException();
        }
    }

    public function createUrlAliasFromEntity(MigratePostRowSaveEvent $event)
    {
        if (!\Drupal::hasService('path.alias_storage')) {
            return;
        }
        /** @var \Drupal\Core\Path\AliasStorage $aliasStorage */
        $aliasStorage = \Drupal::service('path.alias_storage');

        $migration = $event->getMigration();
        $row = $event->getRow();

        if (false !== strpos($migration->id(), 'd6_node')) {
            $oldNodeId = $row->getSourceProperty('nid');
            $newNodeId = reset($event->getDestinationIdValues());
            if (!is_numeric($newNodeId)) {
                return;
            }

            /** @var \Drupal\node\Plugin\migrate\source\d6\Node $sourcePlugin */
            $sourcePlugin = $migration->getSourcePlugin();

            // @see \Drupal\path\Plugin\migrate\source\d6\UrlAlias
            $options['fetch'] = \PDO::FETCH_ASSOC;
            /** @var \Drupal\Core\Database\Query\Select $query */
            $query = $sourcePlugin->getDatabase()->select('url_alias', 'ua', $options)->fields('ua')->orderBy('pid');
            $orGroup = $query->orConditionGroup();
            $orGroup->condition('src', 'node/'.$oldNodeId);
            $orGroup->condition('src', 'node/'.$oldNodeId.'/%', 'LIKE');
            $query->condition($orGroup);

            $statement = $query->execute();
            $results = $statement->fetchAll();

            foreach ($results as $oldUrlAlias) {
                $newSource = '/'.preg_replace('`node/\d+`', 'node/'.$newNodeId, $oldUrlAlias['src']);
                $newDest = '/'.$oldUrlAlias['dst'];
                $aliasStorage->save($newSource, $newDest);
            }
        }

        if (false !== strpos($migration->id(), 'd6_user')) {
            $oldUserId = $row->getSourceProperty('uid');
            $newUserId = reset($event->getDestinationIdValues());
            if (!is_numeric($newUserId)) {
                return;
            }

            /** @var \Drupal\node\Plugin\migrate\source\d6\Node $sourcePlugin */
            $sourcePlugin = $migration->getSourcePlugin();

            // @see \Drupal\path\Plugin\migrate\source\d6\UrlAlias
            $options['fetch'] = \PDO::FETCH_ASSOC;
            /** @var \Drupal\Core\Database\Query\Select $query */
            $query = $sourcePlugin->getDatabase()->select('url_alias', 'ua', $options)->fields('ua')->orderBy('pid');
            $orGroup = $query->orConditionGroup();
            $orGroup->condition('src', 'user/'.$oldUserId);
            $orGroup->condition('src', 'user/'.$oldUserId.'/%', 'LIKE');
            $query->condition($orGroup);

            $statement = $query->execute();
            $results = $statement->fetchAll();

            foreach ($results as $oldUrlAlias) {
                $newSource = '/'.preg_replace('`user/\d+`', 'user/'.$newUserId, $oldUrlAlias['src']);
                $newDest = '/'.$oldUrlAlias['dst'];
                $aliasStorage->save($newSource, $newDest);
            }
        }

        if (false !== strpos($migration->id(), 'd6_taxonomy_term')) {
            $oldTermId = $row->getSourceProperty('tid');
            $newTermId = reset($event->getDestinationIdValues());
            if (!is_numeric($newTermId)) {
                return;
            }

            /** @var \Drupal\node\Plugin\migrate\source\d6\Node $sourcePlugin */
            $sourcePlugin = $migration->getSourcePlugin();

            // @see \Drupal\path\Plugin\migrate\source\d6\UrlAlias
            $options['fetch'] = \PDO::FETCH_ASSOC;
            /** @var \Drupal\Core\Database\Query\Select $query */
            $query = $sourcePlugin->getDatabase()->select('url_alias', 'ua', $options)->fields('ua')->orderBy('pid');
            $orGroup = $query->orConditionGroup();
            $orGroup->condition('src', 'taxonomy/term/'.$oldTermId);
            $orGroup->condition('src', 'taxonomy/term/'.$oldTermId.'%', 'LIKE');
            $query->condition($orGroup);

            $statement = $query->execute();
            $results = $statement->fetchAll();

            foreach ($results as $oldUrlAlias) {
                $newSource = '/'.preg_replace('`taxonomy/term/\d+`', 'taxonomy/term/'.$newTermId, $oldUrlAlias['src']);
                $newDest = '/'.$oldUrlAlias['dst'];
                $aliasStorage->save($newSource, $newDest);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        $events[/*ReplicationEvents::PRE_REPLICATION*/'replication.pre_replication'][] = array('onPreReplication');
        $events[MigrateEvents::PRE_IMPORT][] = array('onPreImport');
        $events[MigrateEvents::PRE_ROW_SAVE][] = array('updateBlockTheme');
        $events[MigrateEvents::PRE_ROW_SAVE][] = array('updateTaxonomyVocabulary');
        $events[MigrateEvents::PRE_ROW_SAVE][] = array('updateUrlAlias');
        $events[MigrateEvents::POST_ROW_SAVE][] = array('createUrlAliasFromEntity');
        return $events;
    }
}
