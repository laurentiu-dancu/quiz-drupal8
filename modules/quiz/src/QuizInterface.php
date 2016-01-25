<?php

/**
 * @file
 * Contains \Drupal\quiz\QuizInterface.
 */

namespace Drupal\quiz;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\quiz\Entity\UserQuizStatus;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Quiz entities.
 *
 * @ingroup quiz
 */
interface QuizInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets all the questions for this Quiz.
   *
   * @return mixed
   */
  public function getQuestions();

  /**
   * Gets all the states of a given user for this quiz.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   * @return array
   *    An array containing the user quiz states for this quiz.
   */
  public function getStatuses(AccountInterface $user);

  /**
   * Gets the active status of a given user for this quiz.
   * Gets NULL if the user has no active state.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   * @return UserQuizStatus|NULL
   */
  public function getActiveStatus(AccountInterface $user);

  /**
   * Calculates and returns the maximum achievable score of this quiz.
   *
   * @return int
   */
  public function getMaxScore();

  /**
   * Counts the number of questions associated with this quiz.
   *
   * @return int
   */
  public function getQuestionCount();

  /**
   * Gets all the questions that exist.
   *
   * @return array
   */
  public function getAllQuestions();

  /**
   * Removes a question from this quiz without deleting it.
   *
   * @param \Drupal\quiz\QuestionInterface $question
   * @return $this
   */
  public function removeQuestion(QuestionInterface $question);

  /**
   * Gets the percent needed for an user to pass this quiz.
   *
   * @return int
   */
  public function getPercentile();

  /**
   * Gets the time limit in seconds set for this quiz.
   *
   * @return int
   */
  public function getTimeLimit();

  /**
   * Gets the description of this quiz.
   *
   * @return string
   */
  public function getDescription();

  /**
   * Gets the number of times an user is allowed to attempt this quiz.
   *
   * @return int
   */
  public function getAttemptLimit();

  /**
   * Gets the name of this quiz.
   *
   * @return string
   */
  public function getName();

  public function removeQuestionById($qid);

  public function getUnselectedQuestions();

  public function getQuestionScoreById($qid);

}
