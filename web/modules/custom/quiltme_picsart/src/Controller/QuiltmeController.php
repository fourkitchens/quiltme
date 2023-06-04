<?php

namespace Drupal\quiltme_picsart\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * An example controller.
 */
class QuiltmeController extends ControllerBase {

  /**
   * Creates a media image and returns the ID.
   */
  public function content() {
    // $service = \Drupal::service('quiltme_picsart.connector');
    // $media_id = $service->getStyleTransferMediaId($options);
    $build = [
      '#markup' => $this->t('You have created a media with id @id', ['@id' => $media_id]),
    ];
    return $build;
  }

}
