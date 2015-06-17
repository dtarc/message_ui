<?php

/**
 * @file
 * Contains \Drupal\message_ui\Form\MessageDeleteConfirm.
 */

namespace Drupal\message_ui\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for message deletion.
 */
class MessageDeleteConfirm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete the message %id?', array('%id' => $this->entity->id()));
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    $t_args = array('%id' => $this->entity->id());
    drupal_set_message(t('The message %id has been deleted.', $t_args));
    $this->logger('content')->notice('Deleted message %id', $t_args);
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

  /**
   * Returns the route to go to if the user cancels the action.
   *
   * @return \Drupal\Core\Url
   *   A URL object.
   */
  public function getCancelUrl() {
    return new Url('message_ui.show_message', $this->entity);
  }
}
