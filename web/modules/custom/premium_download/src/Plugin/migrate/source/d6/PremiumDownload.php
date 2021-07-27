<?php

namespace Drupal\premium_download\Plugin\migrate\source\d6;

use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Drupal 6 premium download source from database.
 *
 * @MigrateSource(
 *   id = "d6_premium_download",
 *   source_module = "premium_download"
 * )
 */
class PremiumDownload extends DrupalSqlBase {

    /**
     * {@inheritdoc}
     */
    public function query() {
        $query = $this->select('premium_download', 'p')
            ->fields('p', array(
                'pid',
                'filepath',
                'permission',
            ));

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function fields() {
        $fields = array(
            'pid' => $this->t('ID'),
            'filepath' => $this->t('File path'),
            'permission' => $this->t('Permission'),
        );
        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function getIds() {
        $ids['pid']['type'] = 'integer';
        return $ids;
    }

}
