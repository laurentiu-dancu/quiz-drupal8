<?php

/**
 * @file
 * Contains \Drupal\quiz\Controller\QuizController.
 */

namespace Drupal\quiz\Controller;

use DateTime;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityType;
use Drupal\Core\Routing\UrlGeneratorTrait;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TypedData\Plugin\DataType\Timestamp;
use Drupal\Core\Url;
use Drupal\quiz\AnswerListBuilder;
use Drupal\quiz\Entity\UserQuizStatus;
use Drupal\quiz\QuestionInterface;
use Drupal\quiz\QuestionListBuilder;
use Drupal\quiz\QuizInterface;
use Drupal\quiz\QuizTypeInterface;
use Drupal\user\UserInterface;

/**
 * Class QuizController.
 *
 * @package Drupal\quiz\Controller
 */
class QuizController extends ControllerBase {
  /**
   * Adds a new quiz. If no quiz type is provided,
   * the type is set automatically to basic_quiz.
   *
   * @param \Drupal\quiz\QuizTypeInterface|NULL $quiz_type
   * @return array
   *  Returns a new quiz form.
   */
  public function add(QuizTypeInterface $quiz_type = NULL) {
    if ($quiz_type == NULL) {
      $quiz = static::entityTypeManager()->getStorage('quiz')->create(array(
        'type' => 'basic_quiz',
      ));
    }
    else {
      $quiz = static::entityTypeManager()->getStorage('quiz')->create(array(
        'type' => $quiz_type->id(),
      ));
    }
    $form = $this->entityFormBuilder()->getForm($quiz);
    return $form;
  }

  /**
   * Uses QuestionFormBuilder to build a list of questions for a given quiz
   *
   * @param \Drupal\quiz\QuizInterface $quiz
   * @return array
   *    Returns a rendable array.
   */
  public function listQuestions(QuizInterface $quiz) {
    $storage = static::entityTypeManager()->getStorage('question');
    $query = $storage->getQuery();
    $qids = $query
      ->Condition('quiz', $quiz->id())
      ->execute();
    $result = array();
    if (count($qids) != 0) {
      reset($qids);
      $type = $storage->load(current($qids))->getEntityType();
      $builder = new QuestionListBuilder($type, $storage);
      $builder->setIds($qids);
      $result = $builder->render();
    }
    return array(
      '#theme' => 'quiz_list_questions',
      '#questions' => $result,
    );
  }

  /**
   * Starts a quiz by loading its first question
   *
   * @param \Drupal\quiz\QuizInterface $quiz
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *  Returns a redirect for the first question if questions exist
   *  else it returns a redirect back to quiz.
   */
  public function takeQuiz(QuizInterface $quiz) {
    // Only attempt quiz if it has questions.

    $questions = $quiz->getQuestions();
    if (count($questions)) {

      $status = $quiz->getStatus($this->currentUser());
      //while(1);
      // If no open quiz session is found, create one.
      if($status == NULL) {
        $status = UserQuizStatus::create(array());
        $status->setQuiz($quiz);
        $status->setFinished(0);
        $status->save();
      }

      $next = 0;
      $nextQuestion = NULL;
      foreach ($questions as $question) {
        /* @var $question \Drupal\quiz\Entity\Question */
        if ($status->getLastQuestion() == NULL || $next) {
          kint($status->getLastQuestion());
          $nextQuestion = $question;
          break;
        }
        if ($status->getLastQuestion() == $question->id()) {
          $next = 1;
        }

      }

      // There is a question to be answered case.
      if ($nextQuestion != NULL) {
        return $this->redirect('entity.answer.add_answer', [
          'question' => $nextQuestion->id(),
        ]);
      }
      // Quiz completed case.
      else {
        kint($status->isFinished());
        if($status->isFinished() == 0) {
          $status->setScore($this->evaluate($quiz, $this->currentUser()));
          $status->setMaxScore($this->getMaxScore($quiz));
          $status->setCorrectAnswerCount(0);
          $status->setTotalAnswerCount(count($this->getQuestionIds($quiz)));
          $status->setPercent($quiz->get('percent')->value);
          $status->setFinished(time());
          $status->save();
        }
        return $this->redirect('entity.quiz.canonical_user', [
          'quiz' => $quiz->id(),
        ]);
      }
    }

    /*
    // Only attempt quiz if it has questions.
    if (count($questions)) {
      foreach ($questions as $question) {
        /* @var $question \Drupal\quiz\Entity\Question
        if ($question->getUserAnswersCount($this->currentUser())) {
          return $this->redirect('entity.quiz.take_quiz_question', [
            'question' => $question->id(),
            'quiz' => $quiz->id()
          ]);
        }
        return $this->redirect('entity.answer.add_answer', [
          'question' => $question->id(),
        ]);
      }
    }
*/
    drupal_set_message($this->t('This quiz has no questions.'), 'warning');
    return $this->redirect('entity.quiz.canonical_user', [
      'quiz' => $quiz->id(),
    ]);
  }

