<?php

/**
 * @file
 * Contains \Drupal\quiz\Entity\Form\questionForm.
 */

namespace Drupal\quiz\Entity\Form;

use Drupal\quiz\Entity\AnswerType;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\Language;
use Drupal\quiz\QuestionInterface;
use Drupal\quiz\QuestionTypeInterface;

/**
 * Form controller for Question edit forms.
 *
 * @ingroup question
 */
class QuestionForm extends ContentEntityForm {
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\quiz\Entity\question */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;
    $form['langcode'] = array(
      '#title' => $this->t('Language'),
      '#type' => 'language_select',
      '#default_value' => $entity->langcode->value,
      '#languages' => Language::STATE_ALL,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, FormStateInterface $form_state) {
    // Build the entity object from the submitted values.
    $entity = parent::submit($form, $form_state);

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = $entity->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Question.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Question.', [
          '%label' => $entity->label(),
        ]));
    }
    if ($entity instanceof QuestionInterface) {
      $form_state->setRedirect('entity.quiz.canonical_admin', ['quiz' => $entity->get('quiz')->target_id]);
    }
    else {
      $form_state->setRedirect('entity.quiz.canonical', ['quiz' => \Drupal::request()->attributes->get('quiz')]);
    }
  }

}
