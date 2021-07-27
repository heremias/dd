<?php

/**
 * @file
 * Contains \Drupal\premium\Form\PremiumSettings.
 */

namespace Drupal\premium\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class PremiumSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'premium_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('premium.settings');

    foreach (Element::children($form) as $variable) {
      $config->set($variable, $form_state->getValue($form[$variable]['#parents']));
      if ('premium_message' == $variable) {
        $val = $form_state->getValue($form[$variable]['#parents']);
        if (isset($val['value']) && isset($val['format'])) {
          $config->set($variable, $val['value']);
          $config->set('premium_format', $val['format']);
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
    return ['premium.settings'];
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $form = [];
//    $form['#validate'] = ['premium_settings_validate'];

    $premium_types = [];
    foreach (node_type_get_names() as $type => $value) {
      if (_premium_node($type)) {
        $premium_types[$type] = $type;
      }
      else {
        $premium_types[$type] = 0;
      }
    }

    $form['premium_node_types'] = [
      '#type' => 'checkboxes',
      '#options' => node_type_get_names(),
      '#title' => t('Node Types'),
      '#default_value' => $premium_types,
    ];

    // timeframe for premium + update existing nodes
    $form['premium_mode'] = [
      '#type' => 'radios',
      '#title' => t('Mode'),
      '#default_value' => \Drupal::config('premium.settings')->get('premium_mode'),
      '#options' => [
        '0' => t('Premium items are permanently restricted'),
        'archive' => t('Protect archives only: Items switch to premium status after a specified period'),
        'latest' => t('Protect latest content only: Items switch to non-premium status after a specified period'),
      ],
    ];

    $options = (range(0, 15));
    unset($options[0]);
    $form['premium_time_count'] = [
      '#type' => 'select',
      '#title' => t('Count'),
      '#default_value' => \Drupal::config('premium.settings')->get('premium_time_count'),
      '#options' => $options,
    ];

    $form['premium_time_unit'] = [
      '#type' => 'select',
      '#title' => t('Unit'),
      '#default_value' => \Drupal::config('premium.settings')->get('premium_time_unit'),
      '#options' => [
        'D' => t('Days'),
        'W' => t('Weeks'),
        'M' => t('Months'),
        'Y' => t('Years'),
      ],
    ];

    $form['premium_bulk_update'] = [
      '#type' => 'checkbox',
      '#title' => t('Bulk update premium status'),
      '#description' => t('Update all existing nodes of the types selected above with the new premium settings.'),
    ];

    $form['premium_message'] = [
      //'#type' => 'textarea',
      '#type' => 'text_format',
      '#format' => \Drupal::config('premium.settings')->get('premium_format'),
      '#title' => t('Premium body text'),
      '#default_value' => \Drupal::config('premium.settings')->get('premium_message'),
      '#cols' => 60,
      '#rows' => 15,
      '#description' => t('When a visitor doesn\'t have access to a premium item they will see this message instead of its full text'),
    ];

    if (\Drupal::moduleHandler()->moduleExists('filter')) {
      // @TODO change filter_form()
//      $form['premium_format'] = filter_form(\Drupal::config('premium.settings')->get('premium_format'), NULL, [
//        'premium_format'
//        ]);
    }
    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $count = $form_state->getValue(['premium_time_count']);
    $unit = $form_state->getValue(['premium_time_unit']);
    $mode = $form_state->getValue(['premium_mode']);
    $types = $form_state->getValue(['premium_node_types']);

    foreach ($types as $type => $premium) {
      $node_options = \Drupal::config('node.type.'.$type)->get('options') ?: array();
      if (in_array('premium', $node_options)) {
        $premium_key = array_search('premium', $node_options);
        unset($node_options[$premium_key]);
      }
      if ($types[$type]) {
        $node_options = array_merge($node_options, ['premium']);
        $premium_types[] = $types[$type];
      }
      \Drupal::configFactory()->getEditable('node.type.'.$type)->set('options', $node_options)->save();
    }

    /*
    // ndr - prevent mass delete for now
    if ($form_state->getValue(['premium_bulk_update'])) {
      db_query("DELETE from {premium}");

      foreach ($premium_types as $type) {
        $start = $end = 0;
        _premium_offset(0, $start, $end, $mode, $count, $unit);
        // Apply the timestamp delta's to the node's created date.
        if ($start) {
          $start = 'created + ' . (int) $start;
        }
        if ($end) {
          $end = 'created + ' . (int) $end;
        }

        db_query("INSERT INTO {premium} (nid, start_ts, end_ts)
        SELECT nid, $start, $end FROM {node} WHERE type = ?", array($type));
      }
    }
    */
  }

}
?>
