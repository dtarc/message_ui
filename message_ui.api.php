<?php

/**
 * @file
 * Defining the API part of the Message UI module.
 */

namespace Drupal\message_ui;

use Drupal\message\Entity\Message;
use Drupal\Core\Session\AccountInterface;

/**
 * Implements hook_message_ui_view_alter().
 */
function hook_message_ui_view_alter(array &$build, Message $message) {
  // Check the output of the message as you wish.
}

/**
 * Implements hook_message_message_ui_access_control().
 */
function hook_message_message_ui_access_control(Message $message, $op, AccountInterface $account) {
  return MESSAGE_UI_DENY;
}
