<?php
/**
 * @file
 * integration with the views module.
 */

/**
 * Implements hook_views_data().
 */
function message_notify_ui_views_data() {
  $data['message']['notify'] = array(
    'title' => t('Notify message'),
    'help' => t('Send the message using a notifier.'),
    'field' => array(
      'handler' => 'message_notify_ui_notify_button',
    ),
  );
  return $data;
}

/**
 * Implements hook_views_default_views_alter().
 */
function message_notify_ui_views_default_views_alter(&$views) {

  if (!in_array('message', array_keys($views))) {
    return;
  }

  $handler =& $views['message']->display['default']->handler;

  /* Field: Bulk operations: Message */
  $field['views_bulk_operations']['id'] = 'views_bulk_operations';
  $field['views_bulk_operations']['table'] = 'message';
  $field['views_bulk_operations']['field'] = 'views_bulk_operations';
  $field['views_bulk_operations']['vbo_settings']['display_type'] = '0';
  $field['views_bulk_operations']['vbo_settings']['enable_select_all_pages'] = 1;
  $field['views_bulk_operations']['vbo_settings']['force_single'] = 0;
  $field['views_bulk_operations']['vbo_settings']['entity_load_capacity'] = '10';
  $field['views_bulk_operations']['vbo_operations'] = array(
    'action::message_notify_send_email_vbo' => array(
      'selected' => 1,
      'postpone_processing' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
  );

  /* Field: Message: View message */
  $field['view']['id'] = 'view';
  $field['view']['table'] = 'message';
  $field['view']['field'] = 'view';
  $field['view']['label'] = '';
  $field['view']['exclude'] = TRUE;
  $field['view']['element_label_colon'] = FALSE;

  /* Field: Message: Notify message */
  $field['notify']['id'] = 'notify';
  $field['notify']['table'] = 'message';
  $field['notify']['field'] = 'notify';
  $field['notify']['label'] = '';
  $field['notify']['exclude'] = TRUE;
  $field['notify']['element_label_colon'] = FALSE;

  $handler->display->display_options['fields'] = $field + $handler->display->display_options['fields'];

  /* Field: Global: Custom text */
  $text = '[notify] ';
  // In case this value was set previously by another "views_default_views_alter".
  if (isset($handler->display->display_options['fields']['nothing']['alter']['text'])) {
    $handler->display->display_options['fields']['nothing']['alter']['text'] .= $text;
  }
  else {
    $handler->display->display_options['fields']['nothing']['alter']['text'] = $text;
  }
}
