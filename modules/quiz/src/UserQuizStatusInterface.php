<?php

/**
 * @file
 * Contains \Drupal\quiz\UserQuizStatusInterface.
 */

namespace Drupal\quiz;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\TypedData\Plugin\DataType\Timestamp;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining User quiz status entities.
 *
 * @ingroup quiz
 */
interface UserQuizStatusInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Sets the quiz.
   *
   * @param \Drupal\quiz\QuizInterface $quiz
   *    The quiz to be set.
   * @return mixed
   */
  public function setQuiz(QuizInterface $quiz);


  /**
   * @return mixed
   */
  public function getQuiz();


  /**
   * @param $score
   * @return mixed
   */
  public function setScore($score);


  /**
   * @return mixed
   */
  public function getScore();


  /**
   * @param $maxScore
   * @return mixed
   */
  public function setMaxScore($maxScore);


  /**
   * @return mixed
   */
  public function getMaxScore();


  /**
   * @param $correctAnswerCount
   * @return mixed
   */
  public function setAnswerCount($correctAnswerCount);


  /**
   * @return mixed
   */
  public function getAnswerCount();


  /**
   * @param $totalAnswerCount
   * @return mixed
   */
  public function setQuestionsCount($totalAnswerCount);


  /**
   * @return mixed
   */
  public function getQuestionsCount();


  /**
   * @param $percent
   * @return mixed
   */
  public function setPercent($percent);


  /**
   * @return mixed
   */
  public function getPercent();


  /**
   * @return mixed
   */
  public function getStarted();

  /**
   * @param int
   * @return mixed
   */
  public function setFinished($timestamp);


  /**
   * @return mixed
   */
  public function getFinished();


  /**
   * @param \Drupal\quiz\QuestionInterface $question
   * @return mixed
   */
  public function setLastQuestion(QuestionInterface $question);


  /**
   * @return mixed
   */
  public function getLastQuestionId();

  /**
   * @return mixed
   */
  public function isFinished();

  /**
   * @param \Drupal\quiz\QuestionInterface|NULL $question
   * @return mixed
   */
  public function setCurrentQuestion(QuestionInterface $question = NULL);

  /**
   * @return mixed
   */
  public function getCurrentQuestionId();

  /**
   * Calculates the score for this quiz instance based on answers to its questions.
   *
   * @return int score
   */
  public function evaluate();

  /**
   * @return mixed
   */
  public function getAnswers();


}
