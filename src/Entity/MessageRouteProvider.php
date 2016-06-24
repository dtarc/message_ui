<?php

/**
 * @file
 * Contains \Drupal\message_ui\Entity\MessageRouteProvider.
 */

namespace Drupal\message_ui\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\EntityRouteProviderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provides routes for messages.
 */
class MessageRouteProvider implements EntityRouteProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function getRoutes( EntityTypeInterface $entity_type) {
    $route_collection = new RouteCollection();

    $route = (new Route('/message/{message}/edit'))
        ->setDefault('_entity_form', 'message.edit')
        ->setRequirement('_message_ui_access', 'edit')
        ->setOption('_admin_route', TRUE);
    $route_collection->add('entity.message.edit_form', $route);

    $route = (new Route('/message/{message}/delete'))
      ->addDefaults([
        '_entity_form' => 'message.delete',
        '_title' => 'Delete',
      ])
      ->setRequirement('_message_ui_access', 'delete')
      ->setOption('_admin_route', TRUE);
    $route_collection->add('entity.message.delete_form', $route);

    return $route_collection;
  }
}
