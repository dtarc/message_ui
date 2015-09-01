<?php

/**
 * @file
 * Contains \Drupal\message_ui\MessageAccessControlHandler.
 */

namespace Drupal\message_ui;

// @todo : strip down all of the below to the minimum needed.

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Condition\ConditionAccessResolverTrait;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Executable\ExecutableManagerInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the access control handler for the message entity type.
 *
 * @see \Drupal\message\Entity\Message
 */
class MessageAccessControlHandler extends EntityAccessControlHandler implements EntityHandlerInterface {

  use ConditionAccessResolverTrait;

  /**
   * The condition plugin manager.
   *
   * @var \Drupal\Core\Executable\ExecutableManagerInterface
   */
  protected $manager;

  /**
   * The plugin context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * The context manager service.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('plugin.manager.condition'),
      $container->get('context.handler'),
      $container->get('context.repository')
    );
  }

  /**
   * Constructs the block access control handler instance
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Executable\ExecutableManagerInterface $manager
   *   The ConditionManager for checking visibility of blocks.
   * @param \Drupal\Core\Plugin\Context\ContextHandlerInterface $context_handler
   *   The ContextHandler for applying contexts to conditions properly.
   * @param \Drupal\Core\Plugin\Context\ContextRepositoryInterface $context_repository
   *   The lazy context repository service.
   */
  public function __construct(EntityTypeInterface $entity_type) {
    parent::__construct($entity_type);
  }

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /**
     * @todo : remove these comments post testing.
     *
     * Message module access callback.
     *
     * @param $operation
     *  The operation - create, view, update, delete.
     * @param $message
     *  The message object or message type.
     * @param User $user
     *  A user object. Optional.
     *
     * @return bool True or false.
     */

    if (empty($account)) {
      $account = \Drupal::currentUser();
    }

    // Get the message type from the function argument or from the message object.
    $type = is_object($entity) ? $entity->getEntityType() : $entity;

    // The user can manage any type of message.
    if ($account->hasPermission('bypass message access control')) {
      return MESSAGE_UI_ALLOW;
    }

    // Allow other modules to alter the access.
    $data = \Drupal::moduleHandler()->invokeAll('message_message_ui_access_control', $entity, $operation, $account);
    foreach ($data as $info) {
      return $info === MESSAGE_UI_ALLOW;
    }

    // Verify that the user can apply the op.
    if ($account->hasPermission($operation . ' any message instance', $account) || $account->hasPermission($operation . ' a ' . $type->getLabel() . ' message instance', $account)) {
      return MESSAGE_UI_ALLOW;
    }

    return MESSAGE_UI_DENY;
  }

  /**
   * Merges cacheable metadata from conditions onto the access result object.
   *
   * @param \Drupal\Core\Access\AccessResult $access
   *   The access result object.
   * @param \Drupal\Core\Condition\ConditionInterface[] $conditions
   *   List of visibility conditions.
   */
  protected function mergeCacheabilityFromConditions(AccessResult $access, array $conditions) {
    foreach ($conditions as $condition) {
      if ($condition instanceof CacheableDependencyInterface) {
        $access->addCacheTags($condition->getCacheTags());
        $access->addCacheContexts($condition->getCacheContexts());
        $access->setCacheMaxAge(Cache::mergeMaxAges($access->getCacheMaxAge(), $condition->getCacheMaxAge()));
      }
    }
  }

}
