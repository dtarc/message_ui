<?php

/**
 * @file
 * Definition of Drupal\message_ui\Tests\MessageUiHardCodedArguments.
 */

namespace Drupal\message_ui\Tests;

use Drupal\user\RoleInterface;
use Drupal\message\Tests\MessageTestBase;
use Drupal\message\Entity\Message;

/**
 * Testing the editing of the hard coded arguments.
 *
 * @group Message UI
 */
class MessageUiHardCodedArguments extends MessageTestBase {

  /**
   * The first user object.
   * @var
   */
  public $user1;

  /**
   * The second user object.
   * @var
   */
  public $user2;

  /**
   * Modules to enable.
   *
   * @var array
   */
  // @todo: is entity_token required in D8?
  public static $modules = array('message', 'message_ui'/*, 'entity_token'*/);

  public static function getInfo() {
    return array(
      'name' => 'Message UI arguments single update',
      'description' => 'Testing the editing of the hard coded arguments.',
      'group' => 'Message UI',
      // 'dependencies' => array('entity_token'), // @todo: is this required?
    );
  }

  public function setUp() {
    parent::setUp();

    // Create Message Type of 'Dummy Test'.
    $this->createMessageType('dummy_message', 'Dummy test', 'This is a dummy message with a dummy message', array('Dummy message'));

    $this->user1 = $this->drupalCreateUser();
    $this->user2 = $this->drupalCreateUser();

    // Load 'authenticated' user role.
    $role = entity_load('user_role', RoleInterface::AUTHENTICATED_ID);

    user_role_grant_permissions($role->id(), array('bypass message access control'));
  }

  /**
   * Verify that a user can update the arguments for each instance.
   */
  public function testHardCoded() {
    $this->drupalLogin($this->user1);

    // Get the message type and create an instance.
    $message_type = $this->loadMessageType('dummy_message');
    $message = Message::create(array('type' => $message_type->id()));
    $message->setAuthorId($this->user1->uid);
    $message->save();

    // Verifying the message hard coded value is set to the user 1.
    $this->drupalGet('message/' . $message->id());

    $this->assertText($this->user1->name, 'The message token is set to the user 1.');

    $message->setAuthorId($this->user2->uid);
    $message->save();
    $this->drupalGet('message/' . $message->id());

    $this->assertNoText($this->user2->name, 'The message token is set to the user 1 after editing the message.');

    // Update the message arguments automatically.
    $edit = array(
      'name' => $this->user2->name,
      'replace_tokens' => 'update',
    );
    $this->drupalPost('message/' . $message->id() . '/edit', $edit, t('Update'));
    $this->assertText($this->user2->name, 'The message token as updated automatically.');

    // Update the message arguments manually.
    $edit = array(
      'name' => $this->user2->name,
      'replace_tokens' => 'update_manually',
      '@{message:user:name}' => 'Dummy name',
    );
    $this->drupalPost('message/' . $message->id() . '/edit', $edit, t('Update'));
    $this->assertText('Dummy name', 'The hard coded token was updated with a custom value.');
  }
}
