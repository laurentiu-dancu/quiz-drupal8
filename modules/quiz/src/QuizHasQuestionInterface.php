<?php

/**
 * @file
 * Contains \Drupal\quiz\QuizHasQuestionInterface.
 */

namespace Drupal\quiz;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Quiz has question entities.
 *
 * @ingroup quiz
 */
interface QuizHasQuestionInterface extends ContentEntityInterface {

  /**
   * @param \Drupal\quiz\QuizInterface $quiz
   * @return QuizHasQuestionInterface $this
   */
  public function setQuiz(QuizInterface $quiz);

  /**
   * @return QuizInterface $quiz
   */
  public function getQuiz();

  /**
   * @param \Drupal\quiz\QuestionInterface $question
   * @return QuizHasQuestionInterface $this
   */
  public function setQuestion(QuestionInterface $question);

  /**
   * @return QuestionInterface $question
   */
  public function getQuestion();

}
