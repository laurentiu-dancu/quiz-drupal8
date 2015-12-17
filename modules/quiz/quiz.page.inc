<?php

/**
 * @file
 * Contains quiz.page.inc..
 *
 * Page callback for Quiz entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Quiz templates.
 *
 * Default template: quiz.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_quiz(array &$variables) {
  // Fetch Quiz Entity Object.
  $quiz = $variables['elements']['#quiz'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
