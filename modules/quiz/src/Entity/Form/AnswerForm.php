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
    $question = $entity->getQuestion();
    /* @var $question \Drupal\quiz\Entity\Question */

    $question = $entity->getQuestion();
    /* @var $question \Drupal\quiz\Entity\Question */
    $quiz = $question->getQuiz();

    $status = $quiz->getActiveStatus($this->currentUser());
    /* @var $status \Drupal\quiz\Entity\UserQuizStatus */

    if ($status == NULL)
      return $this->redirect('entity.quiz.canonical_user', [
        'quiz' => $question->getQuiz()->id(),
      ]);

    if($status->getCurrentQuestionId() != $question->id())
      return $this->redirect('entity.quiz.canonical_user', [
        'quiz' => $question->getQuiz()->id(),
      ]);

    $count = $question->getUserQuizStateAnswersCount($this->currentUser(), $status);
    if($count) {
      $status->setLastQuestion($question);
      $status->save();
      return $this->redirect('entity.quiz.canonical_user', [
        'quiz' => $question->getQuiz()->id(),
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
    /* @var $question \Drupal\quiz\Entity\Question */
    $quiz = $question->getQuiz();
    $status = $quiz->getActiveStatus($this->currentUser());
    /* @var $status \Drupal\quiz\Entity\UserQuizStatus */
    $status->setLastQuestion($question);
    $entity->setUserQuizStatus($status);
    $entity->save();
    $answers = $status->getTotalAnswerCount();
    if($answers = NULL)
      $answers = 0;
    $status->setTotalAnswerCount($answers + 1);
    $status->save();
    /* @var $quiz \Drupal\quiz\Entity\Quiz */
    $form_state->setRedirect('entity.quiz.take_quiz', [
      'quiz' => $quiz->id(),
    ]);
    //}
  }

}
