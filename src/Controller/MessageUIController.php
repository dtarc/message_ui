<?php
/**
 * @file
 * Contains \Drupal\message_ui\Controller\MessageUiController.
 */

namespace Drupal\message_ui\Controller;

use Drupal\Core\Url;
use Drupal\Core\Controller\ControllerBase;
use Drupal\message\MessageInterface;
use Drupal\message\MessageTypeInterface;
use Drupal\message\Entity\MessageType;


class MessageUiController extends ControllerBase {

  /**
   * Display list of message types to create an instance for them.
   */
  // @todo - remove note: message_ui_create_new_message_instance_list in D7.
  public function getInstanceList() {
    $items = array();
    $allowed_types = message_ui_user_can_create_message();

    if ($types = message_ui_get_types()) {
      foreach ($types as $type => $title) {
        if ($allowed_types || (is_array($allowed_types) && $allowed_types[$type])) {
          $items[] = array('type' => $type, 'name' => $title,);
        }
      }

      $item_list = array(
        '#theme' => 'message_ui_create_message', // Should this be item_list?
        '#items' => $items,
        '#type' => 'ul',
      );

      return \Drupal::service('renderer')->render($item_list);
    }
    else {
      $url = Url::fromRoute('message.type_add');
      return t("There are no messages types. You can create a new message type <a href='$url'>here</a>.");
    }
  }

  /**
   * Get list of the messages.
   *
   * @todo : remove if unnecessary. Does MessageType:loadMultiple replace this?
   * @todo remove note: Previously message_ui_get_types().
   */
  public function getTypes() {

    $query = \Drupal::entityQuery('message_type');
    $result = $query->execute();

    if (empty($result['message_type'])) {
      return NULL;
    }

    $message_types = MessageType::loadMultiple($result);

    $list = array();

    foreach ($message_types as $message_type) {
      $list[$message_type->getLabel()] = $message_type->getDescription();
    }

    return $list;
  }

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
  public function showTypes() {
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
    /**
     * Deleting the message.

    function message_ui_instance_delete($form, &$form_state, Message $message) {
    // When the bundle is exported - display a message to the user.
    $form_state['#entity'] = $message;

    // Always provide entity id in the same form key as in the entity edit form.
    return confirm_form($form,
    t('Are you sure you want to delete the @type message instance?',
    array('@type' => $message->getType())),
    'admin/content/message',
    t('Are you sure you want to delete the message instance? This action cannot be undone.'),
    t('Delete'),
    t('Cancel'));
    }*/

    $build = array(
      '#type' => 'markup',
      '#markup' => t(__FUNCTION__ . ' method called correctly'),
    );
    return $build;
  }

  public function deleteMultiple() {
    $account = $this->currentUser();

    // @todo add access control on user account, see message_ui_access_control.

    // @todo display form at /Drupal/message_ui/Form/DeleteMultiple.

    $build = array(
      '#type' => 'markup',
      '#markup' => t(__FUNCTION__ . ' method called correctly'),
    );
    return $build;
  }
}