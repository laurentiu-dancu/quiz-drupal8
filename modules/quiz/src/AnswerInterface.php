<?php

/**
 * @file
 * Contains \Drupal\quiz\AnswerInterface.
 */

namespace Drupal\quiz;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Answer entities.
 *
 * @ingroup answer
 */
interface AnswerInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Returns the question entity for this answer.
   *
   * @return \Drupal\quiz\QuestionInterface
   *   The question entity.
   */
  public function getQuestion();

  /**
   * Sets the entity question.
   *
   * @param \Drupal\quiz\QuestionInterface $question
   *   The question for this answer.
   *
   * @return $this
   */
  public function setQuestion(QuestionInterface $question);

  /**
   * Returns the question entity id for this answer.
   *
   * @return int
   *   Returns the id of question entity.
   */
  public function getQuestionId();

  public function setUserQuizStatus(UserQuizStatusInterface $status);

  public function getUserQuizStatusId();
}
