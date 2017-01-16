<?php

/**
 * @file
 * Definition of Drupal\message_ui\Tests\MessageUiMassiveHardCodedArguments.
 */

namespace Drupal\Tests\message_ui\Functional;

use Drupal\message\Entity\Message;
use Drupal\Tests\message\Functional\MessageTestBase;
use Drupal\user\UserInterface;

/**
 * Testing the update of the hard coded arguments in massive way.
 *
 * @group Message UI
 */
class MessageUiMassiveHardCodedArguments extends MessageTestBase {

  /**
   * The user object.
   *
   * @var UserInterface
   */
  public $user;

  /**
   * Modules to enable.
   *
   * @todo: verify whether entity_token is required in D8.
   *
   * @var array
   */
  public static $modules = ['message', 'message_ui'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->user = $this->drupalCreateUser();
  }

  /**
   * Test removal of added arguments.
   */
  public function testRemoveAddingArguments() {
    // Create Message Template of 'Dummy Test.
    $this->createMessageTemplate('dummy_message', 'Dummy test', 'This is a dummy message with a dummy message', array('Dummy message'));

    // @todo : validate / fix this config access.
    // Set a queue worker for the update arguments when updating a message
    // template.
    $this->configSet('update_tokens.update_tokens', TRUE, 'message_ui.settings');
    $this->configSet('update_tokens.how_to_act', 'update_when_item', 'message_ui.settings');

    // Create a message.
    $message_template = $this->loadMessageTemplate('dummy_message');
    /* @var $message Message */
    $message = Message::create(['template' => $message_template->id()]);

    $message
      ->setOwner($this->user)
      ->save();

    // @todo : check what args are returned in D7.
    $original_arguments = $message->getArguments();

    // @todo : validate / fix this config access.
    // Update message instance when removing a hard coded argument.
    $this->configSet('update_tokens.how_to_act', 'update_when_removed', 'message_ui.settings');

    // Set message text.
    $message_template->set('text', array('[message:user:name].'));
    $message_template->save();

    // Fire the queue worker.
    $queue = \Drupal::queue('message_ui_arguments');
    $item = $queue->claimItem();

    // @todo : check the below calls MessageUiArgumentsWorker::processItem.
    $queue->createItem($item->data);

    // Verify the arguments has changed.
    $message = Message::load($message->id());
    $this->assertTrue($original_arguments != $message->getArguments(), 'The message arguments has changed during the queue worker work.');

    // Creating a new message and her hard coded arguments.
    $message = Message::create(['template' => $message_template->id()]);

    $message
      ->setOwner($this->user)
      ->save();
    $original_arguments = $message->getArguments();

    // @todo : validate / fix this config access.
    // Process the message instance when adding hard coded arguments.
    $this->configSet('update_tokens.how_to_act', 'update_when_added', 'message_ui.settings');

    $message_template = $this->loadMessageTemplate('dummy_message');
    $message_template->set('text', array('@{message:user:name}.'));
    $message_template->save();

    // Fire the queue worker.
    $queue = \Drupal::queue('message_ui_arguments');
    $item = $queue->claimItem();

    // @todo: item->data currently returning null, do we need a check for that?
    // @todo : check the below calls MessageUiArgumentsWorker::createItem.
    $queue->createItem($item->data);

    // Verify the arguments has changed.
    $message = Message::load($message->id());
    $this->assertTrue($original_arguments == $message->getArguments(), 'The message arguments has changed during the queue worker work.');
  }

}
