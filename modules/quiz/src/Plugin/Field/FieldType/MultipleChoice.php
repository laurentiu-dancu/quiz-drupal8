<?php

/**
 * @file
 * Contains \Drupal\quiz\Plugin\Field\FieldType\MultipleChoice.
 */

namespace Drupal\quiz\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'multiple_choice' field type.
 *
 * @FieldType(
 *   id = "multiple_choice",
 *   label = @Translation("Multiple Choice"),
 *   description = @Translation("Multiple choice field type."),
 *   default_widget = "multiple_choice_widget",
 *   default_formatter = "multiple_choice_formatter"
 * )
 */
class MultipleChoice extends FieldItemBase {
  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Prevent early t() calls by using the TranslatableMarkup.
    $properties['name'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Text'))
      ->setRequired(FALSE);

    $properties['value'] = DataDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Value'))
      ->setRequired(FALSE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = array(
      'columns' => array(
        'name' => array(
          'type' => 'varchar',
          'length' => 64,
        ),

        'value' => array(
          'type' => 'int',
        ),
      ),
    );

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraints = parent::getConstraints();

    $constraint_manager = \Drupal::typedDataManager()
      ->getValidationConstraintManager();
    $constraints[] = $constraint_manager->create('ComplexData', array(
      'name' => array(
        'Length' => array(
          'max' => 64,
          'maxMessage' => t('%name: may not be longer than @max characters.', array(
            '%name' => $this->getFieldDefinition()->getLabel(),
            '@max' => 64
          )),
        ),
      ),
    ));

    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $values['value'] = 0;
    $values['name'] = 'name';
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $elements = [];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('name')->getValue();
    return $value === NULL || $value === '';
  }

}
