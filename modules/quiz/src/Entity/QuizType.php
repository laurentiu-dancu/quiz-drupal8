<?php

/**
 * @file
 * Contains \Drupal\quiz\Entity\QuizType.
 */

namespace Drupal\quiz\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\quiz\QuizTypeInterface;

/**
 * Defines the Quiz type entity.
 *
 * @ConfigEntityType(
 *   id = "quiz_type",
 *   label = @Translation("Quiz type"),
 *   handlers = {
 *     "list_builder" = "Drupal\quiz\QuizTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\quiz\Form\QuizTypeForm",
 *       "edit" = "Drupal\quiz\Form\QuizTypeForm",
 *       "delete" = "Drupal\quiz\Form\QuizTypeDeleteForm"
 *     }
 *   },
 *   config_prefix = "quiz_type",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   bundle_of = "quiz",
 *
 *   links = {
 *     "canonical" = "/admin/structure/quiz_type/{quiz_type}",
 *     "edit-form" = "/admin/structure/quiz_type/{quiz_type}/edit",
 *     "delete-form" = "/admin/structure/quiz_type/{quiz_type}/delete",
 *     "collection" = "/admin/structure/visibility_group"
 *   }
 * )
 */
class QuizType extends ConfigEntityBundleBase implements QuizTypeInterface {
  /**
   * The Quiz type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Quiz type label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Answer type uuid.
   *
   * @var string
   */
  protected $uuid;
}
