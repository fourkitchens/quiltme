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
    $service = \Drupal::service('quiltme_picsart.connector');

    $options = [
      'image_url' => 'https://pyxis.nymag.com/v1/imgs/c49/fa5/d43db44d60dee75b3ed58a3e57ef29d527-Vulturefest5333-1-copy.rvertical.w570.jpg',
      'reference_image_url' => 'https://i.etsystatic.com/16016281/r/il/f57f99/3099413915/il_1588xN.3099413915_qf9h.jpg',
      'level' => 'l5',
      'email' => 'laura@fourkitchens.com',
    ];

    $media_id = $service->getStyleTransferMediaId($options);

    $build = [
      '#markup' => $this->t('You have created a media with id @id', ['@id' => $media_id]),
    ];
    return $build;
  }

}