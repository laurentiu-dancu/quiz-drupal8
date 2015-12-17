<?php

/**
 * @file
 * Contains \Drupal\quiz\Entity\Answer.
 */

namespace Drupal\quiz\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Answer entities.
 */
class AnswerViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['answer']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Answer'),
      'help' => $this->t('The Answer ID.'),
    );

    return $data;
  }

}
