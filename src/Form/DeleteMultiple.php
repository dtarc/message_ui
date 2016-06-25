<?php

/**
 * @file
 * Contains \Drupal\message_ui\Form\DeleteMultiple.
 */

namespace Drupal\message_ui\Form;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\message\Entity\Message;
use Drupal\message\Entity\MessageType;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\message_ui\Controller\MessageUiController;

/**
 * Provides a message deletion confirmation form.
 */
class DeleteMultiple extends ConfirmFormBase {

  /**
   * The array of messages to delete.
   *
   * @var array
   */
  protected $messages = array();

  /**
   * The tempstore factory.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The message storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $manager;

  /**
   * Constructs a DeleteMultiple form object.
   *
   * @param \Drupal\user\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\Entity\EntityManagerInterface $manager
   *   The entity manager.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, EntityManagerInterface $manager) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->storage = $manager->getStorage('message');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.private_tempstore'),
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'message_multiple_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return \Drupal::translation()
      ->formatPlural(count($this->messages), 'Are you sure you want to delete this item?', 'Are you sure you want to delete these items?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // @todo - below is from Message module, remove?
    $this->messages = $this->tempStoreFactory->get('message_multiple_delete_confirm')
      ->get(\Drupal::currentUser()->id());
    if (empty($this->messages)) {
      return new RedirectResponse($this->getCancelUrl()
        ->setAbsolute()
        ->toString());
    }

    $form['messages'] = array(
      '#theme' => 'item_list',
      '#items' => array_map(function (Message $message) {
        $params = array(
          '@id' => $message->id(),
          '@type' => $message->getType()->label(),
        );
        return t('Delete message ID @id fo type @type', $params);
      }, $this->messages),
    );
    $form = parent::buildForm($form, $form_state);

    $form['actions']['cancel']['#href'] = $this->getCancelRoute();

    // @todo - See "Delete multiple messages" from message_ui in D7.

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    //Submit handler - delete the messages.
    // Get the message IDs.
    $query = \Drupal::entityQuery('message');
    $result = $query
      ->condition('type', $form_state['values']['types'], 'IN')
      ->execute();

    if (empty($result['message'])) {
      // No messages found, return.
      drupal_set_message(t('No messages were found according to the parameters you entered'), 'error');
      return;
    }

    // Prepare the message IDs chunk array for batch operation.
    $chunks = array_chunk(array_keys($result['message']), 100);
    $operations = array();

    // @todo : update the operation below to new structure.
    foreach ($chunks as $chunk) {
      $operations[] = array('message_delete_multiple', array($chunk));
    }

    // Set the batch.
    $batch = array(
      'operations' => $operations,
      'title' => t('deleting messages.'),
      'init_message' => t('Starting to delete messages.'),
      'progress_message' => t('Processed @current out of @total.'),
      'error_message' => t('The batch operation has failed.'),
    );
    batch_set($batch);
    batch_process($_GET['q']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('message.messages');
  }
}
