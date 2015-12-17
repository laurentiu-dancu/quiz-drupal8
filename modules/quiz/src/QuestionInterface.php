<?php

/**
 * @file
 * Contains \Drupal\quiz\QuestionInterface.
 */

namespace Drupal\quiz;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;

/**
 * Provides an interface for defining Question entities.
 *
 * @ingroup question
 */
interface QuestionInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {
  /**
   * Gets the quiz entity the question references.
   *
   * @return \Drupal\quiz\QuizInterface
   *    Returns a quiz entity.
   */
  public function getQuiz();

  /**
   * Gets the ID of quiz entity the question references.
   *
   * @return int
   *    Returns a quiz ID.
   */
  public function getQuizId();

  /**
   * Gets the bundle type of the quiz entity.
   *
   * @return string
   *    Returns the name of the bundle.
   */
  public function getType();

  /**
   * Gets the number of answers a user has given to a question.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *    An user.
   * @return int
   *    Returns the number of answers an user has for this question.
   */
  public function getUserAnswersCount(AccountInterface $account);

  /**
   * Gets all the answers of this question.
   *
   * @return array
   *    Array containing all the answers given to this question.
   */
  public function getAnswers();
}
