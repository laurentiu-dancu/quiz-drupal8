<?php

/**
 * @file
 * Contains \Drupal\quiz\QuizSelectedQuestions
 */

namespace Drupal\quiz\Form;

use Drupal\Core\Config\PreExistingConfigException;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\quiz\QuestionInterface;
use Drupal\quiz\QuizInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list of selected questions for a quiz.
 */
class QuizSelectedQuestionsForm extends FormBase {
  use StringTranslationTrait;


  /**
   * The current quiz.
   *
   * @var \Drupal\quiz\Entity\Quiz
   */
  protected $quiz;

  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('quiz')
    );
  }

  /**
   * QuizSelectedQuestionsForm constructor.
   * @param \Drupal\quiz\QuizInterface $quiz
   */
  public function __construct(QuizInterface $quiz) {
    $this->quiz = $quiz;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'quiz_selected_questions';
  }

  /**
   * Builds the selected header
   *
   * @return array
   */
  public function buildHeader() {
    $header['selected'] = $this->t('Selected');
    $header['id'] = $this->t('Question ID');
    $header['name'] = $this->t('Name');
    $header['score'] = $this->t('Score');
    $header['type'] = $this->t('Type');
    //$header['operation'] = $this->t('Operation');
    return $header;
  }

  public function buildRow(QuestionInterface $question) {

    $row['selected'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Remove'),
    );

    $row['id']['#markup'] = $question->id();

    $row['name']['#markup'] = $this->l(
      $question->label(),
      new Url(
        'entity.question.edit_form', array(
          'question' => $question->id(),
        )
      )
    );

    $row['score'] = array(
      '#type' => 'textfield',
      '#size' => 4,
      '#maxlength' => 4,
      '#required' => TRUE,
      '#default_value' => $question->getScore($this->quiz),
    );

    $row['type']['#markup'] = $question->bundle();
    return $row;
  }



  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    /* @var $questionRelation \Drupal\quiz\Entity\QuizHasQuestion */
    /* @var $question \Drupal\quiz\Entity\Question */

    $questions = $this->quiz->getQuestions();
    $form['question'] = array();
    foreach ($questions as $question) {
      $form['question'][$question->id()] = $this->buildRow($question);
    }
    $form['question'] += array(
      '#type' => 'table',
      '#title' => 'Questions',
      '#header' => $this->buildHeader(),
    );

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Remove Selected / Update Score'),
      '#button_type' => 'primary',
    );


    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();
    foreach($values['question'] as $qid => $value) {
      if($value['selected'] != 0) {
        $this->quiz->removeQuestionById($qid);
      }
      elseif ($value['score'] != $this->quiz->getQuestionScoreById($qid));
        $this->quiz->setQuestionScoreById($qid, $value['score']);
    }
    return;
  }

  /**
   * Retrieves the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  protected function entityTypeManager() {
    if (!isset($this->entityTypeManager)) {
      $this->entityTypeManager = $this->container()->get('entity_type.manager');
    }
    return $this->entityTypeManager;
  }

  /**
   * Returns the service container.
   *
   * This method is marked private to prevent sub-classes from retrieving
   * services from the container through it. Instead,
   * \Drupal\Core\DependencyInjection\ContainerInjectionInterface should be used
   * for injecting services.
   *
   * @return \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   */
  private function container() {
    return \Drupal::getContainer();
  }

}
