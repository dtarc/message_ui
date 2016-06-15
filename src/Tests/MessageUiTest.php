<?php

namespace Drupal\message_ui\Tests;

use Drupal\message_ui\Entity\Message;
use Drupal\examples\Tests\ExamplesTestBase;

/**
 * Tests the basic functions of the Message UI module.
 *
 * @package Drupal\message_ui\Tests
 *
 * @ingroup message_ui
 *
 * @group message_ui
 * @group message
 */
class ContentEntityExampleTest extends ExamplesTestBase {

  public static $modules = array('message_ui', 'block', 'field_ui');

  /**
   * Basic tests for Content Entity Example.
   */
  public function testContentEntityExample() {
    $web_user = $this->drupalCreateUser(array(
      'add message entity',
      'edit message entity',
      'view message entity',
      'delete message entity',
      'administer message entity',
      'administer message display',
      'administer message fields',
      'administer message form display',
    ));

    // Anonymous User should not see the link to the listing.
    $this->assertNoText(t('Message UI: Messages Listing'));

    $this->drupalLogin($web_user);

    // Web_user user has the right to view listing.
    $this->assertLink(t('Message UI: Messages Listing'));

    $this->clickLink(t('Message UI: Messages Listing'));

    // WebUser can add entity content.
    $this->assertLink(t('Add Message'));

    $this->clickLink(t('Add Message'));

    $this->assertFieldByName('name[0][value]', '', 'Name Field, empty');
    $this->assertFieldByName('name[0][value]', '', 'First Name Field, empty');
    $this->assertFieldByName('name[0][value]', '', 'Gender Field, empty');

    $user_ref = $web_user->name->value . ' (' . $web_user->id() . ')';
    $this->assertFieldByName('user_id[0][target_id]', $user_ref, 'User ID reference field points to web_user');

    // Post content, save an instance. Go back to list after saving.
    $edit = array(
      'name[0][value]' => 'test name',
      'first_name[0][value]' => 'test first name',
      'gender' => 'male',
    );
    $this->drupalPostForm(NULL, $edit, t('Save'));

    // Entity listed.
    $this->assertLink(t('Edit'));
    $this->assertLink(t('Delete'));

    $this->clickLink('test name');

    // Entity shown.
    $this->assertText(t('test name'));
    $this->assertText(t('test first name'));
    $this->assertText(t('male'));
    $this->assertLink(t('Add Message'));
    $this->assertLink(t('Edit'));
    $this->assertLink(t('Delete'));

    // Delete the entity.
    $this->clickLink('Delete');

    // Confirm deletion.
    $this->assertLink(t('Cancel'));
    $this->drupalPostForm(NULL, array(), 'Delete');

    // Back to list, must be empty.
    $this->assertNoText('test name');

    // Settings page.
    $this->drupalGet('admin/structure/message_settings');
    $this->assertText(t('Message Settings'));

    // Make sure the field manipulation links are available.
    $this->assertLink(t('Settings'));
    $this->assertLink(t('Manage fields'));
    $this->assertLink(t('Manage form display'));
    $this->assertLink(t('Manage display'));
  }

  /**
   * Test all paths exposed by the module, by permission.
   */
  public function testPaths() {
    // Generate a message so that we can test the paths against it.
    $message = Message::create(
      array(
        'name' => 'somename',
        'first_name' => 'Joe',
        'gender' => 'female',
      )
    );
    $message->save();

    // Gather the test data.
    $data = $this->providerTestPaths($message->id());

    // Run the tests.
    foreach ($data as $datum) {
      // drupalCreateUser() doesn't know what to do with an empty permission
      // array, so we help it out.
      if ($datum[2]) {
        $user = $this->drupalCreateUser(array($datum[2]));
        $this->drupalLogin($user);
      }
      else {
        $user = $this->drupalCreateUser();
        $this->drupalLogin($user);
      }
      $this->drupalGet($datum[1]);
      $this->assertResponse($datum[0]);
    }
  }

  /**
   * Data provider for testPaths.
   *
   * @param int $message_id
   *   The id of an existing Message entity.
   *
   * @return array
   *   Nested array of testing data. Arranged like this:
   *   - Expected response code.
   *   - Path to request.
   *   - Permission for the user.
   */
  protected function providerTestPaths($message_id) {
    return array(
      array(
        200,
        '/message/' . $message_id,
        'view message entity',
      ),
      array(
        403,
        '/message/' . $message_id,
        '',
      ),
      array(
        200,
        '/message/list',
        'view message entity',
      ),
      array(
        403,
        '/message/list',
        '',
      ),
      array(
        200,
        '/message.add_by_type',
        'add message entity',
      ),
      array(
        403,
        '/message.add_by_type',
        '',
      ),
      array(
        200,
        '/message/' . $message_id . '/edit',
        'edit message entity',
      ),
      array(
        403,
        '/message/' . $message_id . '/edit',
        '',
      ),
      array(
        200,
        '/message/' . $message_id . '/delete',
        'delete message entity',
      ),
      array(
        403,
        '/message/' . $message_id . '/delete',
        '',
      ),
      array(
        200,
        'admin/structure/message_settings',
        'administer message entity',
      ),
      array(
        403,
        'admin/structure/message_settings',
        '',
      ),
    );
  }

  /**
   * Test add new fields to the message entity.
   */
  public function testAddFields() {
    $web_user = $this->drupalCreateUser(array(
      'administer message entity',
      'administer message display',
      'administer message fields',
      'administer message form display',
    ));

    $this->drupalLogin($web_user);
    $entity_name = 'message';
    $add_field_url = 'admin/structure/' . $entity_name . '_settings/fields/add-field';
    $this->drupalGet($add_field_url);
    $field_name = 'test_name';
    $edit = array(
      'new_storage_type' => 'list_string',
      'label' => 'test name',
      'field_name' => $field_name,
    );

    $this->drupalPostForm(NULL, $edit, t('Save and continue'));
    $expected_path = $this->buildUrl('admin/structure/' . $entity_name . '_settings/fields/' . $entity_name . '.' . $entity_name . '.field_' . $field_name . '/storage');

    // Fetch url without query parameters.
    $current_path = strtok($this->getUrl(), '?');
    $this->assertEqual($expected_path, $current_path, 'It should redirect to field storage settings page.');

  }

}
