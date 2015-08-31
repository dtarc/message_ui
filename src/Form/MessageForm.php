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

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // @todo Custom validation.
    // D7: field_attach_form_validate('message', $form_state['#entity'], $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // E.g. drupal_set_message($this->t('Your phone number is @number', array('@number' => $form_state->getValue('phone_number'))));
    /**
     * D7:
     *
     *
    * Submit handler - create/edit new message via the UI.
function message_ui_instance_message_create_submit($form, &$form_state) {
  $message = $form_state['#entity'];

  field_attach_submit('message', $message, $form, $form_state);

  // Update the tokens.
  $token_actions = empty($form_state['values']['replace_tokens']) ? array() : $form_state['values']['replace_tokens'];

  if (is_object($message) && !empty($message->arguments)) {
    if (!empty($token_actions) && $token_actions != 'no_update') {

      foreach (array_keys($message->arguments) as $token) {
        // Loop through the arguments of the message.

        if ($token_actions == 'update') {
          // Get the hard coded value of the message and him in the message.
          $token_name = str_replace(array('@{', '}'), array('[', ']'), $token);
          $token_service = Drupal::token();
          $value = $token_service->replace($token_name, array('message' => $message));
        }
        else {
          // Hard coded value given from the user.
          $value = $form_state['values'][$token];
        }

        $message->arguments[$token] = $value;
      }
    }
  }

  $wrapper = entity_metadata_wrapper('message', $message);
  $wrapper->user->set(user_load_by_name($form_state['values']['name']));
  $wrapper->timestamp->set(strtotime($form_state['values']['date']));
  $wrapper->save();

  $form_state['redirect'] = 'message/' . $wrapper->getIdentifier();
}
     */
  }
}
