<?php

/**
 * @file
 * Contains \Drupal\quiz\UserQuizStatusInterface.
 */

namespace Drupal\quiz;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining User quiz status entities.
 *
 * @ingroup quiz
 */
interface UserQuizStatusInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

}
