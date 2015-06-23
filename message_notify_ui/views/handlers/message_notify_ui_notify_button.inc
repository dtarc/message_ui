<?php

/**
 * Notify button for a message instance.
 */
class message_notify_ui_notify_button extends views_handler_field_entity {

  function render($values) {
    $message = message_load($values->mid);

    if (user_access('send message notify')) {
      return l(t('Notify'), 'message/' . $message->mid . '/notify');
    }
  }
}
