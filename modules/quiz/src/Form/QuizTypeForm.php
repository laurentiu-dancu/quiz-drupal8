<?php

/**
 * @file
 * Contains \Drupal\quiz\Form\QuizTypeForm.
 */

namespace Drupal\quiz\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class QuizTypeForm.
 *
 * @package Drupal\quiz\Form
 */
class QuizTypeForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $quiz_type = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $quiz_type->label(),
      '#description' => $this->t("Label for the Quiz type."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $quiz_type->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\quiz\Entity\QuizType::load',
      ),
      '#disabled' => !$quiz_type->isNew(),
    );

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $quiz_type = $this->entity;
    $status = $quiz_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Quiz type.', [
          '%label' => $quiz_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Quiz type.', [
          '%label' => $quiz_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($quiz_type->urlInfo('collection'));
  }

}
