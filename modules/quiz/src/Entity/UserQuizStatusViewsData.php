<?php

/**
 * @file
 * Contains \Drupal\quiz\Entity\UserQuizStatus.
 */

namespace Drupal\quiz\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for User quiz status entities.
 */
class UserQuizStatusViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['user_quiz_status']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('User quiz status'),
      'help' => $this->t('The User quiz status ID.'),
    );

    return $data;
  }

}
