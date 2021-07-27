<?php

/**
 * @file
 * Contains \Drupal\premium_download\Form\PremiumDownloadAdminFile.
 */

namespace Drupal\premium_download\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class PremiumDownloadAdminFile extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'premium_download_admin_file';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    require_once __DIR__.'/../../premium_download.admin.inc';

    //if (!$form_state->getValue(['operation']) && !$form_state->getValue(['confirm'])) {
    //  return premium_download_admin_file_update_confirm($form, $form_state->getValue(['operation']), array_filter($form_state->getValue(['pids'])));
    //}

    // Get filter key.
    $keys = func_get_args();
    array_shift($keys); // Offset the $form parameter.
    array_shift($keys); // Offset the $form_state parameter.
    $keys = implode('/', $keys);

    // Add the local actions and filter form.
    $form['actions'] = [
      '#type' => 'markup',
      '#value' => premium_download_local_actions(),
    ];
    $form['filter'] = premium_download_filter_form($keys);

    // Build the 'Update options' form.
    $form['options'] = [
      '#type' => 'fieldset',
      '#title' => t('Update options'),
      '#prefix' => '<div class="container-inline">',
      '#suffix' => '</div>',
      '#access' => \Drupal::currentUser()->hasPermission('access premium download admin') && \Drupal::moduleHandler()->moduleExists('elements'),
    ];
    $options = [];
    foreach (\Drupal::moduleHandler()->invokeAll('premium_download_operations') as $key => $operation) {
      $options[$key] = $operation['action'];
    }
    $form['options']['operation'] = [
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => 'delete',
    ];
    $form['options']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Update'),
      '#validate' => [
        'premium_download_admin_file_update_validate'
        ],
      '#submit' => ['premium_download_admin_file_update_submit'],
    ];

    // Apply the filter conditions.
    $query = [
      'conditions' => [],
      'args' => [],
      'limit' => 50,
    ];
    premium_download_filter_query($query, $keys);

    $form['pids'] = premium_download_list_files($query, [], TRUE);
    //$form['pager'] = array(
    //    '#type' => 'markup',
    //    '#value' => \Drupal::theme()->render('pager', NULL, 50, 0),
    //  );

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
      // TODO: Implement submitForm() method.
  }

}
?>
