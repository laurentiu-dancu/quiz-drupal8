<?php

/**
 * @file
 * Contains \Drupal\quiz\Entity\Quiz.
 */

namespace Drupal\quiz\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\quiz\QuizInterface;
use Drupal\user\UserInterface;
//"view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
/**
 * Defines the Quiz entity.
 *
 * @ingroup quiz
 *
 * @ContentEntityType(
 *   id = "quiz",
 *   label = @Translation("Quiz"),
 *   bundle_label = @Translation("Quiz bundle"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\quiz\QuizListBuilder",
 *     "views_data" = "Drupal\quiz\Entity\QuizViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\quiz\Entity\Form\QuizForm",
 *       "add" = "Drupal\quiz\Entity\Form\QuizForm",
 *       "edit" = "Drupal\quiz\Entity\Form\QuizForm",
 *       "delete" = "Drupal\quiz\Entity\Form\QuizDeleteForm",
 *     },
 *     "access" = "Drupal\quiz\QuizAccessControlHandler",
 *   },
 *   base_table = "quiz",
 *   admin_permission = "administer Quiz entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "bundle" = "type"
 *   },
 *   bundle_entity_type = "quiz_type",
 *   links = {
 *     "canonical" = "/quiz/{quiz}",
 *     "edit-form" = "/admin/quiz/{quiz}/edit",
 *     "delete-form" = "/admin/quiz/{quiz}/delete"
 *   },
 *   field_ui_base_route = "entity.quiz_type.edit_form"
 * )
 */
class Quiz extends ContentEntityBase implements QuizInterface {
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
  public function getQuestions() {
    $questionStorage = static::entityTypeManager()->getStorage('question');
    $query = $questionStorage->getQuery();
    $aids = $query
      ->Condition('quiz', $this->id())
      ->execute();
    return $questionStorage->loadMultiple($aids);
  }

  /**
   * {@inheritdoc}
   */
  public function getStatuses(AccountInterface $user = NULL) {
    $statusStorage = static::entityTypeManager()->getStorage('user_quiz_status');
    $query = $statusStorage->getQuery();
    if($user != NULL) {
      $qidList = $query
        ->condition('quiz', $this->id())
        ->condition('user_id', $user->id())
        ->execute();
    }
    else {
      $qidList = $query
        ->condition('quiz', $this->id())
        ->execute();
    }
    return $statusStorage->loadMultiple($qidList);
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveStatus(AccountInterface $user) {
    $statusStorage = static::entityTypeManager()->getStorage('user_quiz_status');
    $query = $statusStorage->getQuery();
    $qidList = $query
      ->condition('quiz', $this->id())
      ->condition('user_id', $user->id())
      ->condition('finished', 0)
      ->execute();
    //kint($qidList);
    if (!empty($qidList))
      return $statusStorage->load(key($qidList));
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getMaxScore() {
    $questions = $this->getQuestions();
    $score = 0;
    foreach ($questions as $question) {
      /* @var $question \Drupal\quiz\Entity\Question */
      $score += $question->get('score')->value;
    }
    return $score;
  }

  public function getQuestionCount() {
    $storage = static::entityTypeManager()->getStorage('question');
    $query = $storage->getQuery();
    $qids = $query->Condition('quiz', $this->id())->execute();
    return count($qids);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Quiz entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Quiz entity.'))
      ->setReadOnly(TRUE);

    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The quiz type.'))
      ->setSetting('target_type', 'quiz_type')
      ->setReadOnly(TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Quiz entity.'))
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

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Quiz entity.'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Description'))
      ->setDescription(t('The description of this quiz'))
      ->setSettings(array(
        'max_length' => 256,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => 0,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['percent'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Pass rate'))
      ->setDescription(t('The pass rate for this quiz in percents.'))
      ->setDefaultValue(50)
      ->addPropertyConstraints('value', ['Range' => ['min' => 0, 'max' => 100]])
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => 1,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => 1,
      ));

    $fields['time'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Quiz time (seconds)'))
      ->setDescription(t('The number of seconds the user has to complete the quiz after starting it. Set to 0 for no time limit.'))
      ->setDefaultValue(0)
      ->addPropertyConstraints('value', ['Range' => ['min' => 0]])
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => 1,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => 1,
      ));

    $fields['attempts'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Number of attempts allowed'))
      ->setDescription(t('The number a time an user is allowed to attempt this quiz. Set to 0 for unlimited attempts.'))
      ->setDefaultValue(0)
      ->addPropertyConstraints('value', ['Range' => ['min' => 0]])
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => 1,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => 1,
      ));

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code for the Quiz entity.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
