<?php
/**
 * @file
 * Contains \Drupal\message_ui\Controller\MessageController.
 */

namespace Drupal\message_ui\Controller;

use Drupal\Core\Url;
use Drupal\Core\Controller\ControllerBase;
use Drupal\message\Entity\Message;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\message\MessageTypeInterface;


class MessageController extends ControllerBase implements ContainerInjectionInterface {

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
    $this->accessHandler = \Drupal::entityManager()
      ->getAccessControlHandler('message');
  }

  /**
   * Generates output of all message type entities with permission to create.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   A render array for a list of the message types that can be added; however,
   *   if there is only one node type defined for the site, the function
   *   will return a RedirectResponse to the message.add_by_type page for that one message
   *   type.
   */
  public function addPage() {
    $build = [
      '#theme' => 'message_add_list',
    ];

    $content = array();

    // Only use node types the user has access to.
    foreach ($this->entityManager()
               ->getStorage('message_type')
               ->loadMultiple() as $type) {
      $access = $this->entityManager()
        ->getAccessControlHandler('message')
        ->createAccess($type->id(), NULL, [], TRUE);
      if ($access->isAllowed()) {
        $content[$type->id()] = $type;
      }
    }

    // Bypass the admin/content/messages/create listing if only one content type is available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('message_ui.add', array('message_type' => $type->id()));
    }

    // Return build array.
    if (!empty($content)) {
      return ['#content' => $content];
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
    $build = \Drupal::formBuilder()
      ->getForm('Drupal\message_ui\Form\DeleteMultiple');

    return $build;
  }
}
