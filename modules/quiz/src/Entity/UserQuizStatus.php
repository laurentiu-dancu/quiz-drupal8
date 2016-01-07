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
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TypedData\Plugin\DataType\Timestamp;
use Drupal\quiz\QuestionInterface;
use Drupal\quiz\QuizInterface;
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
 *   base_table = "user_quiz_status",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid"
 *   },
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
  public function setScore($score) {
    $this->set('score', $score);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getScore() {
    return $this->get('score')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setMaxScore($maxScore) {
    $this->set('max_score', $maxScore);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getMaxScore() {
    return $this->get('max_score')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAnswerCount($correctAnswerCount) {
    $this->set('given_answers', $correctAnswerCount);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAnswerCount() {
    return $this->get('given_answers')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setQuestionsCount($totalAnswerCount) {
    $this->set('total_questions', $totalAnswerCount);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestionsCount() {
    return $this->get('total_questions')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPercent($percent) {
    $this->set('percent', $percent);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPercent() {
    return $this->get('percent')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getStarted() {
    return $this->get('started')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setFinished($timestamp) {
    $this->set('finished', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFinished() {
    return $this->get('finished')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setLastQuestion(QuestionInterface $question) {
    $this->set('last_question', $question);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLastQuestionId() {
    /*
    kint($this->get('last_question')->entity);
    if ($this->get('last_question')->target_id == NULL)
      return NULL;
    */
    //kint($this->get('last_question')->target_id);
    return $this->get('last_question')->target_id;
  }

  public function setCurrentQuestion(QuestionInterface $question = NULL) {
    $this->set('current_question', $question);
    return $this;
  }

  public function getCurrentQuestionId() {
    return $this->get('current_question')->target_id;
  }

  public function isFinished() {
    return $this->get('finished')->value;
  }


  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $score = 0;
    $answers = $this->getAnswers();
    foreach ($answers as $answer) {
      /* @var $answer \Drupal\quiz\Entity\Answer */
      $question = $answer->getQuestion();
      /* @var $question \Drupal\quiz\Entity\Question */

      //calculating score for a text question
      if ($question->getType() == 'text_question' && $answer->getType() == 'text_answer') {
        if ($answer->get('field_text_answer')->value == $question->get('field_text_answer')->value) {
          $score += $question->get('score')->value;
        }
      }

      //calculating score for a true or false question
      if ($question->getType() == $answer->getType() && $answer->getType() == 'true_or_false') {
        if ($answer->get('field_true_or_false')->value == $question->get('field_true_or_false')->value) {
          $score += $question->get('score')->value;
        }
      }

      //calculating score for a multiple choice question
      if ($question->getType() == 'multiple_choice_question' && $answer->getType() == 'multiple_choice_answer') {
        $questions = array();
        $fail = 0;
        foreach ($question->get('field_multiple_answer') as $delta => $field) {
          $questions[$delta] = $field->value;
        }
        foreach ($answer->get('field_multiple_answer') as $delta => $field) {
          if ($field->value != $questions[$delta]) {
            $fail = 1;
            break;
          }
        }
        if(!$fail) {
          $score += $question->get('score')->value;
        }
      }
    }
    return $score;
  }


  /**
   * {@inheritdoc}
   */
  public function getAnswers() {
    $answerStorage = static::entityTypeManager()->getStorage('answer');
    $query = $answerStorage->getQuery();
    $aids = $query
      ->Condition('user_id', $this->getOwnerId())
      ->Condition('user_quiz_status', $this->id())
      ->execute();
    $answers = $answerStorage->loadMultiple($aids);
    return $answers;
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

    $fields['given_answers'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Given answer count'))
      ->setDescription(t('How many correct answers were given.'));

    $fields['total_questions'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Total questions count'))
      ->setDescription(t('How many answers were in the quiz.'));

    $fields['percent'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Pass Percent'))
      ->setDescription(t(''));

    $fields['last_question'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Last question'))
      ->setDescription(t('The last question in a quiz that has been answered.'))
      ->setSetting('target_type', 'question');

    $fields['current_question'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Current question'))
      ->setDescription(t('The question that is authorized to be answered'))
      ->setSetting('target_type', 'question');

    $fields['started'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Started'))
      ->setDescription(t('The time that the quiz has started.'));

    $fields['finished'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Finished'))
      ->setDescription(t('The time that the quiz finished.'));

    return $fields;
  }

}
