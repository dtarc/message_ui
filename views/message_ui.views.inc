<?php
/**
 * @file
 * integration with the views module.
 */

/**
 * Implements hook_views_data().
 */
function message_ui_views_data() {
  $data['message']['delete'] = array(
    'title' => t('Delete message'),
    'help' => t('Link to delete the message instance.'),
    'field' => array(
      'handler' => 'message_ui_delete_button',
    ),
  );
  $data['message']['edit'] = array(
    'title' => t('Edit message'),
    'help' => t('Link to edit the message instance.'),
    'field' => array(
      'handler' => 'message_ui_edit_button',
    ),
  );
  $data['message']['view'] = array(
    'title' => t('View message'),
    'help' => t('Link to view the message instance.'),
    'field' => array(
      'handler' => 'message_ui_view_button',
    ),
  );
  return $data;
}

/**
 * Implements hook_views_default_views_alter().
 */
function message_ui_views_default_views_alter(&$views) {
  if (!in_array('message', array_keys($views))) {
    return;
  }

  $handler =& $views['message']->display['default']->handler;

  /* Field: Message: Edit message */
  $field['edit']['id'] = 'edit';
  $field['edit']['table'] = 'message';
  $field['edit']['field'] = 'edit';
  $field['edit']['label'] = '';
  $field['edit']['exclude'] = TRUE;
  $field['edit']['element_label_colon'] = FALSE;

  /* Field: Message: Delete message */
  $field['delete']['id'] = 'delete';
  $field['delete']['table'] = 'message';
  $field['delete']['field'] = 'delete';
  $field['delete']['label'] = '';
  $field['delete']['exclude'] = TRUE;
  $field['delete']['element_label_colon'] = FALSE;

  /* Field: Message: View message */
  $field['view']['id'] = 'view';
  $field['view']['table'] = 'message';
  $field['view']['field'] = 'view';
  $field['view']['label'] = '';
  $field['view']['exclude'] = TRUE;
  $field['view']['element_label_colon'] = FALSE;

  $handler->display->display_options['fields'] = $field + $handler->display->display_options['fields'];

  /* Field: Global: Custom text */
  $handler->display->display_options['fields']['nothing']['id'] = 'nothing';
  $handler->display->display_options['fields']['nothing']['table'] = 'views';
  $handler->display->display_options['fields']['nothing']['field'] = 'nothing';
  $handler->display->display_options['fields']['nothing']['label'] = 'Operation';

  $text = '[view] [edit] [delete] ';
  // In case this value was set previously by another "views_default_views_alter".
  if (isset($handler->display->display_options['fields']['nothing']['alter']['text'])) {
    $handler->display->display_options['fields']['nothing']['alter']['text'] .= $text;
  }
  else {
    $handler->display->display_options['fields']['nothing']['alter']['text'] = $text;
  }
}
