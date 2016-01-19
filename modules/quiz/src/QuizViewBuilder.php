<?php

/**
 * @file
 * Contains \Drupal\quiz\MessageViewBuilder.
 */

namespace Drupal\quiz;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;


class QuizViewBuilder extends EntityViewBuilder {
  public function view(EntityInterface $quiz, $view_mode = 'full', $langcode = NULL) {
    /* @var $quiz \Drupal\quiz\Entity\Quiz */
    $statuses = $quiz->getStatuses(\Drupal::getContainer()->get('current_user'));

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
    $renderer->addCacheableDependency($build, \Drupal::getContainer()->get('current_user'));
    return $build;
  }

  public function getResultsTable(UserQuizStatusInterface $state) {
    $answerStorage = \Drupal::getContainer()->get('entity_type.manager')->getStorage('answer');

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

}