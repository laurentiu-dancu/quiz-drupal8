<?php

/**
 * @file
 * Contains question.page.inc..
 *
 * Page callback for Question entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Question templates.
 *
 * Default template: question.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_question(array &$variables) {
  // Fetch question Entity Object.
  $question = $variables['elements']['#question'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
