<?php

/**
 * @file
 * Definition of Drupal\message_ui\Tests\MessageUiPermissions.
 */

namespace Drupal\message_ui\Tests;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;
use Drupal\message\Tests\MessageTestBase;
use Drupal\message\Entity\Message;

/**
 * Testing the message access use case.
 *
 * @group Message UI
 */
class MessageUiPermissions extends MessageTestBase {

  /**
   * The message access control handler.
   *
   * @var \Drupal\Core\Entity\EntityAccessControlHandlerInterface
   */
  protected $accessHandler;

  /**
   * The user account object.
   * @var
   */
  protected $account;

  /**
   * The user role.
   * @var
   */
  protected $rid;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['message', 'message_ui'];

  public static function getInfo() {
    return array(
      'name' => 'Message UI permissions',
      'description' => 'Testing the use case of message_access function.',
      'group' => 'Message UI',
    );
  }

  /**
   * {@inheritdoc}
   */
  function setUp() {
    parent::setUp();

    $this->accessHandler = \Drupal::entityManager()
      ->getAccessControlHandler('message');

    $this->account = $this->drupalCreateUser();

    // Load 'authenticated' user role.
    $this->rid = Role::load(RoleInterface::AUTHENTICATED_ID)->id();

    // Create Message template foo.
    $this->createMessageTemplate('foo', 'Dummy test', 'Example text.', array('Dummy message'));
  }

  /**
   * Test message_access use case.
   */
  function testMessageUiPermissions() {
    $this->drupalLogin($this->account); // User login.
    $create_url = '/message/add/foo'; // Set our create url.

    // Verify the user can't create the message.
    $this->drupalGet($create_url);
    $this->assertResponse(403, t("The user can't create a message."));

    // Grant and check create permissions for a message.
    $this->grantMessageUiPermission('create');
    $this->drupalGet($create_url);

    // If we get a valid response, create a message.
    if ($this->assertResponse(200, t("The user can create a message."))) {
      // Create a message at current page / url.
      $this->drupalPostForm(NULL, array(), t('Save'));
    }

    $msg_url = '/message/1'; // Create the message url.

    // Verify the user now can see the text.
    $this->grantMessageUiPermission('view');
    $this->drupalGet($msg_url);
    $this->assertResponse(200, "The user can view a message.");

    // Verify can't edit the message.
    $this->drupalGet($msg_url . '/edit');
    $this->assertResponse(403, "The user can't edit a message.");

    // Grant permission to the user.
    $this->grantMessageUiPermission('edit');
    $this->drupalGet($msg_url . '/edit');
    $this->assertResponse(200, "The user can't edit a message.");

    // Verify the user can't delete the message.
    $this->drupalGet($msg_url . '/delete');
    $this->assertResponse(403, "The user can't delete the message");

    // Grant the permission to the user.
    $this->grantMessageUiPermission('delete');
    $this->drupalPostForm($msg_url . '/delete', array(), t('Delete'));

    // User did not have permission to the overview page - verify access
    // denied.
    $this->assertResponse(403, t("The user can't access the over view page."));

    user_role_grant_permissions($this->rid, array('administer message templates'));
    $this->drupalGet('/admin/content/messages');
    $this->assertResponse(200, "The user can access the over view page.");

    // Create a new user with the bypass access permission and verify the bypass.
    $this->drupalLogout();
    $user = $this->drupalCreateUser(array('bypass message access control'));

    // Verify the user can by pass the message access control.
    $this->drupalLogin($user);
    $this->drupalGet($create_url);
    $this->assertResponse(200, 'The user can bypass the message access control.');
  }

  /**
   * Grant to the user a specific permission.
   *
   * @param $operation
   *  The template of operation - create, update, delete or view.
   */
  private function grantMessageUiPermission($operation) {
    user_role_grant_permissions($this->rid, array($operation . ' foo message'));
  }

  /**
   * Checking the alteration flow for other modules.
   */
  public function testMessageUiAccessHook() {
    // Install the message ui test dummy module.
    \Drupal::service('module_installer')->install(['message_ui_test']);

    $this->drupalLogin($this->account);

    // Setting up the operation and the expected value from the access callback.
    $permissions = array(
      'create' => TRUE,
      'view' => TRUE,
      'delete' => FALSE,
      'update' => FALSE,
    );

    // Get the message template and create an instance.
    $message_template = $this->loadMessageTemplate('foo');
    /* @var $message Message */
    $message = Message::create(array('template' => $message_template->id()));
    $message->setOwner($this->account);
    $message->save();

    foreach ($permissions as $op => $value) {
      // When the hook access of the dummy module will get in action it will
      // check which value need to return. If the access control function will
      // return the expected value then we know the hook got in action.
      $message->{$op} = $value;
      $params = array(
        '@operation' => $op,
        '@value' => $value,
      );

      $this->assertEqual($value, $this->accessHandler->access($message, $op, $this->account), new FormattableMarkup('The hook return @value for @operation', $params));
    }
  }
}
