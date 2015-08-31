<?php
/**
 * @file
 * Contains \Drupal\message_ui\Controller\MessageUiController.
 */

namespace Drupal\message_ui\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\message\MessageInterface;
use Drupal\message\MessageTypeInterface;


class MessageUiController extends ControllerBase {

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
    /**
     * Display the message.

    function message_ui_show_message(Message $message) {
      $build = $message->view();

      $build += array(
        '#theme' => 'message',
        '#entity' => $message,
        '#view_mode' => 'full',
      );

      $build['#contextual_links']['message'] = array('message', array($message->identifier()));

      // Allow modules to modify the structured node.
      drupal_alter('message_ui_view', $build, $message);

      return $build;
    }
     * */

    // @todo build the proper array following message_ui_show_message.
    $build = array(
      '#type' => 'markup',
      '#markup' => t(__FUNCTION__ . ' method called correctly'),
    );
    return $build;
  }

  /**
   * Generates output of all message type entities with permission to create.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function show_types() {
    $account = $this->currentUser();

    // @todo build render array using message_ui_create_new_message_instance_list.

    // @todo add access control for message_type, see message_ui_access_control.
    $build = array(
      '#type' => 'markup',
      '#markup' => t(__FUNCTION__ . ' method called correctly'),
    );
    return $build;
  }

  /**
   * Generates form output for adding a new message entity of message_type.
   *
   * @param \Drupal\message\MessageTypeInterface $message_type
   *   A message_type object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function add(MessageTypeInterface $message_type) {
    $account = $this->currentUser();

    // @todo for specific message_type display /Drupal/message_ui/Form/MessageForm.

    // @todo add access control for message_type, see message_ui_access_control.
    $build = array(
      '#type' => 'markup',
      '#markup' => t(__FUNCTION__ . ' method called correctly'),
    );
    return $build;
  }

  /**
   * Generates form output for editing existing message entity of message_type.
   *
   * @param \Drupal\message\MessageInterface $message
   *   A message object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function edit(MessageInterface $message) {
    $account = $this->currentUser();

    // @todo add access control on user account, see message_ui_access_control.

    // @todo load and populate form at /Drupal/message_ui/Form/MessageForm.

    // @todo build the proper array following message_ui_show_message.
    $build = array(
      '#type' => 'markup',
      '#markup' => t(__FUNCTION__ . ' method called correctly'),
    );
    return $build;
  }

  /**
   * Generates form output for confirming delete of message entity.
   *
   * @param \Drupal\message\MessageInterface $message
   *   A message object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function delete(MessageInterface $message) {
    $account = $this->currentUser();

    // @todo add access control on user account, see message_ui_access_control.

    // @todo display confirm form at /Drupal/message_ui/Form/MessageDeleteConfirm.

    // @todo copy method at message_ui_instance_delete.
    $build = array(
      '#type' => 'markup',
      '#markup' => t(__FUNCTION__ . ' method called correctly'),
    );
    return $build;
  }

  public function delete_multiple() {
    $account = $this->currentUser();

    // @todo add access control on user account, see message_ui_access_control.

    // @todo display form at /Drupal/message_ui/Form/DeleteMultiple.

    // @todo copy method at message_ui_delete_multiple_messages.
    $build = array(
      '#type' => 'markup',
      '#markup' => t(__FUNCTION__ . ' method called correctly'),
    );
    return $build;
  }
}