<?php

/**
 * @file
 * Contains \Drupal\quiz\Controller\QuizController.
 */

namespace Drupal\quiz\Controller;

use DateTime;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityType;
use Drupal\Core\Routing\UrlGeneratorTrait;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\Plugin\DataType\Timestamp;
use Drupal\Core\Url;
use Drupal\quiz\AnswerListBuilder;
use Drupal\quiz\Entity\UserQuizStatus;
use Drupal\quiz\QuestionInterface;
use Drupal\quiz\QuestionListBuilder;
use Drupal\quiz\QuizInterface;
use Drupal\quiz\QuizTypeInterface;
use Drupal\Core\Link;
use Drupal\quiz\UserQuizStatusInterface;
use Drupal\user\UserInterface;

/**
 * Class QuizController.
 *
 * @package Drupal\quiz\Controller
 */
class QuizController extends ControllerBase {
  use StringTranslationTrait;
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

    $attemptCount = $quiz->get('attempts')->value;

    if($attemptCount > 0) {
      $statuses = $quiz->getStatuses($this->currentUser());
      if (count($statuses) >= $attemptCount) {
        $status = $quiz->getActiveStatus($this->currentUser());
        if($status == NULL) {
          drupal_set_message($this->t('Maximum attempts for this quiz reached.'), 'warning');
          return $this->redirect('entity.quiz.canonical', [
            'quiz' => $quiz->id(),
          ]);
        }
      }
    }

    $questions = $quiz->getQuestions();
    if (count($questions) != 0) {

      $status = $quiz->getActiveStatus($this->currentUser());
      //while(1);
      // If no open quiz session is found, create one.
      if($status == NULL) {
        $status = UserQuizStatus::create(array());
        $status->setQuiz($quiz);
        $status->setFinished(0);
        $status->setAnswerCount(0);
        $status->save();
      }

      $next = 0;
      $nextQuestion = NULL;
      foreach ($questions as $question) {
        /* @var $question \Drupal\quiz\Entity\Question */
        if ($status->getLastQuestionId() == NULL || $next) {
          //kint($status->getLastQuestionId());
          $nextQuestion = $question;
          break;
        }
        if ($status->getLastQuestionId() == $question->id()) {
          $next = 1;
        }

      }

      // There is a question to be answered case.
      if ($nextQuestion != NULL) {
        $status->setCurrentQuestion($nextQuestion);
        $status->save();
        return $this->redirect('entity.answer.add_answer', [
          'question' => $nextQuestion->id(),
        ]);
      }
      // Quiz completed case.
      else {
        //kint($status->isFinished());

        if($status->isFinished() == 0) {
          $status->setScore($status->evaluate());
          $status->setMaxScore($this->getMaxScore($quiz));
          $status->setPercent($quiz->get('percent')->value);

          $status->setQuestionsCount(count($quiz->getQuestions()));
          $status->setFinished(time());
          $status->setCurrentQuestion();
          $status->save();
        }
        return $this->redirect('entity.quiz.canonical', [
          'quiz' => $quiz->id(),
        ]);
      }
    }

