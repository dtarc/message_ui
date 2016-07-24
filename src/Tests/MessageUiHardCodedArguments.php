<?php

/**
 * @file
 * Definition of Drupal\message_ui\Tests\MessageUiHardCodedArguments.
 */

namespace Drupal\message_ui\Tests;

use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\Role;
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
   * @var AccountInterface
   */
  public $user1;

  /**
   * The second user object.
   * @var AccountInterface
   */
  public $user2;

  /**
   * Modules to enable.
   *
   * @todo: is entity_token required in D8?
   *
   * @var array
   */
  public static $modules = ['message', 'message_ui'];

  public static function getInfo() {
    return array(
      'name' => 'Message UI arguments single update',
      'description' => 'Testing the editing of the hard coded arguments.',
      'group' => 'Message UI',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->user1 = $this->drupalCreateUser();
    $this->user2 = $this->drupalCreateUser();
  }

  /**
   * Verify that a user can update the arguments for each instance.
   */
  public function testHardCoded() {
    // Load 'authenticated' user role.
    $role = Role::load(RoleInterface::AUTHENTICATED_ID);
    /* @var $role Role */
    user_role_grant_permissions($role->id(), array('bypass message access control'));

    $this->drupalLogin($this->user1);

    // Create Message Template of 'Dummy Test'.
    $this->createMessageTemplate('dummy_message', 'Dummy test', 'This is a dummy message with a dummy message', array('Dummy message'));

    // Get the message template and create an instance.
    $message_template = $this->loadMessageTemplate('dummy_message');
    /* @var $message Message */
    $message = Message::create(array('template' => $message_template->id()));
    $message->setOwner($this->user1);
    $message->save();

    // Verifying the message hard coded value is set to the user 1.
    $this->drupalGet('message/' . $message->id());

    $this->assertText($this->user1->getUsername(), 'The message token is set to the user 1.');

    $message->setOwner($this->user2);
    $message->save();
    $this->drupalGet('message/' . $message->id());

    $this->assertNoText($this->user2->getUsername(), 'The message token is set to the user 1 after editing the message.');

    // Update the message arguments automatically.
    $edit = array(
      'name' => $this->user2->getUsername(),
      'replace_tokens' => 'update',
    );

    $this->drupalPostForm('message/' . $message->id() . '/edit', $edit, t('Update'));
    $this->assertText($this->user2->name, 'The message token as updated automatically.');

    // Update the message arguments manually.
    $edit = array(
      'name' => $this->user2->getUsername(),
      'replace_tokens' => 'update_manually',
      '@{message:user:name}' => 'Dummy name',
    );

    $this->drupalPostForm('message/' . $message->id() . '/edit', $edit, t('Update'));
    $this->assertText('Dummy name', 'The hard coded token was updated with a custom value.');

  }
}
