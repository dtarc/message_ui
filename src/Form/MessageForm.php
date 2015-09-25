<?php

/**
 * @file
 * Contains \Drupal\message_ui\MessageForm.
 */

namespace Drupal\message_ui\Form;

use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\message\Entity\Message;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for node type forms.
 *
 * @ingroup message
 */
class MessageForm extends ContentEntityForm {

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
    $form = parent::form($form, $form_state);

    /** @var Message $message */
    $message = $this->entity;

    // @todo follow MessageType form and message_ui_instance_message_manage.
    // The UI for creating/editing the message.
    if (!is_object($message)) {
      $message = Message::create($message);
    }

    // $form_state['#entity'] = $message;

    $view_builder = \Drupal::entityManager()->getViewBuilder('message');
    $message_text = $view_builder->view($message);

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
      '#default_value' => ($message->getAuthorId() ? User::load($message->getAuthorId())->getUsername() : NULL),
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
    $url = is_object($message) && !empty($mid) ? Url::fromRoute('message_ui.show_message', ['message' => $mid]) : Url::fromRoute('message.overview_types');

    $link =  \Drupal::l(t('Cancel'), $url);

    $form['actions'] = array(
      '#type' => 'actions',
      'submit' => array(
        '#type' => 'submit',
        '#value' => $message->isNew() ? t('Update') : t('Create'),
        '#submit' => array('message_ui_instance_message_create_submit'),
      ),
      'cancel' => array(
        '#type' => 'markup',
        '#markup' => $link
      ),
    );

    return $form;
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

    // Submit handler - create/edit new message via the UI.
    /* @var $message Message */
    $message = $form_state['#entity'];

    // @todo: submit handlers are removed, what is needed below? https://www.drupal.org/node/1846648
    field_attach_submit('message', $message, $form, $form_state);

    // Update the tokens.
    $token_actions = empty($form_state['values']['replace_tokens']) ? array() : $form_state['values']['replace_tokens'];

    $args = $message->getArguments();

    if (is_object($message) && !empty($args)) {
      if (!empty($token_actions) && $token_actions != 'no_update') {

        foreach (array_keys($args) as $token) {
          // Loop through the arguments of the message.

          if ($token_actions == 'update') {
            // Get the hard coded value of the message and him in the message.
            $token_name = str_replace(array('@{', '}'), array('[', ']'), $token);
            $token_service = \Drupal::token();
            $value = $token_service->replace($token_name, array('message' => $message));
          }
          else {
            // Hard coded value given from the user.
            $value = $form_state['values'][$token];
          }

          $args[$token] = $value;
        }
      }
    }

    $message->setAuthorId(user_load_by_name($form_state['values']['name']));
    $message->setCreatedTime(strtotime($form_state['values']['date']));
    $message->save();

    $form_state['redirect'] = 'message/' . $message->id();
  }
}
