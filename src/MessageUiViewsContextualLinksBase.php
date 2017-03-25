<?php

namespace Drupal\message_ui;

use Drupal\Component\Plugin\PluginBase;
use Drupal\message\Entity\Message;

/**
 * Base class for Message UI views contextual links plugins.
 */
abstract class MessageUiViewsContextualLinksBase extends PluginBase implements MessageUiViewsContextualLinksInterface {

  /**
   * @var \Drupal\message\Entity\Message.
   *
   * The message object.
   */
  protected $message;

  /**
   * {@inheritdoc}
   */
  public function setMessage(Message $message) {
    $this->message = $message;
  }

  /**
   * {@inheritdoc}
   */
  public function getMessage() {
    return $this->message;
  }

}