  /**
   * Gets the next question for a quiz.
   *
   * @param \Drupal\quiz\QuizInterface $quiz
   * @param \Drupal\quiz\QuestionInterface $question
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *    Returns a redirect to the next answer or back to quiz if no next answer.
   */
  /*
  public function takeQuizQuestion(QuizInterface $quiz, QuestionInterface $question) {

    // Getting IDs of questions for this quiz
    $storage = static::entityTypeManager()->getStorage('question');
    $query = $storage->getQuery();
    $qids = $query->Condition('quiz', $quiz->id())->execute();
    $next = 0;
    foreach ($qids as $qid) {
      if ($next == 1) {
        $quest = $storage->load($qid);
        /* @var $quest \Drupal\quiz\Entity\Question
        if($this->isAnswered($quest, $this->currentUser())) {
          return $this->redirect('entity.quiz.take_quiz_question', [
            'question' => $quest->id(),
            'quiz' => $quiz->id()
          ]);
        }
        else {
          return $this->redirect('entity.answer.add_answer', [
            'question' => $quest->id(),
          ]);
        }
      }
      if ($qid == $question->id()) {
        $next = 1;
      }
    }
    return $this->redirect('entity.quiz.canonical_user', [
      'quiz' => $quiz->id(),
    ]);

  }
*/

  /**
   * Gets all the answers for a quiz for an user.
   *
   * @param \Drupal\quiz\QuizInterface $quiz
   * @param \Drupal\Core\Session\AccountInterface $user
   * @return array
   *    Returns answers for a quiz for an user.
   */
  public function getAnswers(QuizInterface $quiz, AccountInterface $user) {
    $answerStorage = static::entityTypeManager()->getStorage('answer');
    $query = $answerStorage->getQuery();
    $aids = $query->Condition('user_id', $user->id())->execute();

    $answerArray = array();
    $answers = $answerStorage->loadMultiple($aids);

    foreach ($answers as $answer) {
      /* @var $answer \Drupal\quiz\Entity\Answer */
      if ($answer->getQuestion()->getQuizId() == $quiz->id()) {
        $answerArray[] = $answer;
      }
    }
    return $answerArray;
  }

  public function getQuestions(QuizInterface $quiz) {
    $questionStorage = static::entityTypeManager()->getStorage('question');
    $query = $questionStorage->getQuery();
    $qids = $query->Condition('quiz', $quiz->id())->execute();
    $questions = $questionStorage->loadMultiple($qids);
    return $questions;
  }

  /**
   * Gets the ID of the lasted responded question in a quiz for an user
   *
   * @param \Drupal\quiz\QuizInterface $quiz
   * @param \Drupal\Core\Session\AccountInterface $user
   * @return int
   *    Returns id of question.
   */
  public function getLatestAnsweredQuestionId(QuizInterface $quiz, AccountInterface $user) {
    $answers = $this->getAnswers($quiz, $user);
    /* @var $answer \Drupal\quiz\Entity\Answer */
    $answer = end($answers);
    return $answer->getQuestionId();
  }

  /**
   * Finds the IDs of questions for a quiz.
   *
   * @param \Drupal\quiz\QuizInterface $quiz
   * @return array
   *    Returns IDs of questions for a quiz
   */
  public function getQuestionIds(QuizInterface $quiz) {
    $storage = static::entityTypeManager()->getStorage('question');
    $query = $storage->getQuery();
    $qids = $query->Condition('quiz', $quiz->id())->execute();
    return $qids;
  }

  public function userDisplayQuizTitle(QuizInterface $quiz) {
    return $quiz->get('name')->value;
  }

