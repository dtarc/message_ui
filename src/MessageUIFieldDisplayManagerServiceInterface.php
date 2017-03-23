<?php

namespace Drupal\message_ui;

/**
 * Interface MessageUIFieldDisplayManagerServiceInterface.
 *
 * @package Drupal\message_ui
 */
interface MessageUIFieldDisplayManagerServiceInterface {

  /**
   * Setting the fields to display.
   *
   * @param $template
   *   The message template.
   */
  public function SetFieldsDisplay($template);

}
