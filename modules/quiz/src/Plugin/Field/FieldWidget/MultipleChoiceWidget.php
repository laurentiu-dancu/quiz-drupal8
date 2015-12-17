<?php

/**
 * @file
 * Contains \Drupal\quiz\Plugin\Field\FieldWidget\MultipleChoiceWidget.
 */

namespace Drupal\quiz\Plugin\Field\FieldWidget;

use Behat\Mink\Exception\Exception;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'multiple_choice_widget' widget.
 *
 * @FieldWidget(
 *   id = "multiple_choice_widget",
 *   label = @Translation("Multiple choice widget"),
 *   field_types = {
 *     "multiple_choice"
 *   }
 * )
 */
class MultipleChoiceWidget extends WidgetBase {
  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'size' => 60,
      'placeholder' => '',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];
    $elements['placeholder'] = array(
      '#type' => 'textfield',
      '#title' => t('Placeholder'),
      '#default_value' => $this->getSetting('placeholder'),
      '#description' => t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
    );

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    if (!empty($this->getSetting('placeholder'))) {
      $summary[] = t('Placeholder: @placeholder', array('@placeholder' => $this->getSetting('placeholder')));
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    //
    $element = [];


    $delta1 = $delta + 1;
    $element['value'] = array(
      '#type' => 'checkbox',
      '#title' => 'Option ' . $delta1,
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
    );
    $element['name'] = array(
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->name) ? $items[$delta]->name : NULL,
      '#size' => $this->getSetting('size'),
      '#placeholder' => $this->getSetting('placeholder'),
      '#maxlength' => $this->getFieldSetting('max_length'),
    );


    /*
    //answer mode
    $element['value'] = array(
      '#type' => 'checkbox',
      '#title' => $items[$delta]->value,
    );
*/

    return $element;
  }

}
