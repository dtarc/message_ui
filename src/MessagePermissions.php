<?php

/**
 * @file
 * Contains Drupal\message_ui\MessagePermissions.
 */

namespace Drupal\message_ui;

use Drupal\Core\Routing\UrlGeneratorTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\message\Entity\MessageTemplate;

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
   *   The message template permissions.
   *
   * @see \Drupal\user\PermissionHandlerInterface::getPermissions()
   */
  public function messageTemplatePermissions() {
    $perms = array();

    // Generate node permissions for all message templates.
    foreach (MessageTemplate::loadMultiple() as $template) {
      $perms += $this->buildPermissions($template);
    }

    return $perms;
  }

  /**
   * Builds a standard list of message permissions for a given template.
   *
   * @param \Drupal\message\Entity\MessageTemplate $template
   *   The machine name of the message template.
   *
   * @return array
   *   An array of permission names and descriptions.
   */
  protected function buildPermissions(MessageTemplate $template) {
    $template_params = array('%template_name' => $template->label());

    return [
      'view $template_id message' => [
        'title' => $this->t('%template_name: View a message instance', $template_params),
      ],
      'edit $template_id message' => [
        'title' => $this->t('%template_name: Edit a message instance', $template_params),
      ],
      'create $template_id message' => [
        'title' => $this->t('%template_name: Create a new message instance', $template_params),
      ],
      'delete $template_id message' => [
        'title' => $this->t('%template_name: Delete a message instance', $template_params),
      ],
    ];
  }

}
