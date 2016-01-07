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
   * @param \Drupal\Core\Session\AccountInterface $user
   * @return mixed
   */
  public function getStatuses(AccountInterface $user);

  public function getActiveStatus(AccountInterface $user);

  public function getMaxScore();

  public function getQuestionCount();
}
