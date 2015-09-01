<?php

/**
 * @file
 * Contains \Drupal\message_ui\MessagePermissions.
 */

// @todo : remove permissions not available in D7.

namespace Drupal\message_ui;

use Drupal\Core\Routing\UrlGeneratorTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\message\Entity\MessageType;

/**
 * Defines a class containing permission callbacks.
 */
class MessagePermissions {

  use StringTranslationTrait;
  use UrlGeneratorTrait;

  /**
   * Gets an array of message type permissions.
   *
   * @return array
   *   The node type permissions.
   *   @see \Drupal\user\PermissionHandlerInterface::getPermissions()
   */
  public function messageTypePermissions() {
    $perms = array();
    // Generate node permissions for all node types.
    foreach (MessageType::loadMultiple() as $type) {
      $perms += $this->buildPermissions($type);
    }

    return $perms;
  }

  /**
   * Builds a standard list of message permissions for a given type.
   *
   * @param \Drupal\message\Entity\MessageType $type
   *   The machine name of the message type.
   *
   * @return array
   *   An array of permission names and descriptions.
   */
  protected function buildPermissions(MessageType $type) {
    $type_id = $type->id();
    $type_params = array('%type_name' => $type->label());

    return array(
      "create $type_id message" => array(
        'title' => $this->t('%type_name: Create new message', $type_params),
      ),
      "edit own $type_id message" => array(
        'title' => $this->t('%type_name: Edit own message', $type_params),
      ),
      "edit any $type_id message" => array(
        'title' => $this->t('%type_name: Edit any message', $type_params),
      ),
      "delete own $type_id message" => array(
        'title' => $this->t('%type_name: Delete own message', $type_params),
      ),
      "delete any $type_id message" => array(
        'title' => $this->t('%type_name: Delete any message', $type_params),
      ),
    );
  }

}
