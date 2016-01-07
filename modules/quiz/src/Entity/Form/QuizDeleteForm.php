<?php

/**
 * @file
 * Contains \Drupal\quiz\Entity\Form\QuizDeleteForm.
 */

namespace Drupal\quiz\Entity\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for deleting Quiz entities.
 *
 * @ingroup quiz
 */
class QuizDeleteForm extends ContentEntityConfirmFormBase {
  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete %name?', array('%name' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.quiz.collection');
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
    /* @var $entity \Drupal\quiz\Entity\Quiz */
    /* @var $question \Drupal\quiz\Entity\Question */
    /* @var $answer \Drupal\quiz\Entity\Answer */
    /* @var $state \Drupal\quiz\Entity\UserQuizStatus */

    $entity = $this->entity;

    $questions = $entity->getQuestions();

    $answerCount = 0;
    $questionCount = 0;
    $stateCount = 0;
    foreach($questions as $question) {
      $answers = $question->getAnswers();
      foreach ($answers as $answer) {
        $answerCount++;
        $answer->delete();
      }
      $questionCount++;
      $question->delete();
    }

    $states = $entity->getStatuses();

    foreach($states as $state) {
      $stateCount++;
      $state->delete();
    }

    $this->entity->delete();

    drupal_set_message(
      $this->t('Deleted Quiz @label, @answers answers, @questions questions and @states quiz attempts.',
        [
          '@label' => $this->entity->label(),
          '@answers' => $answerCount,
          '@questions' => $questionCount,
          '@states' => $stateCount,
        ]
        )
    );

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('This action cannot be undone and will also delete all the questions and answers associated with this quiz.');
  }

}
