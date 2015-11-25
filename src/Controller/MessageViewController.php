<?php

/**
 * @file
 * Contains \Drupal\message_ui\Controller\MessageViewController.
 */

namespace Drupal\message_ui\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Controller\EntityViewController;

/**
 * Defines a controller to render a single message.
 */
class MessageViewController extends EntityViewController {

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $message, $view_mode = 'full', $langcode = NULL) {
    $build = parent::view($message, $view_mode);
    return $build;
  }
}
