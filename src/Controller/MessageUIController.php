<?php
/**
 * @file
 * Contains \Drupal\message_ui\Controller\MessageUiController.
 */

namespace Drupal\message_ui\Controller;

use Drupal\Core\Url;
use Drupal\Core\Controller\ControllerBase;
use Drupal\message\MessageInterface;
use Drupal\message\Entity\MessageType;
use Drupal\message\Entity\Message;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;


class MessageUiController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Constructs a MessageUiController object.
   */
  public function __construct() {
    // @todo: should I be using Dependency Injection here?
    $this->entityManager = \Drupal::entityManager();
  }

  /**
   * Display list of message types to create an instance for them.
   *
   */
  // @todo - remove note: message_ui_create_new_message_instance_list in D7.
  public function getAllowedInstanceList() {
    // $access_controller = new MessageAccessControlHandler('message');
    // $allowed_types = $access_controller->userCreateMessageAccess();

    // @todo - replace this line with access controlled type list:
    $allowed_types = MessageType::loadMultiple();

    if ($types = MessageType::loadMultiple()) {
      foreach ($types as $type) {
        if ($allowed_types || (is_array($allowed_types) && array_key_exists($type, $allowed_types))) {
          return $allowed_types;
        }
      }
    }
    return FALSE;
  }

  /**
   * Generates output for displaying a message entity.
   *
   * @param Message $message
   *   A message object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function show(MessageInterface $message) {
    $message_view_controller = new MessageUiViewController($this->entityManager, \Drupal::service('renderer'));
    return $message_view_controller->view($message);
  }

  /**
   * Generates output of all message type entities with permission to create.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function showTypes() {
    // $account = $this->currentUser();

    // @todo add access control for message_type, see message_ui_access_control.

    $items = array();
    // @todo : Use the following or MessageType's method? $this->entityManager()->getStorage('message_type')->loadMultiple()
    // Only use node types the user has access to.
    foreach ($this->getAllowedInstanceList() as $type => $entity) {
      // @todo - get access control working below.
      // \Doctrine\Common\Util\Debug::dump($this->entityManager()->getAccessControlHandler('message')->createAccess($type->id()));
      // if ($this->entityManager()->getAccessControlHandler('message')->createAccess($type->id())) {
      /* @var $entity MessageType */
      $url = Url::fromUri('internal:/admin/content/messages/create/' . str_replace('_', '-', $type));
      $items[] = array(
        'type' => $type,
        'name' => $entity->label(),
        'internal_link' => \Drupal::l(ucfirst(str_replace('_', ' ', $type)), $url),
      );
      //\Doctrine\Common\Util\Debug::dump($content);
      // }
    }

    // Bypass the admin/content/messages/create listing if only one content type is available.
    /* if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('message_ui.create_message_by_type', array('message_type' => $type->id()));
    } */

    if ($items) {
      return array(
        '#theme' => 'instance_item_list',
        '#items' => $items,
        '#type' => 'ul'
      );
    }
    else {
      $url = Url::fromRoute('message.type_add');
      return t("There are no messages types. You can create a new message type <a href='$url'>here</a>.");
    }
  }

  /**
   * Generates form output for adding a new message entity of message_type.
   *
   * @todo pass : @param \Drupal\message\MessageTypeInterface $message_type
   *   A message_type object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function add($message_type) {
    $types = MessageType::loadMultiple();; // @todo : remove.
    $message_type = $types[str_replace('-', '_', $message_type)]; // @todo : remove.
    // \Doctrine\Common\Util\Debug::dump($message_type);
    // @todo add & pass arg as : MessageTypeInterface $message_type
    // $account = $this->currentUser();

    // @todo add access control for message_type, see message_ui_access_control.

    // @todo : how should form args be wrapped in this case, or a better method?
    // $build = \Drupal::formBuilder()->getForm('Drupal\message_ui\Form\MessageUiForm');

    // return $build;

    $message = Message::create(array('type' => $message_type->id()));

    $form = $this->entityFormBuilder()->getForm($message);

    return $form;
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
    // $account = $this->currentUser();

    // @todo add access control on user account, see message_ui_access_control.

    // @todo : how should form args be wrapped in this case, or a better method?
    $build = \Drupal::formBuilder()->getForm('Drupal\message_ui\Form\MessageUiForm', $message);

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
    // $account = $this->currentUser();

    // @todo add access control on user account, see message_ui_access_control.

    // @todo : how should form args be wrapped in this case, or a better method?
    $build = \Drupal::formBuilder()->getForm('Drupal\message_ui\Form\MessageUiDeleteConfirm', $message);

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
    // $account = $this->currentUser();

    // @todo add access control on user account, see message_ui_access_control.

    // @todo pass messages to be deleted in args?
    $build = \Drupal::formBuilder()->getForm('Drupal\message_ui\Form\MessageUiDeleteMultiple');

    return $build;
  }
}
