<?php

/**
 * @file
 * Contains Drupal\message_ui\MessagePermissions.
 */

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
   *   The message type permissions.
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
      "view $type_id message" => array(
        'title' => $this->t('%type_name: View a message instance', $type_params),
      ),
      "edit $type_id message" => array(
        'title' => $this->t('%type_name: Edit a message instance', $type_params),
      ),
      "create $type_id message" => array(
        'title' => $this->t('%type_name: Create a new message instance', $type_params),
      ),
      "delete $type_id message" => array(
        'title' => $this->t('%type_name: Delete a message instance', $type_params),
      ),
    );
  }

}
