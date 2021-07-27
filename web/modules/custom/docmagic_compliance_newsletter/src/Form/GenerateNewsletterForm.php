<?php

namespace Drupal\docmagic_compliance_newsletter\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\docmagic_compliance_newsletter\Service\ComplianceNewsletter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GenerateNewsletterForm.
 */
class GenerateNewsletterForm extends FormBase {

  /**
   * Drupal\docmagic_compliance_newsletter\Service\ComplianceNewsletter definition.
   *
   * @var \Drupal\docmagic_compliance_newsletter\Service\ComplianceNewsletter
   */
  protected $complianceNewsletter;

  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new GenerateNewsletterForm object.
   */
  public function __construct(
    ComplianceNewsletter $complianceNewsletter,
    MessengerInterface $messenger
  ) {
    $this->complianceNewsletter = $complianceNewsletter;
    $this->messenger = $messenger;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('docmagic_compliance_newsletter.compliance_newsletter'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'generate_newsletter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $months = $this->complianceNewsletter->getMonths();
    $years = $this->complianceNewsletter->getYears();
    $monthOptions = array_combine($months, $months);
    $yearOptions = array_combine($years, $years);
    $now = new \DateTime();

    $form['newsletter_month'] = [
      '#type' => 'select',
      '#title' => $this->t('Newsletter Month'),
      '#options' => $monthOptions,
      '#default_value' => $now->format('F'),
    ];
    $form['newsletter_year'] = [
      '#type' => 'select',
      '#title' => $this->t('Newsletter Year'),
      '#options' => $yearOptions,
      '#default_value' => $now->format('Y'),
    ];
    $form['newsletter_regenerate'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Regenerate'),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $month = $form_state->getValue('newsletter_month');
    $year = $form_state->getValue('newsletter_year');
    $regenerate = $form_state->getValue('newsletter_regenerate') ? true : false;
    $issueNode = $this->complianceNewsletter->generateComplianceNewsletter($year, $month, $regenerate);

    if ($issueNode) {
      $form_state->setRedirectUrl($issueNode->toUrl('edit-form'));
      $this->messenger->addMessage(
        $this->t(sprintf('The Compliance Newsletter for %s %s has been generated.', $month, $year)),
        MessengerInterface::TYPE_STATUS
      );
    } else {
      $this->messenger->addMessage(
        $this->t(sprintf('There was an error creating a Compliance Newsletter. There may already be a newsletter for %s %s.', $month, $year)),
        MessengerInterface::TYPE_ERROR
      );
    }
  }

}
