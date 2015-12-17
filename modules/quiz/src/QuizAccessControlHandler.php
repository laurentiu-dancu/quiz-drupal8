<?php

/**
 * @file
 * Contains \Drupal\quiz\QuizAccessControlHandler.
 */

namespace Drupal\quiz;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Quiz entity.
 *
 * @see \Drupal\quiz\Entity\Quiz.
 */
class QuizAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view quiz entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit quiz entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete quiz entities');

      case 'take':
        return AccessResult::allowedIfHasPermission($account, 'take quiz');
    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add quiz entities');
  }

}
