<?php

/**
 * @file
 * Contains \Drupal\quiz\Entity\Form\QuestionDeleteFormQuiz.
 */

namespace Drupal\quiz\Entity\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\quiz\QuestionInterface;
use Drupal\quiz\QuizInterface;

/**
 * Provides a form for deleting Question entities.
 *
 * @ingroup question
 */
class QuestionDeleteFormQuiz extends ConfirmFormBase {

  private $quiz;

  private $entity;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'quiz_question_delete';
  }

  public function buildForm(array $form, FormStateInterface $form_state, QuizInterface $quiz = NULL, QuestionInterface $question = NULL) {

    $this->quiz = $quiz;
    $this->entity = $question;

    kint($quiz);
    return parent::buildForm($form, $form_state);
  }

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

    $question = $this->entity;
    /* @var $question \Drupal\quiz\Entity\Question */

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

    if($this->quiz == NULL) {
      kint($this->quiz);
      $form_state->setRedirect('entity.quiz.collection');
    }
    else {
      $form_state->setRedirect('entity.quiz.canonical_admin', [
        'quiz' => $this->quiz->id(),
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('This action cannot be undone and will also delete all the answers given to this question.');
  }

}
