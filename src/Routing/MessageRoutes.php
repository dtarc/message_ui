<?php
/**
 * @file
 * Contains \Drupal\message_ui\Routing\MessageRoutes.
 */

namespace Drupal\message_ui\Routing;

use Symfony\Component\Routing\Route;

/**
 * Defines dynamic routes.
 */
class MessageRoutes {
  /**
   * {@inheritdoc}
   */
  public function routes() {
    $routes = array();

    // @todo Complete code for interaction with devel module for this menu item.
    //    $items['message/%message/devel'] = array(
    //      'title' => 'Devel',
    //      'page callback' => 'devel_load_object',
    //      'page arguments' => array('message', 1),
    //      'access arguments' => array('access devel information'),
    //      'type' => MENU_LOCAL_TASK,
    //      'context' => MENU_CONTEXT_PAGE | MENU_CONTEXT_INLINE,
    //      'file' => 'devel.pages.inc',
    //      'file path' => drupal_get_path('module', 'devel'),
    //      'weight' => 20,
    //    );

    // @todo : enabled for testing.
    // $devel_enabled = \Drupal::moduleHandler()->moduleExists('devel');

    // If the devel module is installed, create this route.
    if (!empty($devel_enabled)) {
      // @todo Add this route for deleting via devel module.
      //    message_ui.delete_multiple:
      //  path: '/admin/config/development/message_delete_multiple'
      //  defaults:
      //    _title: 'Message delete multiple'
      //    _controller: '\Drupal\message_ui\Controller\MessageController::deleteMultiple'
      //  options:
      //    _admin_route: TRUE

      // Declares a single route under the name 'entity.message.canonical'.
      // Returns an array of Route objects.
      $routes['entity.message.canonical.devel'] = new Route(
      // Path to attach this route to:
        '/message/{message}/devel',
        // Route defaults:
        array(
          '_controller' => '\Drupal\message_ui\Controller\MessageController::devel',
          '_title' => 'Devel'
        ),
        // Route requirements:
        array(
          '_permission' => 'access content',
        )
      );
    }
    return $routes;
  }
}
