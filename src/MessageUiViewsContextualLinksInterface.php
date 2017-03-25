<?php

namespace Drupal\message_ui;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\message\Entity\Message;

/**
 * Defines an interface for Message UI views contextual links plugins.
 */
interface MessageUiViewsContextualLinksInterface extends PluginInspectionInterface {

  /**
   * Set the message object.
   *
   * @param Message $message
   *   The message object.
   *
   * @return mixed
   */
  public function setMessage(Message $message);

  /**
   * @return \Drupal\message\Entity\Message
   */
  public function getMessage();

  /**
   * Return the an array with the router ID and message info.
   *
   * @return array
   */
  public function getRouterInfo();

  /**
   * Checking if the user have access to do the action.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   Access result interface object.
   */
  public function access();

}
