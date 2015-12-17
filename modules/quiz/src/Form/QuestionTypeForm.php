<?php

/**
 * @file
 * Contains \Drupal\quiz\Form\question_typeForm.
 */

namespace Drupal\quiz\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class question_typeForm.
 *
 * @package Drupal\quiz\Form
 */
class QuestionTypeForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $question_type = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $question_type->label(),
      '#description' => $this->t("Label for the Question Type."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $question_type->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\quiz\Entity\question_type::load',
      ),
      '#disabled' => !$question_type->isNew(),
    );

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $question_type = $this->entity;
    $status = $question_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Question Type.', [
          '%label' => $question_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Question Type.', [
          '%label' => $question_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($question_type->urlInfo('collection'));
  }

}
