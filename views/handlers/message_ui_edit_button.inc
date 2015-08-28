<?php

use Drupal\Core\Url;

/**
 * Edit button for a message instance.
 */
class message_ui_edit_button extends views_handler_field_entity {

  function render($values) {
    $message = message_load($values->mid);

    if (message_ui_access_control('edit', $message)) {
      $url = Url::fromRoute('message_ui.edit_message', $message);
      return \Drupal::l(t('Edit'), $url);
    }
  }
}
