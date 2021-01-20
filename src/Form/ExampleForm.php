<?php

namespace Drupal\e3_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Provides a E3 Form form.
 */
class ExampleForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'e3_form_example';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Grab storage service and get the whatsits.
    $storage = \Drupal::service('entity_type.manager')->getStorage('node');

    $whatsits = $storage->loadByProperties([
      'type' => 'whatsits',
      'status' => 1,
    ]);

    // Storing titles and default values, logic for lack of whatsits.
    $whatsit_array = [];
    $whatsit_disable = TRUE;

    // Assemble array for field options
    if ($whatsits) {
      foreach ($whatsits as $node) {
        $optionItem = '';
        if ( $node->get('field_show_hidden_field_')->value ) {
          $optionItem = '(Special) ';
        }
        $optionItem .= $node->getTitle();
        $whatsit_array[$node->id()] = $optionItem;
      }
      $whatsit_disable = FALSE;
    } else {
      array_push($whatsit_array, "No Whatsits found");
    }

    // Form fields
    $form['one_plaintext_field'] = [
      '#type'      => 'textarea',
      '#title'     => $this->t('One Plaintext Field'),
      '#required'  => TRUE,
    ];

    $form['whatsit_list'] = [
      '#type'     => 'select',
      '#title'    => ('Whatsits'),
      '#disabled' => $whatsit_disable,
      '#options'  => $whatsit_array,
      '#ajax'     => [
        'callback'  => [$this, 'specialWhatsitCallback'],
        'event'     => 'change',
        'wrapper'   => 'ajax-edit',
      ],
    ];

    // This field renders as hidden and disabled in order to be replaced when needed by specialWhatsitCallback (below)
    $form['super_special_checkbox'] = [
      '#type' => 'hidden',
      '#disabled' => TRUE,
      '#prefix' => '<div id="ajax-edit">',
      '#suffix' => '</div>',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addStatus($this->t('The message has been sent.'));
    $form_state->setRedirect('<front>');
  }

  /**
   * AJAX callback for displaying the hidden field for special whatsits
   */
  public function specialWhatsitCallback(array &$form, FormStateInterface $form_state) {
    if ($whatsit = $form_state->getValue('whatsit_list')) {
      $entity = \Drupal::service('entity_type.manager')->getStorage('node')->load($whatsit);

      if ($entity->get('field_show_hidden_field_')->value == 1) {
        $form['super_special_checkbox'] = [
          '#type' => 'checkbox',
          '#title' => ('Are you sure you want to use this Super Special Whatsit?'),
          '#prefix' => '<div id="ajax-edit">',
          '#suffix' => '</div>',
        ];
      }
    }
    return $form['super_special_checkbox'];
  }

}