    drupal_set_message($this->t('This quiz has no questions.'), 'warning');
    return $this->redirect('entity.quiz.canonical', [
      'quiz' => $quiz->id(),
    ]);
  }

  /**
   * @param \Drupal\quiz\UserQuizStatusInterface $state
   * @param \Drupal\Core\Session\AccountInterface $user
   * @return array
   */
  public function getAnswers(UserQuizStatusInterface $state, AccountInterface $user) {
    $answerStorage = static::entityTypeManager()->getStorage('answer');
    $query = $answerStorage->getQuery();
    $aids = $query
      ->Condition('user_id', $user->id())
      ->Condition('user_quiz_status', $state->id())
      ->execute();
    $answers = $answerStorage->loadMultiple($aids);



    /*
    foreach ($answers as $answer) {
      /* @var $answer \Drupal\quiz\Entity\Answer
      if ($answer->getUserQuizStatusId() == $state->id()) {
        $answerArray[] = $answer;
      }
    }
  */
    return $answers;
  }

  public function getAllAnswers(QuizInterface $quiz, AccountInterface $user) {
    $answerStorage = static::entityTypeManager()->getStorage('answer');
    $query = $answerStorage->getQuery();
    $aids = $query
      ->Condition('user_id', $user->id())
      ->execute();
    $answers = $answerStorage->loadMultiple($aids);
    $answerArray = array();
    foreach ($answers as $answer) {
      /* @var $answer \Drupal\quiz\Entity\Answer */
      if ($answer->getQuestion()->getQuiz()->id() == $quiz->id()) {
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
    $answers = $this->getAllAnswers($quiz, $user);
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
   * Makes a rendable array containing the page with quiz status.
   *
   * @param \Drupal\quiz\QuizInterface $quiz
   * @return array
   */
  public function userDisplayQuiz(QuizInterface $quiz) {
    $statuses = $quiz->getStatuses($this->currentUser());

    $build = array();

    $link = '';
    $questions = count($this->getQuestionIds($quiz));
    $percent = $quiz->get('percent')->value;
    $timeLimit = $quiz->get('time')->value;
    if($timeLimit == 0 || $timeLimit == NULL)
      $timeLimit = 0;
    $description = $quiz->get('description')->value;
    $attemptLimit = $quiz->get('attempts')->value;
    $attemptTimes = count($statuses);

    $attempts = array();
    // The quiz has been attempted at least once
    if(!empty($statuses)) {
      $status = array_pop($statuses);
      $statuses[] = $status;
      // If the quiz was attempted and finished
      if ($status->isFinished() && ($attemptLimit == 0 || $attemptLimit > $attemptTimes)) {
        $url = Url::fromRoute('entity.quiz.take_quiz', ['quiz' => $quiz->id()]);
        $href = Link::fromTextAndUrl('Retake Quiz', $url)->toRenderable();
        $link = $href;
      }
      // If the last attempt is not yet finished
      elseif ($attemptLimit == 0 || $attemptLimit > $attemptTimes) {
        $url = Url::fromRoute('entity.quiz.take_quiz', ['quiz' => $quiz->id()]);
        $href = Link::fromTextAndUrl('Continue Quiz', $url)->toRenderable();
        $link = $href;
      }
      /* @var $status \Drupal\quiz\Entity\UserQuizStatus */

      $c = 0;
      $attempts['#prefix'] = "<div id='tabs'>";
      $attempts['#suffix'] = "</div>";

      $attempts['tabs']['#prefix'] = "<ul class='tabs'>";
      $attempts['tabs']['#suffix'] = "</ul>";

      $attempts['attempts']['#prefix'] = "<div class='attempts'>";
      $attempts['attempts']['#suffix'] = "</div>";

      foreach ($statuses as $status) {
        // Only generate reports for finished quizzes
        if ($status->isFinished()) {
          $score = $status->getScore();
          $maxScore = $status->getMaxScore();
          $percent = $status->getPercent();
          $timeTaken = $status->getFinished() - $status->getStarted();
          $attempted = $finished = count($statuses);

          $attempt['status']['time']['#markup'] = $this->t('Time taken: @time',
            ['@time' => gmdate("H:i:s", $timeTaken)]);
          $attempt['status']['time']['#suffix'] = '<br>';
          $attempt['status']['score']['#markup'] = $this->t('You scored @score out of @max points', [
            '@score' => $score,
            '@max' => $maxScore
          ]);
          $attempt['status']['score']['#suffix'] = '<br>';
          if ($score / $maxScore >= $percent / 100) {
            $attempt['status']['pass']['#markup'] = $this->t('You passed this quiz with @percents%!',
              ['@percents' => round($score / $maxScore, 2) * 100]);
          }
          else {
            $attempt['status']['pass']['#markup'] = $this->t('You failed this quiz with @percents%.',
              ['@percents' => round($score / $maxScore, 2) * 100]);
          }
          $attempt['status']['pass']['#prefix'] = '<br>';
          $attempt['status']['pass']['#suffix'] = '<br>';
          $attempt['status']['#prefix'] = '<div>';
          $attempt['status']['#suffix'] = '</div>';
          $attempt['table'] = $this->getResultsTable($status, $this->currentUser());

          $attempts['tabs']['#weight'] = -1;
          $attempts['tabs'][++$c]['#markup'] = $this->t("Attempt @id",['@id' => $c]);
          $attempts['tabs'][$c]['#prefix'] = "<li><a href='#tabs-" . $c . "'>";
          $attempts['tabs'][$c]['#suffix'] = '</a></li>';

          $attempts['attempts'][$c] = $attempt;
          $attempts['attempts'][$c]['#prefix'] = $this->t("<div id='tabs-@id'>",['@id' => $c]);
          $attempts['attempts'][$c]['#suffix'] = "</div>";
        }

      }
    }

    // If the quiz was never attempted
    else {
      $url = Url::fromRoute('entity.quiz.take_quiz', ['quiz' => $quiz->id()]);
      $href = Link::fromTextAndUrl('Take Quiz', $url)->toRenderable();
      $link = $href;
    }

    $build['details']['description']['#markup'] = $this->t('@description',['@description' => $description]);
    $build['details']['description']['#prefix'] = '<p>';
    $build['details']['description']['#suffix'] = '</p>';

    $build['details']['status']['#prefix'] = '<p>';
    $build['details']['status']['#suffix'] = '</p>';

    $build['details']['status']['questions']['#markup'] = $this->t('Number of questions: @questions',['@questions' => $questions]);
    $build['details']['status']['questions']['#suffix'] = '<br>';

    $build['details']['status']['percent']['#markup'] = $this->t('Pass rate: @percent%',['@percent' => $percent]);
    $build['details']['status']['percent']['#suffix'] = '<br>';

    $build['details']['status']['time']['#markup'] = $this->t('Time limit: @time',['@time' => $timeLimit > 0 ? gmdate("H:i:s", $timeLimit) : 'No']);
    $build['details']['status']['time']['#suffix'] = '<br>';

    $build['details']['status']['allowed']['#markup'] = $this->t('Attempts allowed: @times',['@times' => $attemptLimit > 0 ? $attemptLimit : 'Unlimited']);
    $build['details']['status']['allowed']['#suffix'] = '<br>';

    $build['details']['status']['attempts']['#markup'] = $this->t('Attempted @times times.',['@times' => $attemptTimes]);

    $build['details']['link'] = $link;
    $build['details']['link']['#prefix'] = '<p>';
    $build['details']['link']['#suffix'] = '</p>';

    $build['results'] = $attempts;

    $build['results']['#attached']['library'][] = 'quiz/quiz.tabs';
    return $build;
  }

  public function getResultsTable(UserQuizStatusInterface $state, AccountInterface $user) {
    $answerStorage = static::entityTypeManager()->getStorage('answer');

    $header = array();
    $header['id'] = 'No.';
    $header['question'] = 'Question';
    $header['expected'] = 'Correct Answer';
    $header['received'] = 'Your Answer';

    $rows = array();
    $answers = $this->getAnswers($state, $user);

    $c = 1;
    foreach ($answers as $answer) {
      /* @var $answer \Drupal\quiz\Entity\Answer */

      $question = $answer->getQuestion();
      /* @var $question \Drupal\quiz\Entity\Question */

      $rows[$answer->id()]['id'] = $c++;
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
    }

    $build['table'] = array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('You didn\'t answer any question.'),
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
   * @param \Drupal\quiz\UserQuizStatusInterface $state
   * @param \Drupal\Core\Session\AccountInterface $user
   * @return int
   *
   * @deprecated Use $state->evaluate() instead.
   */
  public function evaluate(UserQuizStatusInterface $state, AccountInterface $user) {
    $score = 0;
    $answers = $this->getAnswers($state, $user);
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
    $answers = $this->getAllAnswers($quiz, $user);
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
   *
   * @deprecated Use $question->getAnswers() instead.
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
   *
   * @deprecated Use $question->getAnswers() instead.
   */
  public function isAnswered(QuestionInterface $question, AccountInterface $user) {
    $answers = $this->getAnswersToQuestion($question, $user);
    return count($answers);
  }

  public function newUserQuizStatus(QuizInterface $quiz) {

    $quizStatus = UserQuizStatus::create(array());
    $quizStatus->setQuiz($quiz);
    $quizStatus->save();
    return array('#markup' => 'It works!</br>Entity ID: ' . $quizStatus->id());
  }

}