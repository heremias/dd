<?php

namespace Drupal\premium_download\Plugin\migrate\destination;

use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\migrate\Plugin\migrate\destination\DestinationBase;

/**
 * Drupal 8 premium_download destination.
 *
 * @MigrateDestination(
 *   id = "premium_download"
 * )
 */
class PremiumDownload extends DestinationBase {

    /**
     * {@inheritdoc}
     */
    public function getIds() {
        return [
            'pid' => [
                'type' => 'integer',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fields(MigrationInterface $migration = NULL) {
        return [
            'pid' => 'ID',
            'filepath' => 'File path',
            'permission' => 'Permission',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function import(Row $row, array $old_destination_id_values = []) {
        $pid = $row->getDestinationProperty('pid');
        $filepath = $row->getDestinationProperty('filepath');
        $permission = $row->getDestinationProperty('permission');

        db_query('DELETE FROM {premium_download} WHERE pid = ?', array($pid));
        db_query('INSERT INTO {premium_download} (pid, filepath, permission) 
                  VALUES ( ?, ?, ? )', array($pid, $filepath, $permission));

        return [$pid];
    }

}
