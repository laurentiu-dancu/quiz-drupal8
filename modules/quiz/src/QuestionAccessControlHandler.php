<?php

/**
 * @file
 * Contains \Drupal\quiz\questionAccessControlHandler.
 */

namespace Drupal\quiz;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Question entity.
 *
 * @see \Drupal\quiz\Entity\Question.
 */
class QuestionAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view question entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit question entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete question entities');
    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add question entities');
  }

}
