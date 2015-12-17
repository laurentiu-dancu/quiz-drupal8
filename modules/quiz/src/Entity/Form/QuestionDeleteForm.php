<?php

/**
 * @file
 * Contains \Drupal\quiz\Entity\Form\questionDeleteForm.
 */

namespace Drupal\quiz\Entity\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for deleting Question entities.
 *
 * @ingroup question
 */
class QuestionDeleteForm extends ContentEntityConfirmFormBase {
  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete entity %name?', array('%name' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.question.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $question = $this->entity;
    /* @var $question \Drupal\quiz\Entity\Question */
    $qid = $question->getQuizId();

    $counter = 0;
    foreach ($question->getAnswers() as $answer) {
      /* @var $answer \Drupal\quiz\Entity\Answer */
      $answer->delete();
      $counter++;
    }
    $this->entity->delete();

    drupal_set_message(
      $this->t('Quiz: deleted question "@label" and its @count answers.',
        [
          '@count' => $counter,
          '@label' => $this->entity->label()
        ]
        )
    );

    $form_state->setRedirect('entity.quiz.canonical', [
      'quiz' => $qid,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('This action cannot be undone and will also delete all the answers given to this question.');
  }

}
