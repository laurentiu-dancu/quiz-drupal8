<?php

/**
 * @file
 * Contains answer.page.inc..
 *
 * Page callback for Answer entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Answer templates.
 *
 * Default template: answer.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_answer(array &$variables) {
  // Fetch Answer Entity Object.
  $answer = $variables['elements']['#answer'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
