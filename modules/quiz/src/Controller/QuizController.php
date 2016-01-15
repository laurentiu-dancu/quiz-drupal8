<?php

/**
 * @file
 * Contains \Drupal\quiz\Controller\QuizController.
 */

namespace Drupal\quiz\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\quiz\Entity\UserQuizStatus;
use Drupal\quiz\QuestionInterface;
use Drupal\quiz\QuestionListBuilder;
use Drupal\quiz\QuestionTypeInterface;
use Drupal\quiz\QuestionTypeListBuilder;
use Drupal\quiz\QuizInterface;
use Drupal\quiz\QuizTypeInterface;
use Drupal\Core\Link;
use Drupal\quiz\UserQuizStatusInterface;

/**
 * Class QuizController.
 *
 * @package Drupal\quiz\Controller
 */
class QuizController extends ControllerBase {

  /**
   * Adds an answer to a question.
   *
   * @param \Drupal\quiz\QuestionInterface $question
   * @return array
   *  Returns form for adding an answer.
   */
  public function addAnswer(QuestionInterface $question) {
    $answer = static::entityTypeManager()->getStorage('answer')->create(array(
      'type' => $question->get('answer_type')->target_id,
      'question' => $question->id(),
    ));

    $form = $this->entityFormBuilder()->getForm($answer);

    return $form;
  }

  /**
   * Builds a title for a question in format question x of n.
   *
   * @param \Drupal\quiz\QuestionInterface $question
   * @return string
   *  Returns title string.
   */
  public function addAnswerTitle(QuestionInterface $question) {
    $storage = static::entityTypeManager()->getStorage('question');
    $quizId = $question->getQuizId();
    $query = $storage->getQuery();
    $qids = $query
      ->Condition('quiz', $quizId)
      ->execute();
    $current = 0;
    foreach ($qids as $qid) {
      $current++;
      if($qid == $question->id())
        break;
    }
    return 'Question ' . $current . ' of ' . count($qids);
  }

  /**
   * Adds a new question.
   *
   * @param $quiz
   *    The quiz for which to add the question.
   * @param \Drupal\quiz\QuestionTypeInterface $question_type
   *    The type of question.
   * @return array
   *    New question type form.
   */
  public function addQuestion($quiz, QuestionTypeInterface $question_type) {
    $answer_type = NULL;
    if($question_type->id() == 'true_or_false')
      $answer_type = 'true_or_false';
    if($question_type->id() == 'text_question')
      $answer_type = 'text_answer';
    if($question_type->id() == 'multiple_choice_question')
      $answer_type = 'multiple_choice_answer';
    $question = static::entityTypeManager()->getStorage('question')->create(array(
      'type' => $question_type->id(),
      'answer_type' => $answer_type,
      'quiz' => $quiz,
    ));

    $form = $this->entityFormBuilder()->getForm($question);

    return $form;
  }

  use StringTranslationTrait;
  /**
   * Adds a new quiz. If no quiz type is provided,
   * the type is set automatically to basic_quiz.
   *
   * @param \Drupal\quiz\QuizTypeInterface|NULL $quiz_type
   * @return array
   *  Returns a new quiz form.
   */
  public function addQuiz(QuizTypeInterface $quiz_type = NULL) {
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
    $builder = new QuestionListBuilder($storage->getEntityType(), $storage);
    $builder->setIds($qids);

    $renderArray = $builder->render();
    return $renderArray;
  }

  /**
   * Lists the question types for a new question in a quiz.
   *
   * @param \Drupal\quiz\QuizInterface $quiz
   * @return array
   *  Rendable array
   */
  public function pickQuestionType(QuizInterface $quiz) {
    $storage = static::entityTypeManager()->getStorage('question_type');
    $builder = new QuestionTypeListBuilder($storage->getEntityType(), $storage);
    $builder->setQuizId($quiz->id());
    $builder->load();
    $result = $builder->render();
    return $result;
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
      // Take questions in order mechanism. Extend here to implement random order.
      foreach ($questions as $question) {
        /* @var $question \Drupal\quiz\Entity\Question */
        if ($status->getLastQuestionId() == NULL || $next) {
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
      elseif($status->isFinished() == 0) {
          $status->setScore($status->evaluate());
          $status->setMaxScore($quiz->getMaxScore());
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

    drupal_set_message($this->t('This quiz has no questions.'), 'warning');
    return $this->redirect('entity.quiz.canonical', [
      'quiz' => $quiz->id(),
    ]);
  }

  /**
   * Finds the IDs of questions for a quiz.
   *
   * @param \Drupal\quiz\QuizInterface $quiz
   * @return array
   *    Returns IDs of questions for a quiz
   */


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

    $renderer = \Drupal::service('renderer');

    $config = \Drupal::config('system.site');

    $link = '';
    $questions = $quiz->getQuestionCount();
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
          $attempt['table'] = $this->getResultsTable($status);

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
    $build['#cache'] = ['contexts' => ['user']];
    $renderer->addCacheableDependency($build, $config);
    $renderer->addCacheableDependency($build, $this->currentUser());
    return $build;
  }

  /**
   * Builds the table of answers for a given quiz state.
   *
   * @param \Drupal\quiz\UserQuizStatusInterface $state
   * @return array()
   *    Returns a rendable array.
   */
  public function getResultsTable(UserQuizStatusInterface $state) {
    $answerStorage = static::entityTypeManager()->getStorage('answer');

    $header = array();
    $header['id'] = 'No.';
    $header['question'] = 'Question';
    $header['expected'] = 'Correct Answer';
    $header['received'] = 'Your Answer';

    $rows = array();
    $answers = $state->getAnswers();

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

  /**
   * Deletes all answers to a quiz for a user. If no user is specified
   * the current user is assumed.
   *
   * @param \Drupal\quiz\QuizInterface $quiz
   * @param \Drupal\Core\Session\AccountInterface|NULL $user
   * @return array
   *  Returns a redirect to the canonical quiz page.
   *
   * @TODO: Also delete states, not just answer entities.
   * @deprecated Just don't use it, it doesn't work with statuses
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
}