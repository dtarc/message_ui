<?php

/**
 * @file
 * Contains \Drupal\message_ui\Access\MessageUiAddAccessCheck.
 */

namespace Drupal\message_ui\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\message\MessageTypeInterface;

/**
 * Determines access to for message add pages.
 *
 * @ingroup message_access
 */
class MessageUiAddAccessCheck implements AccessInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a EntityCreateAccessCheck object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * Checks access to the node add page for the message type.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Drupal\message\MessageTypeInterface $message_type
   *   (optional) The message type. If not specified, access is allowed if there
   *   exists at least one message type for which the user may create a message.
   *
   * @return string
   *   A \Drupal\Core\Access\AccessInterface constant value.
   */
  public function access(AccountInterface $account, MessageTypeInterface $message_type = NULL) {
    $access_control_handler = $this->entityManager->getAccessControlHandler('message');
    // If checking whether a node of a particular type may be created.
    if ($account->hasPermission('administer content types')) {
      return AccessResult::allowed()->cachePerPermissions();
    }
    if ($message_type) {
      return $access_control_handler->createAccess($message_type->id(), $account, [], TRUE);
    }
    // If checking whether a node of any type may be created.
    foreach ($this->entityManager->getStorage('message_type')->loadMultiple() as $message_type) {
      if (($access = $access_control_handler->createAccess($message_type->id(), $account, [], TRUE)) && $access->isAllowed()) {
        return $access;
      }
    }

    // No opinion.
    return AccessResult::neutral();
  }

}
