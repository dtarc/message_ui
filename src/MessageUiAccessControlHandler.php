<?php

/**
 * @file
 * Contains \Drupal\message_ui\MessageUiAccessControlHandler.
 */

namespace Drupal\message_ui;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the access control handler for the node entity type.
 *
 * @see \Drupal\node\Entity\Node
 * @ingroup node_access
 */
class MessageUiAccessControlHandler extends EntityAccessControlHandler implements EntityHandlerInterface {

  /**
   * Constructs a NodeAccessControlHandler object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   */
  public function __construct(EntityTypeInterface $entity_type) {
    parent::__construct($entity_type);
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type
    );
  }


  /**
   * {@inheritdoc}
   */
  public function access(EntityInterface $entity, $operation, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $account = $this->prepareUser($account);

    if ($account->hasPermission('bypass node access')) {
      $result = AccessResult::allowed()->cachePerPermissions();
      return $return_as_object ? $result : $result->isAllowed();
    }
    if (!$account->hasPermission('access content')) {
      $result = AccessResult::forbidden()->cachePerPermissions();
      return $return_as_object ? $result : $result->isAllowed();
    }
    $result = parent::access($entity, $operation, $account, TRUE)->cachePerPermissions();
    return $return_as_object ? $result : $result->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function createAccess($entity_bundle = NULL, AccountInterface $account = NULL, array $context = array(), $return_as_object = FALSE) {
    /*
    // @todo remove note: previously message_ui_user_can_create_message.
    if (empty($account)) {
      $account = \Drupal::currentUser();
    }

    $message_ui_controller = new MessageUiController();
    $types = $message_ui_controller->getTypes();

    // User have access to create any instances.
    if ($account->hasPermission('create any message instance')) {
      return TRUE;
    }

    // Check access for a specific message.
    if ($type) {

      // Didn't found that type.
      if (!in_array($type, $types)) {
        return FALSE;
      }

      if ($account->hasPermission('create a ' . $type . ' message instance')) {
        return TRUE;
      }
    }

    // Build list of arrays for the permissions.
    $permissions = array();
    foreach (array_keys($types) as $type) {
      $permissions[$type] = $account->hasPermission('create a ' . $type . ' message instance');
    }
    */

    $account = $this->prepareUser($account);

    if ($account->hasPermission('bypass node access')) {
      $result = AccessResult::allowed()->cachePerPermissions();
      return $return_as_object ? $result : $result->isAllowed();
    }
    if (!$account->hasPermission('access content')) {
      $result = AccessResult::forbidden()->cachePerPermissions();
      return $return_as_object ? $result : $result->isAllowed();
    }

    $result = parent::createAccess($entity_bundle, $account, $context, TRUE)->cachePerPermissions();
    return $return_as_object ? $result : $result->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $node, $operation, AccountInterface $account) {
    /** @var \Drupal\node\NodeInterface $node */

    // Fetch information from the node object if possible.
    $status = $node->isPublished();
    $uid = $node->getOwnerId();

    // Check if authors can view their own unpublished nodes.
    if ($operation === 'view' && !$status && $account->hasPermission('view own unpublished content') && $account->isAuthenticated() && $account->id() == $uid) {
      return AccessResult::allowed()->cachePerPermissions()->cachePerUser()->cacheUntilEntityChanges($node);
    }

    // Evaluate node grants.
    return $this->grantStorage->access($node, $operation, $account);
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIf($account->hasPermission('create ' . $entity_bundle . ' content'))->cachePerPermissions();
  }
}
