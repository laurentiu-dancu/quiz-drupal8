<?php

/**
 * @file
 * Contains \Drupal\quiz\Entity\UserQuizStatus.
 */

namespace Drupal\quiz\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\quiz\UserQuizStatusInterface;
use Drupal\user\UserInterface;

/**
 * Defines the User quiz status entity.
 *
 * @ingroup quiz
 *
 * @ContentEntityType(
 *   id = "user_quiz_status",
 *   label = @Translation("User quiz status"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\quiz\UserQuizStatusListBuilder",
 *     "views_data" = "Drupal\quiz\Entity\UserQuizStatusViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\quiz\Entity\Form\UserQuizStatusForm",
 *       "add" = "Drupal\quiz\Entity\Form\UserQuizStatusForm",
 *       "edit" = "Drupal\quiz\Entity\Form\UserQuizStatusForm",
 *       "delete" = "Drupal\quiz\Entity\Form\UserQuizStatusDeleteForm",
 *     },
 *     "access" = "Drupal\quiz\UserQuizStatusAccessControlHandler",
 *   },
 *   base_table = "user_quiz_status",
 *   admin_permission = "administer UserQuizStatus entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/user_quiz_status/{user_quiz_status}",
 *     "edit-form" = "/admin/user_quiz_status/{user_quiz_status}/edit",
 *     "delete-form" = "/admin/user_quiz_status/{user_quiz_status}/delete"
 *   },
 *   field_ui_base_route = "user_quiz_status.settings"
 * )
 */
class UserQuizStatus extends ContentEntityBase implements UserQuizStatusInterface {
  use EntityChangedTrait;
  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the User quiz status entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the User quiz status entity.'))
      ->setReadOnly(TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the User quiz status entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setTranslatable(TRUE);

    $fields['quiz'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Quiz'))
      ->setDescription(t('The quiz of this question.'))
      ->setSetting('target_type', 'quiz');

    $fields['score'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Score'))
      ->setDescription(t('The score the user obtained for this quiz'));

    $fields['max_score'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Maximum Score'))
      ->setDescription(t('The maximum score that can be obtained for this quiz.'));

    $fields['correct_answers'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Correct answer count'))
      ->setDescription(t('How many correct answers were given.'));

    $fields['total_answers'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Total answer count'))
      ->setDescription(t('How many answers were in the quiz.'));

    $fields['percent'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Pass Percent'))
      ->setDescription(t(''));

    $fields['last_question'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Last question'))
      ->setDescription(t('The last question in a quiz that has been answered.'))
      ->setSetting('target_type', 'quiz');

    $fields['started'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Started'))
      ->setDescription(t('The time that the quiz has started.'));

    $fields['finished'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Finished'))
      ->setDescription(t('The time that the quiz finished.'));

    return $fields;
  }

}
