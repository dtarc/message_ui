<?php

namespace Drupal\message_ui\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Language\Language;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\message\Entity\Message;
use Drupal\user\Entity\User;

/**
 * Form controller for the message_ui entity edit forms.
 *
 * @ingroup message_ui
 */
class MessageForm extends ContentEntityForm {
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    /** @var Message $message */
    $message = $this->getEntity();

    // Access the message text from the view builder.
    $view_builder = \Drupal::entityManager()->getViewBuilder('message');
    $message_text = $view_builder->view($message);

    if (\Drupal::config('message_ui.settings')->get('update_tokens.show_preview')) {
      $form['text'] = array(
          '#type' => 'item',
          '#title' => t('Message text'),
          '#markup' => render($message_text),
      );
    }

    // Create the advanced vertical tabs "group".
    $form['advanced'] = array(
        '#type' => 'vertical_tabs',
        '#attributes' => array('class' => array('entity-meta')),
        '#weight' => 99,
    );

    $form['owner'] = array(
        '#type' => 'fieldset',
        '#title' => t('Owner information'),
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#group' => 'advanced',
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

    $form['owner']['uid'] = array(
        '#type' => 'entity_autocomplete',
        '#target_type' => 'user',
        '#title' => t('Created by'),
        '#selection_settings' => ['include_anonymous' => FALSE],
        '#weight' => 99,
        '#description' => t('Leave blank for %anonymous.', array('%anonymous' => \Drupal::config('message_ui.settings')->get('anonymous'))),
        '#default_value' => ($message->getOwnerId() ? $message->getOwnerId() : NULL),
        '#access' => $this->currentUser()->hasPermission('bypass message access control'),
    );

    $form['owner']['date'] = array(
        '#type' => 'textfield',
        '#title' => t('Created on'),
        '#description' => t('Please insert in the format of @date', array(
            '@date' => date('Y-m-d j:i', $message->getCreatedTime()),
        )),
        '#default_value' => date('Y-m-d H:i', $message->getCreatedTime()),
        '#maxlength' => 25,
        '#weight' => 100,
    );

    // @todo: assess the best way to access and create tokens tab from D7.
    $args = $message->getArguments();

    if (!empty($args) && (\Drupal::currentUser()->hasPermission('update tokens') || \Drupal::currentUser()->hasPermission('bypass message access control'))) {
      $form['tokens'] = array(
          '#type' => 'fieldset',
          '#title' => t('Tokens and arguments'),
          '#collapsible' => TRUE,
          '#collapsed' => TRUE,
          '#group' => 'advanced',
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

    $form['langcode'] = array(
        '#title' => $this->t('Language'),
        '#type' => 'language_select',
        '#default_value' => $message->getUntranslated()->language()->getId(),
        '#languages' => Language::STATE_ALL,
    );

    // @todo : add similar to node/from library, adding css for 'message-form-owner' class.
    // $form['#attached']['library'][] = 'node/form';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);
    $message = $this->entity;

    // @todo : check if we need access control here on form submit.
    // Create custom save button with conditional label / value.
    $element['save'] = $element['submit'];
    if ($message->isNew()) {
      $element['save']['#value'] = t('Create');
    }
    else {
      $element['save']['#value'] = t('Update');
    }
    $element['save']['#weight'] = 0;

    $mid = $message->id();
    $url = is_object($message) && !empty($mid) ? Url::fromRoute('entity.message.canonical', ['message' => $mid]) : Url::fromRoute('message.overview_types');

    $link =  \Drupal::l(t('Cancel'), $url);

    $element['cancel'] = array(
        '#type' => 'markup',
        '#markup' => $link
    );

    // Remove the default "Save" button.
    $element['submit']['#access'] = FALSE;

    return $element;
  }

  /**
   * {@inheritdoc}
   *
   * Updates the message object by processing the submitted values.
   *
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Build the node object from the submitted values.
    parent::submitForm($form, $form_state);
    /* @var $message Message */
    $message = $this->entity;

    // @todo - can you set this here or only after $message->save()?
    // Set message owner and timestamp.
    $message->setOwnerId($form_state->getValue('uid'));
    $message->setCreatedTime(strtotime($form_state->getValue('date')));

    // Get the tokens to be replaced and prepare for replacing.
    $replace_tokens = $form_state->getValue('replace_tokens');
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
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /* @var $message Message */
    $message = $this->entity;
    $insert = $message->isNew();

    $message->save();

    // Set up message link and status message contexts.
    $message_link = $message->link($this->t('View'));
    $context = array('@type' => $message->getType(), '%title' => 'Message:' . $message->id(), 'link' => $message_link);
    $t_args = array('@type' => $message->getEntityType()->getLabel(), '%title' => 'Message:' . $message->id());

    // Display newly created or updated message depending on if new entity.
    if ($insert) {
      $this->logger('content')->notice('@type: added %title.', $context);
      drupal_set_message(t('@type %title has been created.', $t_args));
    }
    else {
      $this->logger('content')->notice('@type: updated %title.', $context);
      drupal_set_message(t('@type %title has been updated.', $t_args));
    }

    // Redirect to message view display if user has access.
    if ($message->id()) {
      $form_state->setValue('mid', $message->id());
      $form_state->set('mid', $message->id());
      if ($message->access('view')) {
        $form_state->setRedirect(
            'entity.message.canonical',
            ['message' => $message->id()]
        );
      }
      else {
        $form_state->setRedirect('<front>');
      }
      // @todo : for node they clear temp store here, but perhaps unused with message.
    }
    else {
      // In the unlikely case something went wrong on save, the message will be
      // rebuilt and message form redisplayed.
      drupal_set_message(t('The message could not be saved.'), 'error');
      $form_state->setRebuild();
    }
  }

}
