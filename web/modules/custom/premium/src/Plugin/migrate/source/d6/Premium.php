<?php

namespace Drupal\premium\Plugin\migrate\source\d6;

use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Drupal 6 premium source from database.
 *
 * @MigrateSource(
 *   id = "d6_premium",
 *   source_module = "premium"
 * )
 */
class Premium extends DrupalSqlBase {

    /**
     * {@inheritdoc}
     */
    public function query() {
        $query = $this->select('premium', 'p')
            ->fields('p', array(
                'nid',
                'start_ts',
                'end_ts',
            ));

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function fields() {
        $fields = array(
            'nid' => $this->t('Node ID'),
            'start_ts' => $this->t('Start TS'),
            'end_ts' => $this->t('End TS'),
        );
        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function getIds() {
        $ids['nid']['type'] = 'integer';
        return $ids;
    }

}
