<?php

namespace Drupal\premium\Plugin\migrate\destination;

use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\migrate\Plugin\migrate\destination\DestinationBase;

/**
 * Drupal 8 premium destination.
 *
 * @MigrateDestination(
 *   id = "premium"
 * )
 */
class Premium extends DestinationBase {

    /**
     * {@inheritdoc}
     */
    public function getIds() {
        return [
            'nid' => [
                'type' => 'integer',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fields(MigrationInterface $migration = NULL) {
        return [
            'nid' => 'Node ID',
            'start_ts' => 'Start TS',
            'end_ts' => 'End TS',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function import(Row $row, array $old_destination_id_values = []) {
        $nid = $row->getDestinationProperty('nid');
        $start_ts = $row->getDestinationProperty('start_ts');
        $end_ts = $row->getDestinationProperty('end_ts');

        db_query('DELETE FROM {premium} WHERE nid = ?', array($nid));
        db_query('INSERT INTO {premium} (nid, start_ts, end_ts) 
                  VALUES ( ?, ?, ? )', array($nid, $start_ts, $end_ts));

        return [$nid];
    }

}
