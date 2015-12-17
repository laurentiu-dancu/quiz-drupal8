<?php

/**
 * @file
 * Contains \Drupal\quiz\Entity\Quiz.
 */

namespace Drupal\quiz\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Quiz entities.
 */
class QuizViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['quiz']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Quiz'),
      'help' => $this->t('The Quiz ID.'),
    );

    return $data;
  }

}
