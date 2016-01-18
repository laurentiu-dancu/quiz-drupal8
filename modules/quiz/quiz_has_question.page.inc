<?php

/**
 * @file
 * Contains quiz_has_question.page.inc..
 *
 * Page callback for Quiz has question entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Quiz has question templates.
 *
 * Default template: quiz_has_question.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_quiz_has_question(array &$variables) {
  // Fetch QuizHasQuestion Entity Object.
  $quiz_has_question = $variables['elements']['#quiz_has_question'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
