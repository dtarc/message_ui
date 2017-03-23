<?php

/**
 * @file
 * Definition of Drupal\message_ui\Tests\MessageUiHardCodedArguments.
 */

namespace Drupal\Tests\message_ui\Functional;

use Drupal\Tests\message\Functional\MessageTestBase;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;
use Drupal\message\Entity\Message;
use Drupal\user\UserInterface;

/**
 * Testing the editing of the hard coded arguments.
 *
 * @group Message UI
 */
class MessageUiHardCodedArgumentsTest extends AbstractTestMessageUi {

  /**
   * The first user object.
   *
   * @var UserInterface
   */
  public $user1;

  /**
   * The second user object.
   *
   * @var UserInterface
   */
  public $user2;

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
    $this->createMessageTemplate(
      'dummy_message',
      'Dummy test',
      'This is a dummy message with a dummy message',
      ['@{message:author:name}']);

    // Get the message template and create an instance.
    $message_template = $this->loadMessageTemplate('dummy_message');
    /* @var $message Message */
    $message = Message::create(array('template' => $message_template->id()));
    $message->setOwner($this->user1);
    $message->save();

    // Verifying the message hard coded value is set to the user 1.
    $this->drupalGet('message/' . $message->id());

    // The message token is set to the user 1.
    $this->assertSession()->pageTextContains($this->user1->getAccountName());

    $message->setOwner($this->user2);
    $message->save();
    $this->drupalGet('message/' . $message->id());

    // The message token is set to the user 1 after editing the message.
    $this->assertSession()->pageTextNotContains($this->user2->getAccountName());

    // Update the message arguments automatically.
    $edit = array(
      'name' => $this->user2->getAccountName() . ' (' . $this->user2->id() . ')',
      'replace_tokens' => 'update',
    );

    $this->drupalPostForm('message/' . $message->id() . '/edit', $edit, t('Update'));

    // The message token as updated automatically.
    $this->assertSession()->pageTextContains($this->user2->getAccountName());

    // Update the message arguments manually.
    $edit = array(
      'name' => $this->user2->label(),
      'replace_tokens' => 'update_manually',
      'edit-messageauthorname' => 'Dummy name',
    );

    $this->drupalPostForm('message/' . $message->id() . '/edit', $edit, t('Update'));

    // The hard coded token was updated with a custom value.
    $this->assertSession()->pageTextContains('Dummy name');

  }

}
