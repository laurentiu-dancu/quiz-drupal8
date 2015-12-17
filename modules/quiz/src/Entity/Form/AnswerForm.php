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
    $count = $question->getUserAnswersCount($this->currentUser());
    if($count) {
      return $this->redirect('entity.quiz.take_quiz_question', [
        'question' => $question->id(),
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
    $status = $entity->save();
    $question = $entity->getQuestion();
    /* @var $question \Drupal\quiz\Entity\Question */
    $quiz = $question->getQuiz();
    /* @var $quiz \Drupal\quiz\Entity\Quiz */
    $form_state->setRedirect('entity.quiz.take_quiz_question', [
      'quiz' => $quiz->id(),
      'question' => $question->id()
    ]);
    //}
  }

}
