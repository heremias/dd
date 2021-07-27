<?php

/**
 * @file
 * Contains \Drupal\premium_basic\Form\PremiumBasicSettings.
 */

namespace Drupal\premium_basic\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class PremiumBasicSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'premium_basic_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('premium_basic.settings');

    foreach (Element::children($form) as $variable) {
      $config->set($variable, $form_state->getValue($form[$variable]['#parents']));
      if ('premium_basic_message' == $variable) {
        $val = $form_state->getValue($form[$variable]['#parents']);
        if (isset($val['value']) && isset($val['format'])) {
          $config->set($variable, $val['value']);
          $config->set('premium_basic_format', $val['format']);
        }
      }
    }
    $config->clear('actions');
    $config->clear('form_build_id');
    $config->clear('form_id');
    $config->clear('form_token');
    $config->save();

    if (method_exists($this, '_submitForm')) {
      $this->_submitForm($form, $form_state);
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['premium_basic.settings'];
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $form = [];
//    $form['#validate'] = ['premium_basic_settings_validate'];

    $premium_basic_types = [];
    foreach (node_type_get_names() as $type => $value) {
      if (_premium_basic_node($type)) {
        $premium_basic_types[$type] = $type;
      }
      else {
        $premium_basic_types[$type] = 0;
      }
    }

    $form['premium_basic_node_types'] = [
      '#type' => 'checkboxes',
      '#options' => node_type_get_names(),
      '#title' => t('Node Types'),
      '#default_value' => $premium_basic_types,
    ];

    // timeframe for premium_basic + update existing nodes
    $form['premium_basic_mode'] = [
      '#type' => 'radios',
      '#title' => t('Mode'),
      '#default_value' => \Drupal::config('premium_basic.settings')->get('premium_basic_mode'),
      '#options' => [
        '0' => t('Premium_basic items are permanently restricted'),
        'archive' => t('Protect archives only: Items switch to premium_basic status after a specified period'),
        'latest' => t('Protect latest content only: Items switch to non-premium_basic status after a specified period'),
      ],
    ];

    $options = (range(0, 15));
    unset($options[0]);
    $form['premium_basic_time_count'] = [
      '#type' => 'select',
      '#title' => t('Count'),
      '#default_value' => \Drupal::config('premium_basic.settings')->get('premium_basic_time_count'),
      '#options' => $options,
    ];

    $form['premium_basic_time_unit'] = [
      '#type' => 'select',
      '#title' => t('Unit'),
      '#default_value' => \Drupal::config('premium_basic.settings')->get('premium_basic_time_unit'),
      '#options' => [
        'D' => t('Days'),
        'W' => t('Weeks'),
        'M' => t('Months'),
        'Y' => t('Years'),
      ],
    ];

    $form['premium_basic_bulk_update'] = [
      '#type' => 'checkbox',
      '#title' => t('Bulk update premium_basic status'),
      '#description' => t('Update all existing nodes of the types selected above with the new premium_basic settings.'),
    ];

    $form['premium_basic_message'] = [
      //'#type' => 'textarea',
      '#type' => 'text_format',
      '#format' => \Drupal::config('premium_basic.settings')->get('premium_basic_format'),
      '#title' => t('Premium_basic body text'),
      '#default_value' => \Drupal::config('premium_basic.settings')->get('premium_basic_message'),
      '#cols' => 60,
      '#rows' => 15,
      '#description' => t('When a visitor doesn\'t have access to a premium_basic item they will see this message instead of its full text'),
    ];

    if (\Drupal::moduleHandler()->moduleExists('filter')) {
      // @TODO change filter_form()
//      $form['premium_basic_format'] = filter_form(\Drupal::config('premium_basic.settings')->get('premium_basic_format'), NULL, [
//        'premium_basic_format'
//        ]);
    }
    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $count = $form_state->getValue(['premium_basic_time_count']);
    $unit = $form_state->getValue(['premium_basic_time_unit']);
    $mode = $form_state->getValue(['premium_basic_mode']);
    $types = $form_state->getValue(['premium_basic_node_types']);

    foreach ($types as $type => $premium_basic) {
      $node_options = \Drupal::config('node.type.'.$type)->get('options') ?: array();
      if (in_array('premium_basic', $node_options)) {
        $premium_basic_key = array_search('premium_basic', $node_options);
        unset($node_options[$premium_basic_key]);
      }
      if ($types[$type]) {
        $node_options = array_merge($node_options, ['premium_basic']);
        $premium_basic_types[] = $types[$type];
      }
      \Drupal::configFactory()->getEditable('node.type.'.$type)->set('options', $node_options)->save();
    }

    /*
    // ndr - prevent mass delete for now
    if ($form_state->getValue(['premium_basic_bulk_update'])) {
      db_query("DELETE from {premium_basic}");

      foreach ($premium_basic_types as $type) {
        $start = $end = 0;
        _premium_basic_offset(0, $start, $end, $mode, $count, $unit);
        // Apply the timestamp delta's to the node's created date.
        if ($start) {
          $start = 'created + ' . (int) $start;
        }
        if ($end) {
          $end = 'created + ' . (int) $end;
        }

        db_query("INSERT INTO {premium_basic} (nid, start_ts, end_ts)
        SELECT nid, $start, $end FROM {node} WHERE type = ?", array($type));
      }
    }
    */
  }

}
?>
