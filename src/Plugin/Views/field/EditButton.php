<?php

/**
 * @file
 * Definition of Drupal\message_ui\Plugin\views\field\EditButton.
 */

namespace Drupal\message_ui\Plugin\views\field;

use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\message\Entity\Message;
use Drupal\message_ui\MessageAccessControlHandler;

/**
 * Field handler to present an Edit button for a message instance.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("edit_button")
 */
class EditButton extends FieldPluginBase {

  /**
   * Stores the result of node_view_multiple for all rows to reuse it later.
   *
   * @var array
   */
  protected $build;

  /**
   * {@inheritdoc}
   */
  public function query() {}

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $message = Message::load($values->_entity->id());

    $access_handler = new MessageAccessControlHandler('message');
    if ($access_handler->checkAccess($message, 'edit', \Drupal::currentUser())) {
      $url = Url::fromRoute('message_ui.edit_message', $message);
      return \Drupal::l(t('Edit'), $url);
    }
  }

}
