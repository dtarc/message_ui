<?php

/**
 * @file
 * Contains \Drupal\message_ui\Form\MessageUiDeleteForm.
 */

namespace Drupal\message_ui\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;

/**
 * Provides a form for deleting a message.
 */
class MessageUiDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  protected function getDeletionMessage() {
    /** @var \Drupal\message\MessageInterface $entity */
    $entity = $this->getEntity();

    $message_type_storage = $this->entityManager->getStorage('message_type');
    $message_type = $message_type_storage->load($entity->bundle())->label();

    if (!$entity->isDefaultTranslation()) {
      return $this->t('@language translation of the @type %label has been deleted.', [
        '@language' => $entity->language()->getName(),
        '@type' => $message_type,
        '%label' => $entity->label(),
      ]);
    }

    return $this->t('The @type %title has been deleted.', array(
      '@type' => $message_type,
      '%title' => $this->getEntity()->label(),
    ));
  }

  /**
   * {@inheritdoc}
   */
  protected function logDeletionMessage() {
    /** @var \Drupal\message\MessageInterface $entity */
    $entity = $this->getEntity();
    $this->logger('content')->notice('@type: deleted %title.', ['@type' => $entity->getEntityType(), '%title' => $entity->label()]);
  }

}