  /**
   * Displays the status for a quiz for a normal user
   *
   * @param \Drupal\quiz\QuizInterface $quiz
   * @return array
   *  Returns a rendable array with a markup
   */
  public function userDisplayQuiz(QuizInterface $quiz) {
    $answeredQuestions = count($this->getAnswers($quiz, $this->currentUser()));
    $nrOfQuestions = count($this->getQuestionIds($quiz));


    $started = "no";
    $completed = "no";
    $percent = $quiz->get('percent')->value;
    $description = $quiz->get('description')->value;
    $linkGenerator = $this->getLinkGenerator();


    $list = array();
    if($answeredQuestions == 0) {
      $takeQuizUrl = Url::fromRoute('entity.quiz.take_quiz', ['quiz' => $quiz->id()]);
      $link = $linkGenerator->generate('Take Quiz', $takeQuizUrl);
    }
    elseif ($answeredQuestions < $nrOfQuestions) {
      $list = $this->getResultsTable($quiz,$this->currentUser());
      $started = "yes";
      $latestQuestion = $this->getLatestAnsweredQuestionId($quiz, $this->currentUser());
      $takeQuizUrl = Url::fromRoute('entity.quiz.take_quiz',
        ['quiz' => $quiz->id()]);
      $link = $linkGenerator->generate('Continue Quiz',$takeQuizUrl);
    }
    else {
      $list = $this->getResultsTable($quiz,$this->currentUser());
      $completed = "yes";
      $started = "yes";

      $score = $this->evaluate($quiz, $this->currentUser());
      $maxScore = $this->getMaxScore($quiz);

      if($score/$maxScore >= $percent/100)
        $link = '<p>You passed this quiz with '. round($score/$maxScore, 2) * 100 .'%!</p>';
      else
        $link = '<p>You failed this quiz with '. round($score/$maxScore, 2) * 100 .'%.</p>';

      $link .= "<p>Your score is " .
        $score . " out of " .
        $maxScore . ' possible.</p>';
    }

    $markup = '<p>' . $description . '</p>';

    $markup .= '<p>Number of questions: ' . $nrOfQuestions . "</br>" .
      'Pass Percent: ' . $percent . '</br>' .
      'Started: ' . $started . '</br>' .
      'Completed: ' . $completed . '</p>' .
      $link;

    return array(
      '#theme' => 'quiz_list_results',
      '#results' => $list,
      '#markup' => $markup,
    );
  }

  /**
   * Builds a rendable array containing a table of answers for a quiz.
   *
   * @param \Drupal\quiz\QuizInterface $quiz
   * @param \Drupal\Core\Session\AccountInterface $user
   * @return array
   *  Returns a rendable array containing a table.
   */
  public function getResultsTable(QuizInterface $quiz, AccountInterface $user) {
    $answerStorage = static::entityTypeManager()->getStorage('answer');

    $header = array();
    $header['id'] = 'No.';
    $header['question'] = 'Question';
    $header['expected'] = 'Correct Answer';
    $header['received'] = 'Your Answer';

    $rows = array();
    $counter = 1;
    $answers = $this->getAnswers($quiz, $user);
    foreach ($answers as $answer) {
      /* @var $answer \Drupal\quiz\Entity\Answer */

      $question = $answer->getQuestion();
      /* @var $question \Drupal\quiz\Entity\Question */

      $rows[$answer->id()]['id'] = $counter;
      $rows[$answer->id()]['question'] = $answer->getQuestion()->get('question')->value;
      $rows[$answer->id()]['expected'] = '';
      $rows[$answer->id()]['received'] = '';
      $possible = array();

      //display the correct answer for the question
      if($question->getType() == 'multiple_choice_question') {
        foreach ($question->get('field_multiple_answer') as $field) {
          if ($field->value == 1) {
            $rows[$answer->id()]['expected'] .= $field->name . ', ';
          }
          $possible[] = $field->name;
        }
      }

      if($question->getType() == 'text_question')
        $rows[$answer->id()]['expected'] = $question->get('field_text_answer')->value;

      if($question->getType() == 'true_or_false') {
        if ($question->get('field_true_or_false')->value == 0) {
          $rows[$answer->id()]['expected'] = 'False';
        }
        else {
          $rows[$answer->id()]['expected'] = 'True';
        }
      }

      //display the user answer for the question
      if($answer->getType() == 'multiple_choice_answer') {
        foreach ($answer->get('field_multiple_answer') as $delta => $field) {
          if ($field->value == 1) {
            $rows[$answer->id()]['received'] .= $possible[$delta] . ', ';
          }
        }
      }

      if($answer->getType() == 'text_answer')
        $rows[$answer->id()]['received'] = $answer->get('field_text_answer')->value;

      if($answer->getType() == 'true_or_false') {
        //kint($answer->get('field_true_or_false')->delta);
        if($answer->get('field_true_or_false')->value == 0) {
          $rows[$answer->id()]['received'] = 'False';
        }
        else {
          $rows[$answer->id()]['received'] = 'True';
        }
      }
      $counter++;
    }

    $build['table'] = array(
      '#type' => 'table',
      '#header' => $header,
      '#title' => "what title",
      '#rows' => $rows,
      '#empty' => $this->t('You haven\'t answered to any question yet.'),
      '#cache' => [
        'contexts' => $answerStorage->getEntityType()->getListCacheContexts(),
        'tags' => $answerStorage->getEntityType()->getListCacheTags(),
      ],
    );

    return $build;
  }
  /**
   * Gets the maximum achievable score for a quiz.
   *
   * @param \Drupal\quiz\QuizInterface $quiz
   * @return int
   *  Returns score.
   */
  public function getMaxScore(QuizInterface $quiz) {
    $questions = $this->getQuestions($quiz);
    $score = 0;
    foreach ($questions as $question) {
      /* @var $question \Drupal\quiz\Entity\Question */
      $score += $question->get('score')->value;
    }
    return $score;
  }

