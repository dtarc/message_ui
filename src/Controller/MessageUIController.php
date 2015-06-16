<?php
/**
 * @file
 * Contains \Drupal\message_ui\Controller.
 */

namespace Drupal\message_ui\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\message\MessageInterface;
use Drupal\message\MessageTypeInterface;


class MessageUIController extends ControllerBase {

  /**
   * Generates output for viewing a message entity.
   *
   * @param \Drupal\message\MessageInterface $message
   *   A message object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function show(MessageInterface $message) {
    $account = $this->currentUser();

    // @todo add access control on user account, see message_ui_access_control.

    // @todo build the proper array following message_ui_show_message.
    $build = array(
      '#type' => 'markup',
      '#markup' => t(__FUNCTION__ . ' method called correctly'),
    );
    return $build;
  }

  public function add(MessageTypeInterface $message) {
    $account = $this->currentUser();

    // @todo check for message type or 'any' string in message arg.

    // @todo add access control, see message_ui_access_control.

    // @todo build render array using message_ui_create_new_message_instance_list.
    $build = array(
      '#type' => 'markup',
      '#markup' => t(__FUNCTION__ . ' method called correctly'),
    );
    return $build;
  }
}