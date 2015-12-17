<?php

/**
 * @file
 * Contains \Drupal\quiz\Controller\QuestionController.
 */

namespace Drupal\quiz\Controller;

use Drupal\quiz\QuestionTypeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\quiz\QuestionTypeListBuilder;
use Drupal\quiz\QuizInterface;

/**
 * Class QuestionController.
 *
 * @package Drupal\quiz\Controller
 */
class QuestionController extends ControllerBase {
  /**
   * Adds a new question.
   *
   * @param $quiz
   *    The quiz for which to add the question.
   * @param \Drupal\quiz\QuestionTypeInterface $question_type
   *    The type of question.
   * @return array
   *    New question type form.
   */
  public function add($quiz, QuestionTypeInterface $question_type) {
    $answer_type = NULL;
    if($question_type->id() == 'true_or_false')
      $answer_type = 'true_or_false';
    if($question_type->id() == 'text_question')
      $answer_type = 'text_answer';
    if($question_type->id() == 'multiple_choice_question')
      $answer_type = 'multiple_choice_answer';
    $question = static::entityTypeManager()->getStorage('question')->create(array(
      'type' => $question_type->id(),
      'answer_type' => $answer_type,
      'quiz' => $quiz,
    ));

    $form = $this->entityFormBuilder()->getForm($question);

    return $form;
  }

  /**
   * Lists all the question types.
   *
   * @return array
   *    Rendable array containing a table of question types.
   */
  public function addPage() {
    $storage = static::entityTypeManager()->getStorage('question_type');
    $query = $storage->getQuery();
    $qtids = $query->execute();
    $result = array();

    if(count($qtids) != 0) {
      reset($qtids);
      $type = $storage->load(current($qtids))->getEntityType();

      $builder = new QuestionTypeListBuilder($type, $storage);
      $builder->load();
      $result = $builder->render();

    }

    return array(
      '#theme' => 'question',
      '#content' => $result,
    );
  }

  /**
   * Lists the question types for a new question in a quiz.
   *
   * @param \Drupal\quiz\QuizInterface $quiz
   * @return array
   *  Rendable array
   */
  public function addPageQuiz(QuizInterface $quiz) {
    $storage = static::entityTypeManager()->getStorage('question_type');
    $query = $storage->getQuery();
    $qtids = $query->execute();
    $result = array();

    if(count($qtids) != 0) {
      reset($qtids);
      $type = $storage->load(current($qtids))->getEntityType();
      $builder = new QuestionTypeListBuilder($type, $storage);
      $builder->setQuizId($quiz->id());
      $builder->load();
      $result = $builder->render();
    }
    return array(
      '#theme' => 'question',
      '#content' => $result,
    );
  }

}
