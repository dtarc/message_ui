<?php

/**
 * @file
 * Contains \Drupal\message_ui\Access\MessageUiAccessCheck.
 */

namespace Drupal\message_ui\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\message\MessageInterface;
use Symfony\Component\Routing\Route;

/**
 * Determines access to for message view pages.
 *
 * @ingroup message_access
 */
class MessageUiAccessCheck implements AccessInterface {

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
   * Checks routing access for the message.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Drupal\message\MessageInterface $message
   *   (optional) A message object.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(Route $route, AccountInterface $account, MessageInterface $message = NULL) {
    if ($operation = $route->getRequirement('_message_ui_access')) {
      if ($account->hasPermission($operation . ' any message type')) {
        return AccessResult::allowed();
      }
      return $message->access($operation, $account, TRUE);
    }
    return AccessResult::neutral();
  }
}
