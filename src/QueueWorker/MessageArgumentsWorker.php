<?php
/**
 * @file
 * Contains \Drupal\message_ui\Plugin\QueueWorker\MessageArgumentsWorker.
 */

namespace Drupal\message_ui\Plugin\QueueWorker;

use Drupal\message\Entity\Message;
use Drupal\message\Entity\MessageTemplate;
use Drupal\message\MessageInterface;
use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Queue worker plugin instance to update the message arguments.
 *
 * @QueueWorker(
 *   id = "message_ui_arguments",
 *   title = @Translation("Message UI arguments"),
 *   cron = {"time" = 60}
 * )
 */
class MessageArgumentsWorker extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {

    // Update the message arguments via a queue worker.
    if ($data instanceof MessageInterface) {

      // Load all of the messages.
      $query = \Drupal::entityQuery('message');
      $result = $query
        ->condition('type', $data['type'])
        ->sort('mid', 'DESC')
        ->condition('mid', $data['last_mid'], '>=')
        ->range(0, $data['item_to_process'])
        ->execute();

      if (empty($result['message'])) {
        return FALSE;
      }

      // Update the messages.
      $messages = Message::loadMultiple(array_keys($result['message']));
      foreach ($messages as $message) {
        /* @var Message $message */
        $this->messageArgumentsUpdate($message, $data['new_arguments']);
        $data['last_mid'] = $message->id();
      }

      // Create the next queue worker.
      $queue = \Drupal::queue('message_ui_arguments');
      return $queue->createItem($data);
    }

    return FALSE;
  }

  /**
   * Get hard coded arguments.
   *
   * @param string $template
   *   The message template.
   * @param bool $count
   *   Determine weather to the count the arguments or return a list of them.
   *
   * @return int
   *   The number of the arguments.
   */
  public static function getArguments($template, $count = FALSE) {
    /* @var $message_template MessageTemplate */
    $message_template = MessageTemplate::load($template);

    if (!$output = $message_template->getText()) {
      return FALSE;
    }

    preg_match_all('/[@|%|\!]\{([a-z0-9:_\-]+?)\}/i', $output, $matches);

    return $count ? count($matches[0]) : $matches[0];
  }

  /**
   * A helper function for generate a new array of the message's arguments.
   *
   * @param Message $message
   *   The message which her arguments need an update.
   * @param array $arguments
   *   The new arguments need to be calculated.
   */
  public static function messageArgumentsUpdate(Message $message, array $arguments) {

    $message_arguments = array();

    foreach ($arguments as $token) {
      // Get the hard coded value of the message and him in the message.
      $token_name = str_replace(array('@{', '}'), array('[', ']'), $token);
      $token_service = \Drupal::token();
      $value = $token_service->replace($token_name, array('message' => $message));

      $message_arguments[$token] = $value;
    }

    $message->setArguments($message_arguments);
    $message->save();
  }

  /**
   * The message batch or queue item callback function.
   *
   * @param array $mids
   *   The messages ID for process.
   * @param array $arguments
   *   The new state arguments.
   */
  public static function argumentsUpdate(array $mids, array $arguments) {
    // Load the messages and update them.
    $messages = Message::loadMultiple($mids);

    foreach ($messages as $message) {
      /* @var Message $message */
      MessageArgumentsWorker::messageArgumentsUpdate($message, $arguments);
    }
  }

}
