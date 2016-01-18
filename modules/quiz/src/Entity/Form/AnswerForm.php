<?php

/**
 * @file
 * Contains \Drupal\quiz\Entity\Form\AnswerForm.
 */

namespace Drupal\quiz\Entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\Language;

/**
 * Form controller for Answer edit forms.
 *
 * @ingroup answer
 */
class AnswerForm extends ContentEntityForm {
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\quiz\Entity\Answer */

    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

    /* @var $question \Drupal\quiz\Entity\Question */

    $question = $entity->getQuestion();
    /* @var $question \Drupal\quiz\Entity\Question */

    /* @var $status \Drupal\quiz\Entity\UserQuizStatus */
    $status = $entity->getState();


    if ($status == NULL)
      return $this->redirect('entity.quiz.canonical_user', [
        'quiz' => $status->getQuiz()->id(),
      ]);

    if($status->getCurrentQuestionId() != $question->id())
      return $this->redirect('entity.quiz.canonical_user', [
        'quiz' => $status->getQuiz()->id(),
      ]);

    $count = $question->getUserQuizStateAnswersCount($this->currentUser(), $status);
    if($count) {
      $status->setLastQuestion($question);

      $status->save();
      return $this->redirect('entity.quiz.canonical_user', [
        'quiz' => $status->getQuiz()->id(),
      ]);
    }

    $form['langcode'] = array(
      '#title' => $this->t('Language'),
      '#type' => 'language_select',
      '#default_value' => $entity->langcode->value,
      '#languages' => Language::STATE_ALL,
    );

    $form['question'] = array(
      '#type' => 'label',
      '#title' => $entity->getQuestion()->get('question')->value,
      '#weight' => -5
    );

    // Only display a timer if the quiz is timed.
    //kint($quiz->get('time')->value);
    if($status->getQuiz()->get('time')->value > 0) {
      $form['timer'] = array(
        '#markup' => '<div id="js-timer"></div>',
        '#weight' => -9
      );

      $timeLeft = $status->getQuiz()->get('time')->value + $status->get('started')->value - time();

      //kint($timeLeft);
      // If we're out of time we mark the status as finished, no matter if some questions were left unanswered.
      if($timeLeft < 0) {
        $status->setFinished(time());


        $status->setScore($status->evaluate());
        $status->setMaxScore($status->getQuiz()->getMaxScore());
        $status->setPercent($status->getQuiz()->get('percent')->value);
        $status->setFinished(time());
        $status->setQuestionsCount(count($status->getQuiz()->getQuestions()));
        $status->save();

        //TODO: redirect to evaluation. And make status save a separate function in controller.
        return $this->redirect('entity.quiz.canonical', ['quiz' => $status->getQuiz()->id()]);
      }

      $form['#attached']['library'][] = 'quiz/quiz.timer';
      $form['#attached']['drupalSettings']['quiz']['endtime'] = $timeLeft - 1;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, FormStateInterface $form_state) {
    // Build the entity object from the submitted values.
    $entity = parent::submit($form, $form_state);

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    /* @var $entity \Drupal\quiz\Entity\Answer */
    $question = $entity->getQuestion();
    $status = $entity->getState();
    /* @var $question \Drupal\quiz\Entity\Question */
    $quiz = $status->getQuiz();
    $status = $quiz->getActiveStatus($this->currentUser());
    /* @var $status \Drupal\quiz\Entity\UserQuizStatus */
    $status->setLastQuestion($question);
    $entity->setState($status);
    $entity->save();
    $status->setAnswerCount($status->getAnswerCount() + 1);
    $status->save();
    /* @var $quiz \Drupal\quiz\Entity\Quiz */
    $form_state->setRedirect('entity.quiz.take_quiz', [
      'quiz' => $quiz->id(),
    ]);
    //}
  }

}
