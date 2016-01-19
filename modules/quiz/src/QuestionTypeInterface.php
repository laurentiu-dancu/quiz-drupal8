<?php

/**
 * @file
 * Contains \Drupal\quiz\QuestionTypeInterface.
 */

namespace Drupal\quiz;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Question Type entities.
 */
interface QuestionTypeInterface extends ConfigEntityInterface {
  // Add get/set methods for your configuration properties here.

  /**
   * @return bool
   *    Returns true if the bundle id is true_or_false
   */
  public function isTrueFalse();

  /**
   * @return bool
   *    Returns true if the bundle id is text_question
   */
  public function isText();

  /**
   * @return bool
   *    Returns true if the bundle id is multiple_choice_question
   */
  public function isMultipleChoice();
}
