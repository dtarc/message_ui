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
use Drupal\message_ui\MessageAccessControlHandler;


class MessageUiController extends ControllerBase {

  /**
   * Display list of message types to create an instance for them.
   */
  // @todo - remove note: message_ui_create_new_message_instance_list in D7.
  public function getInstanceList() {
    $items = array();
    $access_controller = new MessageAccessControlHandler('message');
    $allowed_types = $access_controller->userCreateMessageAccess();

    if ($types = $this->getTypes()) {
      foreach ($types as $type => $title) {
        if ($allowed_types || (is_array($allowed_types) && $allowed_types[$type])) {
          $items[] = array(
            'type' => $type,
            'name' => $title,
            'internal_link' => \Drupal::l(ucfirst(str_replace('_', ' ', $type)), Url::fromUri('admin/content/message/create/' . str_replace('_', '-', $type))),
          );
        }
      }

      $item_list = array(
        '#theme' => 'instance_item_list',
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
   * Generates output for displaying a message entity.
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

    $view_builder = \Drupal::entityManager()->getViewBuilder('message');

    $build = $view_builder->view($message);

    $build += array(
      '#theme' => 'message',
      '#entity' => $message,
      '#view_mode' => 'full',
    );

    $build['#contextual_links']['message'] = array('message', array($message->id()));

    // Allow modules to modify the structured node.
    \Drupal::moduleHandler()->alter('message_ui_view', $build, $message);

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
    // $build = $this->getInstanceList();

    // @todo add access control for message_type, see message_ui_access_control.


    $content = array();

    // Only use node types the user has access to.
    foreach ($this->entityManager()->getStorage('message_type')->loadMultiple() as $type) {
      // @todo - get access control working below.
      // \Doctrine\Common\Util\Debug::dump($this->entityManager()->getAccessControlHandler('message')->createAccess($type->id()));
      // if ($this->entityManager()->getAccessControlHandler('message')->createAccess($type->id())) {
        $content[$type->id()] = $type;
      \Doctrine\Common\Util\Debug::dump($content);
      // }
    }

    // Bypass the node/add listing if only one content type is available.
    /* if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('message_ui.create_message_by_type', array('message_type' => $type->id()));
    } */

    \Doctrine\Common\Util\Debug::dump($content);

    return array(
      '#theme' => 'instance_item_list',
      '#content' => $content,
    );
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
  public function add($message_type) {
    \Doctrine\Common\Util\Debug::dump($message_type);
    // @todo add & pass arg as : MessageTypeInterface $message_type
    $account = $this->currentUser();

    // @todo add access control for message_type, see message_ui_access_control.

    // @todo : how should form args be wrapped in this case, or a better method?
    // $build = \Drupal::formBuilder()->getForm('Drupal\message_ui\Form\MessageForm');

    // return $build;
    return array();
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

    // @todo : how should form args be wrapped in this case, or a better method?
    $build = \Drupal::formBuilder()->getForm('Drupal\message_ui\Form\MessageForm', $message);

    // @todo build the proper array following message_ui_show_message.
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

    // @todo : how should form args be wrapped in this case, or a better method?
    $build = \Drupal::formBuilder()->getForm('Drupal\message_ui\Form\MessageDeleteConfirm', $message);

    return $build;
  }

  /**
   * Generates form output for deleting of multiple message entities.
   *
   * @param \Drupal\message\MessageInterface $message
   *   A message object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function deleteMultiple() {
    $account = $this->currentUser();

    // @todo add access control on user account, see message_ui_access_control.

    // @todo pass messages to be deleted in args?
    $build = \Drupal::formBuilder()->getForm('Drupal\message_ui\Form\DeleteMultiple');

    return $build;
  }
}