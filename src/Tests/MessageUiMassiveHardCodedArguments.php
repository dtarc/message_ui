<?php

/**
 * @file
 * Definition of Drupal\message_ui\Tests\MessageUiMassiveHardCodedArguments.
 */

namespace Drupal\message_ui\Tests;

use Drupal\message\Tests\MessageTestBase;
use Drupal\message\Entity\MessageType;

/**
 * Testing the update of the hard coded arguments in massive way.
 *
 * @group Message UI
 */
class MessageUiMassiveHardCodedArguments extends MessageTestBase {

  public $user1;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('message', 'message_ui', 'entity_token');

  public static function getInfo() {
    return array(
      'name' => 'Message UI arguments massive update',
      'description' => 'Testing the removing/updating an hard coded arguments.',
      'group' => 'Message UI',
      'dependencies' => array('entity_token'),
    );
  }

  public function setUp() {
    parent::setUp();

    $message_type = MessageType::create('dummy_message', array('message_text' => array(LANGUAGE_NONE => array(array('value' => '@{message:user:name}.')))));
    $message_type->save();

    // Set a queue worker for the update arguments when updating a message type.
    variable_set('update_tokens_update_tokens', TRUE);
    variable_set('update_tokens_how_update', 'update_when_item');
  }

  public function testRemoveAddingArguments() {
    // Create a message.
    $this->user1 = $this->drupalCreateUser();
    $message = message_create('dummy_message');
    $message->uid = $this->user1->uid;
    $message->save();

    $original_arguments = $message->arguments;

    // Update message instance when removing a hard coded argument.
    variable_set('update_tokens_how_to_act', 'update_when_removed');

    $message_type = message_type_load('dummy_message');
    $message_type->message_text[LANGUAGE_NONE][0]['value'] = '[message:user:name].';
    $message_type->save();

    // Fire the queue worker.
    $queue = DrupalQueue::get('message_ui_arguments');
    $item = $queue->claimItem();
    message_ui_arguments_worker($item->data);

    // Verify the arguments has changed.
    $message = message_load($message->mid);
    $this->assertTrue($original_arguments != $message->arguments, 'The message arguments has changed during the queue worker work.');

    // Creating a new message and her hard coded arguments.
    $message = message_create('dummy_message');
    $message->uid = $this->user1->uid;
    $message->save();
    $original_arguments = $message->arguments;

    // Process the message instance when adding hard coded arguments.
    variable_set('update_tokens_how_to_act', 'update_when_added');

    $message_type = message_type_load('dummy_message');
    $message_type->message_text[LANGUAGE_NONE][0]['value'] = '@{message:user:name}.';
    $message_type->save();

    // Fire the queue worker.
    $queue = DrupalQueue::get('message_ui_arguments');
    $item = $queue->claimItem();
    message_ui_arguments_worker($item->data);

    // Verify the arguments has changed.
    $message = message_load($message->mid);
    $this->assertTrue($original_arguments == $message->arguments, 'The message arguments has changed during the queue worker work.');
  }
}
