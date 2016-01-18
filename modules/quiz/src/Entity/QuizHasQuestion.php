<?php

/**
 * @file
 * Contains \Drupal\quiz\Entity\QuizHasQuestion.
 */

namespace Drupal\quiz\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\quiz\QuestionInterface;
use Drupal\quiz\QuizHasQuestionInterface;
use Drupal\quiz\QuizInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Quiz has question entity.
 *
 * @ingroup quiz
 *
 * @ContentEntityType(
 *   id = "quiz_has_question",
 *   label = @Translation("Quiz has question"),
 *   base_table = "quiz_has_question",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid"
 *   },
 * )
 */
class QuizHasQuestion extends ContentEntityBase implements QuizHasQuestionInterface {
  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
  }

  /**
   * {@inheritdoc}
   */
  public function setQuiz(QuizInterface $quiz) {
    $this->set('quiz', $quiz->id());
    return $this;
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
  public function setQuestion(QuestionInterface $question) {
    $this->set('question', $question->id());
    return $this;
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
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Quiz has question entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Quiz has question entity.'))
      ->setReadOnly(TRUE);

    $fields['quiz'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Quiz'))
      ->setDescription(t('The quiz this relation references'))
      ->setSetting('target_type', 'quiz');

    $fields['question'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Question'))
      ->setDescription(t('The question this relation references'))
      ->setSetting('target_type', 'question');

    return $fields;
  }

}
