<?php

/**
 * @file
 * Contains user_quiz_status.page.inc..
 *
 * Page callback for User quiz status entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for User quiz status templates.
 *
 * Default template: user_quiz_status.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_user_quiz_status(array &$variables) {
  // Fetch UserQuizStatus Entity Object.
  $user_quiz_status = $variables['elements']['#user_quiz_status'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
