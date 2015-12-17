<?php

/**
 * @file
 * Contains \Drupal\quiz\UserQuizStatusListBuilder.
 */

namespace Drupal\quiz;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of User quiz status entities.
 *
 * @ingroup quiz
 */
class UserQuizStatusListBuilder extends EntityListBuilder {
  use LinkGeneratorTrait;
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('User quiz status ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\quiz\Entity\UserQuizStatus */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.user_quiz_status.edit_form', array(
          'user_quiz_status' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
