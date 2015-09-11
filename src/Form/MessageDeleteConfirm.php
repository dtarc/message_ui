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
    // Deleting the sub theme submit handler.
    if ($form_state['clicked_button']['#type']) {
      $form_state['#entity']->delete();

      $form_state['redirect'] = 'admin/content/message';
      drupal_set_message(t('The message instance @type deleted successfully', array(
        '@type' => $form_state['#entity']->type,
      )));
    }
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
