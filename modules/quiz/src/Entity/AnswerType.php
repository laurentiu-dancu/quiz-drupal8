<?php

/**
 * @file
 * Contains \Drupal\quiz\Entity\AnswerType.
 */

namespace Drupal\quiz\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\quiz\AnswerTypeInterface;

/**
 * Defines the Answer type entity.
 *
 * @ConfigEntityType(
 *   id = "answer_type",
 *   label = @Translation("Answer type"),
 *   handlers = {
 *     "list_builder" = "Drupal\quiz\AnswerTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\quiz\Form\AnswerTypeForm",
 *       "edit" = "Drupal\quiz\Form\AnswerTypeForm",
 *       "delete" = "Drupal\quiz\Form\AnswerTypeDeleteForm"
 *     }
 *   },
 *   config_prefix = "answer_type",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   bundle_of = "answer",
 *
 *   links = {
 *     "canonical" = "/admin/structure/answer_type/{answer_type}",
 *     "edit-form" = "/admin/structure/answer_type/{answer_type}/edit",
 *     "delete-form" = "/admin/structure/answer_type/{answer_type}/delete",
 *     "collection" = "/admin/structure/visibility_group"
 *   }
 * )
 */
class AnswerType extends ConfigEntityBundleBase implements AnswerTypeInterface {
  /**
   * The Answer type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Answer type label.
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
