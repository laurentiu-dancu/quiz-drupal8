<?php

/**
 * @file
 * Contains \Drupal\quiz\AnswerAccessControlHandler.
 */

namespace Drupal\quiz;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Answer entity.
 *
 * @see \Drupal\quiz\Entity\Answer.
 */
class AnswerAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view answer entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit answer entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete answer entities');
    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add answer entities');
  }

}
