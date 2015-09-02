<?php

/**
 * @file
 * Definition of Drupal\message_ui\Plugin\views\field\ViewButton.
 */

namespace Drupal\message_ui\Plugin\views\field;

use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\message\Entity\Message;
use Drupal\message_ui\MessageAccessControlHandler;

/**
 * Field handler to present an View button for a message instance.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("view_button")
 */
class ViewButton extends FieldPluginBase {

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
    if ($access_handler->checkAccess($message, 'view', \Drupal::currentUser())) {
      $url = Url::fromRoute('message_ui.show_message', $message);
      return \Drupal::l(t('View'), $url);
    }
  }

}