  /**
   * Gets the score a user made from a quiz.
   *
   * @param \Drupal\quiz\QuizInterface $quiz
   * @param \Drupal\Core\Session\AccountInterface $user
   * @return int
   *  Returns the score.
   */
  public function evaluate(QuizInterface $quiz, AccountInterface $user) {
    $score = 0;
    $answers = $this->getAnswers($quiz, $user);
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
   * Deletes all answers to a quiz for a user. If no user is specified
   * the current user is assumed.
   *
   * @param \Drupal\quiz\QuizInterface $quiz
   * @param \Drupal\Core\Session\AccountInterface|NULL $user
   * @return array
   *  Returns a redirect to the canonical quiz page.
   */
  public function resetQuiz(QuizInterface $quiz, AccountInterface $user = NULL) {
    if($user == NULL) {
      $user = $this->currentUser();
    }
    $answers = $this->getAnswers($quiz, $user);
    $counter = 0;
    foreach ($answers as $answer) {
      /* @var $answer \Drupal\quiz\Entity\Answer */
      $answer->delete();
      $counter++;
    }
    drupal_set_message($this->t('Deleted %count answers.', [
      '%count' => $counter,
    ]));
    return $this->redirect('entity.quiz.canonical', [
      'quiz' => $quiz->id(),
    ]);
  }

  /**
   * Gets all the answers of an user for a question.
   *
   * @param \Drupal\quiz\QuestionInterface $question
   * @param \Drupal\Core\Session\AccountInterface $user
   * @return \Drupal\Core\Entity\EntityInterface[]
   *  Returns array of answers
   */
  public function getAnswersToQuestion(QuestionInterface $question, AccountInterface $user) {
    $answerStorage = static::entityTypeManager()->getStorage('answer');
    $query = $answerStorage->getQuery();
    $aids = $query
      ->Condition('user_id', $user->id())
      ->Condition('question', $question->id())
      ->execute();
    $answers = $answerStorage->loadMultiple($aids);
    return $answers;
  }

  /**
   * Gets the number of answers a question has for an user.
   *
   * @param \Drupal\quiz\QuestionInterface $question
   * @param \Drupal\Core\Session\AccountInterface|NULL $user
   * @return int
   *  Returns number of answers. 0 means the question is not answered.
   */
  public function isAnswered(QuestionInterface $question, AccountInterface $user) {
    $answers = $this->getAnswersToQuestion($question, $user);
    return count($answers);
  }

  public function newUserQuizStatus(QuizInterface $quiz) {

    $quizStatus = UserQuizStatus::create(array());
    $quizStatus->setQuiz($quiz);
    kint($quizStatus);
    $quizStatus->save();
    return array('#markup' => 'It works!</br>Entity ID: ' . $quizStatus->id());
  }

}