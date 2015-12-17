<?php

/**
 * @file
 * Contains \Drupal\quiz\Controller\AnswerController.
 */

namespace Drupal\quiz\Controller;

use Drupal\quiz\AnswerTypeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\quiz\QuestionInterface;

/**
 * Class AnswerController.
 *
 * @package Drupal\quiz\Controller
 */
class AnswerController extends ControllerBase {

  /**
   * Adds an answer type to a question.
   *
   * @param \Drupal\quiz\AnswerTypeInterface $answer_type
   * @param \Drupal\quiz\QuestionInterface $question
   * @return array
   *  Returns form for adding an answer type.
   */
  public function add(AnswerTypeInterface $answer_type, QuestionInterface $question) {
    $answer = static::entityTypeManager()->getStorage('answer')->create(array(
      'type' => $answer_type->id(),
      'question' => $question->id(),
    ));
    $form = $this->entityFormBuilder()->getForm($answer);
    return $form;
  }


  /**
   * Adds an answer to a question.
   *
   * @param \Drupal\quiz\QuestionInterface $question
   * @return array
   *  Returns form for adding an answer.
   */
  public function answerQuestion(QuestionInterface $question) {
    $answer = static::entityTypeManager()->getStorage('answer')->create(array(
      'type' => $question->get('answer_type')->target_id,
      'question' => $question->id(),
    ));

    $form = $this->entityFormBuilder()->getForm($answer);

    return $form;
  }

  /**
   * Builds a title for a question in format question x of n.
   *
   * @param \Drupal\quiz\QuestionInterface $question
   * @return string
   *  Returns title string.
   */
  public function answerQuestionTitle(QuestionInterface $question) {
    $storage = static::entityTypeManager()->getStorage('question');
    $quizId = $question->getQuizId();
    $query = $storage->getQuery();
    $qids = $query
      ->Condition('quiz', $quizId)
      ->execute();
    $current = 0;
    foreach ($qids as $qid) {
        $current++;
      if($qid == $question->id())
        break;
    }
    return 'Question ' . $current . ' of ' . count($qids);
  }
}
