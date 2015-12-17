<?php

/**
 * @file
 * Contains \Drupal\quiz\UserQuizStatusAccessControlHandler.
 */

namespace Drupal\quiz;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the User quiz status entity.
 *
 * @see \Drupal\quiz\Entity\UserQuizStatus.
 */
class UserQuizStatusAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view user quiz status entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit user quiz status entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete user quiz status entities');
    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add user quiz status entities');
  }

}
