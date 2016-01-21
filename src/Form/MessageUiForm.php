<?php

/**
 * @file
 * Contains \Drupal\message_ui\MessageUiForm.
 */

namespace Drupal\message_ui\Form;

use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\message\Entity\Message;
use Drupal\Core\Entity\EntityManagerInterface;

/**
 * Form controller for message type forms.
 *
 * @ingroup message
 */
class MessageUiForm extends ContentEntityForm {

  /**
   * Constructs a ContentEntityForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    parent::__construct($entity_manager);
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /** @var Message $message */
    $message = $this->entity;

    // The UI for creating/editing the message.
    if (!is_object($message)) {
      $message = Message::create($message);
    }

    $view_builder = \Drupal::entityManager()->getViewBuilder('message');
    $message_text = $view_builder->view($message);

    if (\Drupal::config('message_ui.settings')->get('update_tokens.show_preview')) {
      $form['text'] = array(
        '#type' => 'item',
        '#title' => t('Message text'),
        '#markup' => render($message_text),
      );
    }

    $form = parent::form($form, $form_state);

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
        'library' => array('message_ui/message_ui.message'),
        'drupalSettings' => array(
          'message_ui' => array('anonymous' => \Drupal::config('message_ui.settings')->get('anonymous')),
        )
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
    $url = is_object($message) && !empty($mid) ? Url::fromRoute('entity.message.canonical', ['message' => $mid]) : Url::fromRoute('message.overview_types');

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
  /*
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // @todo Custom validation, what validation is needed?
    // D7: field_attach_form_validate('message', $form_state['#entity'], $form, $form_state);
  }
  */

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /* @var $message Message */
    $message = $this->entity;
    $insert = $message->isNew();
    $message_link = $message->link($this->t('View'));
    $context = array('@type' => $message->getType(), '%title' => $message->label(), 'link' => $message_link);
    $t_args = array('@type' => $message->getEntityType()->getLabel(), '%title' => $message->label());

    // @todo: submit handlers are removed, what is needed below? https://www.drupal.org/node/1846648
    // field_attach_submit('message', $message, $form, $form_state);

    $replace_tokens = $form_state->getValue('replace_tokens');

    // Update the tokens.
    $token_actions = empty($replace_tokens) ? array() : $replace_tokens;

    $args = $message->getArguments();

    if (is_object($message) && !empty($args)) {
      // @todo : get tokens and token actions working.
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

    $message->setAuthorId(user_load_by_name($form_state->getValue('name')));
    $message->setCreatedTime(strtotime($form_state->getValue('date')));
    $message->save();

    if ($insert) {
      $this->logger('content')->notice('@type: added %title.', $context);
      drupal_set_message(t('@type %title has been created.', $t_args));
    }
    else {
      $this->logger('content')->notice('@type: updated %title.', $context);
      drupal_set_message(t('@type %title has been updated.', $t_args));
    }

    $form_state->setRedirect('entity.message.canonical', ['message' => $message->id()]);
  }
}
