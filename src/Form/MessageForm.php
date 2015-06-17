<?php

/**
 * @file
 * Contains \Drupal\message_ui\MessageForm.
 */

namespace Drupal\message_ui\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for node type forms.
 */
class MessageForm extends EntityForm {

  /**
   * The entity being used by this form.
   *
   * @var \Drupal\message\Entity\Message
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    // @todo follow MessageType form and message_ui_instance_message_manage.
  }
}
