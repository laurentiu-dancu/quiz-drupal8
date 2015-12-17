<?php

/**
 * @file
 * Contains \Drupal\quiz\Form\AnswerTypeForm.
 */

namespace Drupal\quiz\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AnswerTypeForm.
 *
 * @package Drupal\quiz\Form
 */
class AnswerTypeForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $answer_type = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $answer_type->label(),
      '#description' => $this->t("Label for the Answer type."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $answer_type->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\quiz\Entity\AnswerType::load',
      ),
      '#disabled' => !$answer_type->isNew(),
    );

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $answer_type = $this->entity;
    $status = $answer_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Answer type.', [
          '%label' => $answer_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Answer type.', [
          '%label' => $answer_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($answer_type->urlInfo('collection'));
  }

}
