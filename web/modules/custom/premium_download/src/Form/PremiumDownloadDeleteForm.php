<?php

/**
 * @file
 * Contains \Drupal\premium_download\Form\PremiumDownloadDeleteForm.
 */

namespace Drupal\premium_download\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class PremiumDownloadDeleteForm extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'premium_download_delete_form';
  }

//  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $premium_download) {
//    $form['premium_download'] = [
//      '#type' => 'value',
//      '#value' => $premium_download,
//    ];
//    return confirm_form($form, t('Are you sure you want to delete the premium download for %filepath?', [
//      '%filepath' => $premium_download->get([
//        'filepath'
//        ])
//      ]), isset($_REQUEST['destination']) ? $_REQUEST['destination'] : 'admin/build/premium-download');
//  }
  public function buildForm(array $form, FormStateInterface $form_state)
  {
      $form['premium_download'] = [
        '#type' => 'value',
        '#value' => $this->getRouteMatch()->getParameter('premium_download'),
      ];

      return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    premium_download_delete($form_state->getValue(['premium_download'/*, 'pid'*/]));
    drupal_set_message(t('The premium download has been deleted.'));
    $form_state->set(['redirect'], 'admin/build/premium-download');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion()
  {
    return t('Are you sure you want to delete the premium download'./*' for %filepath'.*/'?', [
//        '%filepath' => $premium_download->get([
//            'filepath'
//        ])
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl()
  {
    return \Drupal\Core\Url::fromUserInput(isset($_REQUEST['destination']) ? $_REQUEST['destination'] : '/admin/build/premium-download');
  }

}
?>
