<?php
/**
 * @file
 * Contains \Drupal\message_ui\Controller\MessageUiController.
 */

namespace Drupal\message_ui\Controller;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Controller\ControllerBase;
use Drupal\message\Entity\MessageType;
use Drupal\message\Entity\Message;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;


class MessageUiController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Constructs a MessageUiController object.
   */
  public function __construct() {
    $this->entityManager = \Drupal::entityManager();
  }

  /**
   * Display list of message types to create an instance for them.
   *
   */
  // @todo - remove note: message_ui_create_new_message_instance_list in D7.
  public function getAllowedInstanceList() {

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
   * Generates output of all message type entities with permission to create.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  protected function showTypes() {

    $build = array();
    // @todo : Use the following or MessageType's method? $this->entityManager()->getStorage('message_type')->loadMultiple()
    // Only use message types the user has access to.
    $types = $this->getAllowedInstanceList();
    foreach ($types as $type => $entity) {
      // @todo - get access control working below.
      // \Doctrine\Common\Util\Debug::dump($this->entityManager()->getAccessControlHandler('message')->createAccess($type->id()));
      // if ($this->entityManager()->getAccessControlHandler('message')->createAccess($type->id())) {
      /* @var $entity MessageType */
      $url = Url::fromUri('internal:/admin/content/messages/create/' . str_replace('_', '-', $type));
      $build[] = array(
        'type' => $type,
        'name' => $entity->label(),
        'internal_link' => Link::fromTextAndUrl(ucfirst(str_replace('_', ' ', $type)), $url),
      );
      //\Doctrine\Common\Util\Debug::dump($content);
      // }
    }

    // Bypass the admin/content/messages/create listing if only one content type is available.
    if (count($types) == 1) {
      $type = array_shift($types);
      return $this->redirect('message_ui.create_message_by_type', array('message_type' => $type->id()));
    }

    if ($build) {
      return array(
        '#theme' => 'instance_item_list',
        '#items' => $build,
        '#type' => 'ul'
      );
    }
    else {
      $url = Url::fromRoute('message.type_add');
      return array('#markup' => 'There are no messages types. You can create a new message type <a href="/' . $url->getInternalPath() . '">here</a>.');
    }
  }

  /**
   * Displays add content links for available message types.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   A render array for a list of the node types that can be added; however,
   *   if there is only one node type defined for the site, the function
   *   will return a RedirectResponse to the node add page for that one node
   *   type.
   */
  public function addPage() {
    // @todo: see NodeController for details.
    return $this->showTypes();
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

    $message = Message::create(array('type' => $message_type->id()));

    $form = $this->entityFormBuilder()->getForm($message);

    return $form;
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
    // @todo - create the path corresponding to below.
    // From devel module - admin/config/development/message_delete_multiple.
    // @todo pass messages to be deleted in args?
    $build = \Drupal::formBuilder()->getForm('Drupal\message_ui\Form\DeleteMultiple');

    return $build;
  }
}
