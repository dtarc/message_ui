<?php

/**
 * @file
 * Definition of Drupal\message_ui\Tests\MessageUiPermissions.
 */

namespace Drupal\Tests\message_ui\Functional;

use Drupal\Tests\message\Functional\MessageTestBase;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;
use Drupal\user\UserInterface;

/**
 * Abstract class for Message UI tests.
 */
abstract class AbstractTestMessageUi extends MessageTestBase {

  /**
   * The user account object.
   *
   * @var UserInterface
   */
  protected $account;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['message', 'message_ui'];

  /**
   * The user role.
   *
   * @var integer
   */
  protected $rid;

  /**
   * Grant to the user a specific permission.
   *
   * @param string $operation
   *   The template of operation - create, update, delete or view.
   */
  protected function grantMessageUiPermission($operation) {
    user_role_grant_permissions($this->rid, array($operation . ' foo message'));
  }

  /**
   * Set a config value.
   *
   * @param string $config
   *   The config name.
   * @param string $value
   *   The config value.
   * @param string $storage
   *   The storing of the configuration. Default to message.message.
   */
  protected function configSet($config, $value, $storage = 'message_ui.settings') {
    $this->container->get('config.factory')->getEditable($storage)->set($config, $value)->save();
  }

}
