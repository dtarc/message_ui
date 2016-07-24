<?php

/**
 * @file
 * Definition of Drupal\message_ui\Plugin\views\field\NotifyButton.
 */

namespace Drupal\message_ui\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\message\Entity\Message;

/**
 * Field handler to present a Notify button for a message instance.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("notify_button")
 */
class NotifyButton extends FieldPluginBase {

  /**
   * Stores the result of message_view_multiple for all rows to reuse it later.
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

    if (\Drupal::currentUser()->hasPermission('send message notify')) {
      return l(t('Notify'), 'message/' . $message->id() . '/notify');
    }
    return NULL;
  }
}
