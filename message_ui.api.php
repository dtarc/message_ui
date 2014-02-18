<?php

/**
 * @file
 * Defining the API part of the message UI module.
 */

/**
 * Implements hook_message_ui_view_alter().
 *
 * @param $build
 *  A render-able array which returned by the page callback function.
 * @param $message
 *  The message object.
 */
function hook_message_ui_view_alter(&$build, $message) {
  // Check the output of the message as you wish.
}

/**
 * Implements hook_message_message_ui_access_control().
 *
 * @param Message|String $message
 *  The message. When creating the type will be passed.
 * @param $op
 *  The operation: create, update, read or delete.
 * @param $account
 *  The user account object.
 *
 * @return bool
 *  True or false.
 */
function hook_message_message_ui_access_control($message, $op, $account) {
  return MESSAGE_UI_DENY;
}
