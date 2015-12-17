<?php

/**
 * @file
 * Contains \Drupal\quiz\Plugin\Field\FieldWidget\MultipleAnswerWidget.
 */

namespace Drupal\quiz\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'multiple_answer_widget' widget.
 *
 * @FieldWidget(
 *   id = "multiple_answer_widget",
 *   label = @Translation("Multiple answer widget"),
 *   field_types = {
 *     "multiple_answer"
 *   }
 * )
 */
class MultipleAnswerWidget extends WidgetBase {
  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = [];

    $question = $items->getEntity();
    /* @var $question \Drupal\quiz\Entity\Question */
    //kint($question);
    $element['value'] = array(
      '#type' => 'checkbox',
      '#title' => $question->get('field_multiple_answer')[$delta]->name,
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function form(FieldItemListInterface $items, array &$form, FormStateInterface $form_state, $get_delta = NULL) {
    //kint($items);
    $answer = $items->getEntity();
    /* @var $answer \Drupal\quiz\Entity\Answer */
    $question = $answer->getQuestion();
    /* @var $question \Drupal\quiz\Entity\Question */

    $items = $question->get('field_multiple_answer');

    $field_name = $this->fieldDefinition->getName();
    $parents = $form['#parents'];

    // Store field information in $form_state.
    if (!static::getWidgetState($parents, $field_name, $form_state)) {
      $field_state = array(
        'items_count' => count($items),
        'array_parents' => array(),
      );
      static::setWidgetState($parents, $field_name, $form_state, $field_state);
    }

    // Collect widget elements.
    $elements = array();

    $title = $this->fieldDefinition->getLabel();
    $max = count($items);
    for ($delta = 0; $delta < $max; $delta++) {
      $element = [];
      $elements[$delta] = $this->formSingleElement($items, $max - $delta - 1, $element, $form, $form_state);
      $elements[$delta]['#weight'] = $max - $delta;
    }
    //$elements = array_reverse($elements);


    // Populate the 'array_parents' information in $form_state->get('field')
    // after the form is built, so that we catch changes in the form structure
    // performed in alter() hooks.
    $elements['#after_build'][] = array(get_class($this), 'afterBuild');
    $elements['#field_name'] = $field_name;
    $elements['#field_parents'] = $parents;
    // Enforce the structure of submitted values.
    $elements['#parents'] = array_merge($parents, array($field_name));
    // Most widgets need their internal structure preserved in submitted values.
    $elements += array('#tree' => TRUE);

    return array(
      // Aid in theming of widgets by rendering a classified container.
      '#type' => 'container',
      // Assign a different parent, to keep the main id for the widget itself.
      '#parents' => array_merge($parents, array($field_name . '_wrapper')),
      '#attributes' => array(
        'class' => array(
          'field--type-' . Html::getClass($this->fieldDefinition->getType()),
          'field--name-' . Html::getClass($field_name),
          'field--widget-' . Html::getClass($this->getPluginId()),
        ),
      ),
      'widget' => $elements,
    );
  }

}
