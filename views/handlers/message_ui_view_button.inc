<?php

use Drupal\Core\Url;

/**
 * Delete button for a message instance.
 */
class message_ui_view_button extends views_handler_field_entity {

  function render($values) {
    $message = message_load($values->mid);

    if (message_ui_access_control('view', $message)) {
      $url = Url::fromRoute('message_ui.show_message', $message);
      return \Drupal::l(t('View'), $url);
    }
  }
}
