<?php

/**
 * @file
 * Contains \Drupal\premium_download\Form\PremiumDownloadEditForm.
 */

namespace Drupal\premium_download\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class PremiumDownloadEditForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'premium_download_edit_form';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    require_once __DIR__.'/../../premium_download.admin.inc';

    // Merge default values.
    $premium_download = $form_state->getStorage();
    $pid = $this->getRouteMatch()->getParameter('premium_download');
    $premium_download += premium_download_load($pid);
    $premium_download += [
      'pid' => NULL,
      'filepath' => isset($_GET['filepath']) ? urldecode($_GET['filepath']) : '',
      'permission' => \Drupal::config('premium_download.settings')->get('premium_download_default_status'),
    ];
    $form_state->setStorage($premium_download);

    $form['pid'] = [
      '#type' => 'value',
      '#value' => $form_state->get([
        'pid'
        ]),
    ];
    $form['filepath'] = [
      '#type' => 'textfield',
      '#title' => t('From'),
      '#description' => t("Enter file path to PDFs (e.g. %example1 or %example2).", [
        '%example1' => 'media/docmagic/compliance/confidentiality.pdf',
        '%example2' => 'media/docmagic/compliance/compliance09/audit-rpt-2.pdf',
      ]),
      '#size' => 42,
      '#maxlength' => 255,
      '#default_value' => $form_state->get([
        'filepath'
        ]),
      '#required' => TRUE,
      '#element_validate' => [
        'premium_download_validate_filepath_field'
        ],
    ];

    $form['permission'] = [
      '#type' => 'select',
      '#title' => t('Select the '),
      '#description' => t('You can find more information about HTTP redirect status codes at <a href="@status-codes">@status-codes</a>.', [
        '@status-codes' => 'http://en.wikipedia.org/wiki/List_of_HTTP_status_codes#3xx_Redirection'
        ]),
      '#default_value' => $form_state->get(['permission']),
      '#options' => premium_download_permission_type_options(),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Save'),
    ];
    $form['cancel'] = array(
        '#value' => \Drupal\Core\Link::fromTextAndUrl(t('Cancel'), \Drupal\Core\Url::fromUserInput(isset($_REQUEST['destination']) ? $_REQUEST['destination'] : '/admin/build/premium-download'))->toString(),
      );


    return $form;
  }

  public function validateForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $premium_download = &$form_state->getValues();
    if (strrchr(strtoupper($premium_download['filepath']), '.') != '.PDF') {
      $form_state->setErrorByName('filepath', t('The file path needs to be PDF'));
    }
    if ($existing = premium_download_load_by_filepath($premium_download['filepath'])) {
      if ($premium_download['pid'] != $existing->pid) {
        // The "from" path should not conflict with another redirect
      $form_state->setErrorByName('filepath', t('The filepath path %filepath  already exist. Do you want to <a href="@edit-page">edit the existing premium download</a>?', array('%filepath' => $premium_download['filepath'], '@edit-page' => \Drupal\Core\Url::fromUserInput('/admin/build/premium-download/edit/'. $existing->pid)->toString())));

      }
    }
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    premium_download_save($form_state->getValues());
    drupal_set_message(t('The redirect has been saved.'));
    $form_state->set(['redirect'], 'admin/build/premium-download');
  }

}
?>
