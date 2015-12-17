<?php

/**
 * @file
 * Contains \Drupal\quiz\Plugin\Field\FieldWidget\TrueFalseWidgetType.
 */

namespace Drupal\quiz\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'true_false_widget_type' widget.
 *
 * @FieldWidget(
 *   id = "true_false_widget_type",
 *   label = @Translation("True false widget type"),
 *   field_types = {
 *     "true_false_field"
 *   }
 * )
 */
class TrueFalseWidgetType extends WidgetBase {
  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
    ) + parent::defaultSettings();
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

    $options = array(1 => t('True'), 0 => t('False'));

    $element['value'] = $element + array(
        '#type' => 'radios',
        '#options' => $options,
        '#title' => 'Answer',
        '#weight' => 1,
      );

    return $element;
  }
}
