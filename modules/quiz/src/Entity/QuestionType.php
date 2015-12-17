<?php

/**
 * @file
 * Contains \Drupal\quiz\Entity\QuestionType.
 */

namespace Drupal\quiz\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\quiz\QuestionTypeInterface;

/**
 * Defines the Question Type entity.
 *
 * @ConfigEntityType(
 *   id = "question_type",
 *   label = @Translation("Question Type"),
 *   handlers = {
 *     "list_builder" = "Drupal\quiz\QuestionTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\quiz\Form\QuestionTypeForm",
 *       "edit" = "Drupal\quiz\Form\QuestionTypeForm",
 *       "delete" = "Drupal\quiz\Form\QuestionTypeDeleteForm"
 *     }
 *   },
 *   bundle_of = "question",
 *   config_prefix = "question_type",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/question_type/{question_type}",
 *     "edit-form" = "/admin/structure/question_type/{question_type}/edit",
 *     "delete-form" = "/admin/structure/question_type/{question_type}/delete",
 *     "collection" = "/admin/structure/visibility_group"
 *   }
 * )
 */
class QuestionType extends ConfigEntityBundleBase implements QuestionTypeInterface {
  /**
   * The Question Type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Question Type label.
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
