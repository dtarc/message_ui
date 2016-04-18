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
use Drupal\message\MessageTypeInterface;


class MessageUiController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * @var \Drupal\Core\Entity\EntityAccessControlHandlerInterface
   */
  private $accessHandler;

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a MessageUiController object.
   */
  public function __construct() {
    $this->entityManager = \Drupal::entityManager();
    $this->accessHandler = \Drupal::entityManager()->getAccessControlHandler('message');
  }

  /**
   * Display list of message types to create an instance for them.
   *
   * @return array|bool
   */
  public function getAllowedInstanceList() {

    $allowed_types = [];

    if ($message_types = MessageType::loadMultiple()) {
      /* @var MessageType $message_type */
      foreach ($message_types as $message_type) {
        if ($this->accessHandler->access($message_type, 'create', \Drupal::currentUser())) {
          $allowed_types[$message_type->getLabel()] = $message_type;
        }
      }
      if (!empty($allowed_types)) {
        return $allowed_types;
      }
    }
    return FALSE;
  }

  /**
   * Generates output of all message type entities with permission to create.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   A render array for a list of the message types that can be added; however,
   *   if there is only one node type defined for the site, the function
   *   will return a RedirectResponse to the message add page for that one message
   *   type.
   */
  public function addPage() {

    // Only use message types the user has access to.
    $message_types = $this->getAllowedInstanceList();
    foreach ($message_types as $id => $entity) {
      /* @var $entity MessageType */
      $url = Url::fromUri('internal:/admin/content/messages/create/' . str_replace('_', '-', $id));
      $build[] = array(
        'type' => $id,
        'name' => $entity->label(),
        'internal_link' => Link::fromTextAndUrl(ucfirst(str_replace('_', ' ', $id)), $url),
      );
    }

    // Bypass the admin/content/messages/create listing if only one content type is available.
    if ($message_types && count($message_types) == 1) {
      /* @var $message_type MessageType */
      $message_type = array_shift($message_types);
      return $this->redirect('message_ui.create_message_by_type', array('message_type' => $message_type->id()));
    }

    if (!empty($build)) {
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
   * Generates form output for adding a new message entity of message_type.
   *
   * @param $message_type
   * @return array
   *   An array as expected by drupal_render().
   */
  public function add(MessageTypeInterface $message_type) {

    $message = Message::create(['type' => $message_type->id()]);
    $form = $this->entityFormBuilder()->getForm($message);

    return $form;
  }

  /**
   * Generates form output for deleting of multiple message entities.
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
