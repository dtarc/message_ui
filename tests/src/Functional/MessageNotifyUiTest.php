<?php

/**
 * @file
 * Definition of Drupal\message_ui\Tests\MessageUiPermissions.
 */

namespace Drupal\Tests\message_ui\Functional;

use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Testing the message notify button.
 *
 * @group Message UI
 */
class MessageNotifyUiTest extends AbstractTestMessageUi {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['message', 'message_notify_ui'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->account = $this->drupalCreateUser(array(
      'send message through the ui',
      'overview messages',
    ));

    // Create Message template foo.
    $this->createMessageTemplate('foo', 'Dummy test', 'Example text.', array('Dummy message'));
  }

  /**
   * Testing the displaying of the preview.
   */
  public function testMessageNotifyUi() {

    // User login.
    $this->drupalLogin($this->account);

    // Create a message.
    $message = $this->container->get('entity_type.manager')->getStorage('message')->create([
      'template' => 'foo',
    ]);
    $message->save();

    // Go to the page of notify page.
    $edit = [
      'use_custom' => TRUE,
      'email' => 'foo@gmail.com',
    ];
    $this->drupalPostForm('message/' . $message->id() . '/notify', $edit, t('Notify'));
    $this->assertSession()->pageTextContains('The email sent successfully.');
  }

}
