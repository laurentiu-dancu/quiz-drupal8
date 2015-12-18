<?php

/**
 * @file
 * Contains \Drupal\quiz\Entity\Answer.
 */

namespace Drupal\quiz\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\quiz\AnswerInterface;
use Drupal\quiz\QuestionInterface;
use Drupal\quiz\UserQuizStatusInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Answer entity.
 *
 * @ingroup answer
 *
 * @ContentEntityType(
 *   id = "answer",
 *   label = @Translation("Answer"),
 *   bundle_label = @Translation("Answer bundle"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\quiz\AnswerListBuilder",
 *     "views_data" = "Drupal\quiz\Entity\AnswerViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\quiz\Entity\Form\AnswerForm",
 *       "add" = "Drupal\quiz\Entity\Form\AnswerForm",
 *       "edit" = "Drupal\quiz\Entity\Form\AnswerForm",
 *       "delete" = "Drupal\quiz\Entity\Form\AnswerDeleteForm",
 *     },
 *     "access" = "Drupal\quiz\AnswerAccessControlHandler",
 *   },
 *   base_table = "answer",
 *   admin_permission = "administer Answer entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "bundle" = "type"
 *   },
 *   bundle_entity_type = "answer_type",
 *   links = {
 *     "canonical" = "/admin/answer/{answer}",
 *     "edit-form" = "/admin/answer/{answer}/edit",
 *     "delete-form" = "/admin/answer/{answer}/delete"
 *   },
 *   field_ui_base_route = "entity.answer_type.edit_form"
 * )
 */
class Answer extends ContentEntityBase implements AnswerInterface {
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
  public function getCreatedTime() {
    return $this->get('created')->value;
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
  public function getQuestion() {
    return $this->get('question')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestionId() {
    return $this->get('question')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setQuestion(QuestionInterface $question) {
    $this->set('question', $question->id());
    return $this;
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
  public function getType() {
    return $this->bundle();
  }

  public function setUserQuizStatus(UserQuizStatusInterface $status) {
    $this->set('user_quiz_status', $status);
    return $this;
  }

  public function getUserQuizStatusId() {
    return $this->get('user_quiz_status')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Answer entity.'))
      ->setReadOnly(TRUE);

    $fields['question'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Question'))
      ->setSetting('target_type', 'question')
      ->setDisplayConfigurable('view', TRUE)
      ->setReadOnly(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ));

    $fields['user_quiz_status'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User-Quiz status'))
      ->setSetting('target_type', 'user_quiz_status');


    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Answer entity.'))
      ->setReadOnly(TRUE);


    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Answer entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayConfigurable('view', TRUE);


    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The answer type.'))
      ->setSetting('target_type', 'answer_type')
      ->setReadOnly(TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code for the Answer entity.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
