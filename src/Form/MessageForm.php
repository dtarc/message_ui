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
    /**
     * The UI for creating/editing the message.

    // @todo : move to MessageForm form class, build form method?
    function message_ui_instance_message_manage($form, &$form_state, Message $message) {
      if (!is_object($message)) {
        $message = Message::create($message);
      }

      $form_state['#entity'] = $message;
      $message_text = $message->view();

      if (\Drupal::config('message_ui.settings')->get('update_tokens.show_preview')) {
        $form['text'] = array(
          '#type' => 'item',
          '#title' => t('Message text'),
          '#markup' => render($message_text),
        );
      }

      $display = EntityFormDisplay::collectRenderDisplay($message, 'default');
      $display->buildForm($message, $form, $form_state);

      $form['additional_settings'] = array(
        '#type' => 'vertical_tabs',
        '#weight' => 99,
      );

      $form['owner'] = array(
        '#type' => 'fieldset',
        '#title' => t('Authoring information'),
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#group' => 'additional_settings',
        '#attributes' => array(
          'class' => array('message-form-owner'),
        ),
        '#attached' => array(
          'library' => array(
            '/message_ui/message-ui',
          ),
        'drupalSettings' => array(
          'message_ui' => array('anonymous' => \Drupal::config('message_ui.settings')->get('anonymous')),
        ),
        ),
        '#weight' => 90,
      );

      $form['owner']['name'] = array(
        '#type' => 'textfield',
        '#title' => t('Authored by'),
        '#maxlength' => 60,
        '#weight' => 99,
        '#autocomplete_path' => 'user/autocomplete',
        '#description' => t('Leave blank for %anonymous.', array('%anonymous' => \Drupal::config('message_ui.settings')->get('anonymous'))),
        '#default_value' => User::load($message->getAuthorId())->getUsername(),
      );

      $form['owner']['date'] = array(
        '#type' => 'textfield',
        '#title' => t('Authored on'),
        '#description' => t('Please insert in the format of @date', array(
          '@date' => date('Y-m-d j:i', $message->timestamp),
        )),
        '#default_value' => date('Y-m-d H:i', $message->timestamp),
        '#maxlength' => 25,
        '#weight' => 100,
      );

      $args = $message->getArguments();

      if (!empty($args) && (\Drupal::currentUser()->hasPermission('update tokens') || \Drupal::currentUser()->hasPermission('bypass message access control'))) {
        $form['tokens'] = array(
          '#type' => 'fieldset',
          '#title' => t('Tokens and arguments'),
          '#collapsible' => TRUE,
          '#collapsed' => TRUE,
          '#group' => 'additional_settings',
          '#weight' => 110,
        );

        // Give the user an option to update the har coded tokens.
        $form['tokens']['replace_tokens'] = array(
          '#type' => 'select',
          '#title' => t('Update tokens value automatically'),
          '#description' => t('By default, the hard coded values will be replaced automatically. If unchecked - you can update their value manually.'),
          '#default_value' => 'no_update',
          '#options' => array(
            'no_update' => t("Don't update"),
            'update' => t('Update automatically'),
            'update_manually' => t('Update manually'),
          ),
        );

        $form['tokens']['values'] = array(
          '#type' => 'container',
          '#states' => array(
            'visible' => array(
              ':input[name="replace_tokens"]' => array('value' => 'update_manually'),
            ),
          ),
        );

        // Build list of fields to update the tokens manually.
        foreach ($message->getArguments() as $name => $value) {
          $form['tokens']['values'][$name] = array(
            '#type' => 'textfield',
            '#title' => t("@name's value", array('@name' => $name)),
            '#default_value' => $value,
          );
        }
      }

      $mid = $message->id();
      $url = is_object($message) && !empty($mid) ? Url::fromRoute('message_ui.show_message', $message) : Url::fromRoute('message.overview_types');

      $form['actions'] = array(
        '#type' => 'actions',
        'submit' => array(
          '#type' => 'submit',
          '#value' => empty($message->is_new) ? t('Update') : t('Create'),
          '#submit' => array('message_ui_instance_message_create_submit'),
        ),
        'cancel' => array(
          '#type' => 'markup',
          '#markup' => \Drupal::l(t('Cancel'), $url)
        ),
      );

      return $form;
    }*/
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
