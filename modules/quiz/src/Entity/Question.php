<?php

/**
 * @file
 * Contains \Drupal\quiz\Entity\Question.
 */

namespace Drupal\quiz\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\quiz\QuestionInterface;
use Drupal\quiz\AnswerTypeInterface;
use Drupal\quiz\UserQuizStatusInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Question entity.
 *
 * @ingroup question
 *
 * @ContentEntityType(
 *   id = "question",
 *   label = @Translation("Question"),
 *   bundle_label = "[Question Type Label]",
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\quiz\QuestionListBuilder",
 *     "views_data" = "Drupal\quiz\Entity\QuestionViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\quiz\Entity\Form\QuestionForm",
 *       "add" = "Drupal\quiz\Entity\Form\QuestionForm",
 *       "edit" = "Drupal\quiz\Entity\Form\QuestionForm",
 *       "delete" = "Drupal\quiz\Entity\Form\QuestionDeleteForm",
 *     },
 *     "access" = "Drupal\quiz\QuestionAccessControlHandler",
 *   },
 *   base_table = "question",
 *   data_table = "question_field_data",
 *   admin_permission = "administer question entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "question",
 *     "uuid" = "uuid",
 *     "bundle" = "type"
 *   },
 *   bundle_entity_type = "question_type",
 *   field_ui_base_route = "entity.question_type.edit_form",
 *   permission_granularity = "bundle",
 *   links = {
 *     "canonical" = "/admin/question/{question}",
 *     "edit-form" = "/admin/question/{question}/edit",
 *     "delete-form" = "/admin/question/{question}/delete"
 *   }
 * )
 */
class Question extends ContentEntityBase implements QuestionInterface {
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

  /**
   * {@inheritdoc}
   */
  public function getQuiz() {
    return $this->get('quiz')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuizId() {
    return $this->get('quiz')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getUserAnswersCount(AccountInterface $account) {
    $answerStorage = static::entityTypeManager()->getStorage('answer');
    $query = $answerStorage->getQuery();
    $aids = $query
      ->Condition('user_id', $account->id())
      ->Condition('question', $this->id())
      ->execute();
    return count($aids);
  }

  public function getUserQuizStateAnswersCount(AccountInterface $account, UserQuizStatusInterface $state) {
    $answerStorage = static::entityTypeManager()->getStorage('answer');
    $query = $answerStorage->getQuery();
    $aids = $query
      ->Condition('user_id', $account->id())
      ->Condition('question', $this->id())
      ->Condition('user_quiz_status', $state->id())
      ->execute();
    return count($aids);
  }

  /**
   * {@inheritdoc}
   */
  public function getAnswers() {
    $answerStorage = static::entityTypeManager()->getStorage('answer');
    $query = $answerStorage->getQuery();
    $aids = $query
      ->Condition('question', $this->id())
      ->execute();
    return $answerStorage->loadMultiple($aids);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Question entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Question entity.'))
      ->setReadOnly(TRUE);

    $fields['quiz'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Quiz'))
      ->setDescription(t('The quiz of this question.'))
      ->setSetting('target_type', 'quiz')
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ))
        ->setDisplayConfigurable('form', TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Question entity.'))
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
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The question type.'))
      ->setSetting('target_type', 'question_type')
      ->setReadOnly(TRUE);

    $fields['answer_type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Answer Type'))
      ->setDescription(t('The answer type.'))
      ->setSetting('target_type', 'answer_type')
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => 3,
      ))
      ->setDisplayConfigurable('form', TRUE);


    $fields['question'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Question'))
      ->setDescription(t('The question that has to be answered.'))
      ->setSettings(array(
        'max_length' => 1024,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'text_textfield',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['score'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Score'))
      ->setDescription(t('How many points the question is worth.'))
      ->setDefaultValue(1)
      ->addPropertyConstraints('value', ['Range' => ['min' => 0]])
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ));

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code for the Question entity.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
