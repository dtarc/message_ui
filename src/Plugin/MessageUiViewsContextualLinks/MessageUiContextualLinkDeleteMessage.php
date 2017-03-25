<?php

namespace Drupal\message_ui\Plugin\MessageUiViewsContextualLinks;

use Drupal\Core\Url;
use Drupal\message_ui\MessageUiViewsContextualLinksBase;
use Drupal\message_ui\MessageUiViewsContextualLinksInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * @MessageUiViewsContextualLinks(
 *  id = "message_ui_contextual_link_delete_message",
 *  label = @Translation("Button the delete a message."),
 *  weight = 2
 * )
 */
class MessageUiContextualLinkDeleteMessage extends MessageUiViewsContextualLinksBase implements MessageUiViewsContextualLinksInterface , ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManager $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function access() {
    return $this->message->access('delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getRouterInfo() {
    return [
      'title' => t('Delete'),
      'url' => Url::fromRoute('entity.message.delete_form', ['message' => $this->message->id()]),
    ];
  }

}
