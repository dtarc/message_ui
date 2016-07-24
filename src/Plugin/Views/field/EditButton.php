<?php

/**
 * @file
 * Definition of Drupal\message_ui\Plugin\views\field\EditButton.
 */

namespace Drupal\message_ui\Plugin\views\field;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\message_ui\MessageAccessControlHandler;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\message\Entity\Message;

/**
 * Field handler to present an Edit button for a message instance.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("edit_button")
 */
class EditButton extends FieldPluginBase {

  /**
   * Stores the result of message_view_multiple for all rows to reuse it later.
   *
   * @var array
   */
  protected $build;

  /**
   * {@inheritdoc}
   */
  public function query() {
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $message = Message::load($values->_entity->id());

    $access_handler = new MessageAccessControlHandler($message->getEntityType());
    if ($access_handler->access($message, 'edit', \Drupal::currentUser())) {
      $url = Url::fromRoute('entity.message.edit_form', $message);
      return Link::fromTextAndUrl(t('Edit'), $url);
    }
  }

}
