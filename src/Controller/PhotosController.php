<?php

namespace Drupal\photos\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Defines AlbumController class.
 */
class PhotosController extends ControllerBase {

  /**
   * Return Content.
   *
   * @return array
   *   Return text
   */
  public function content() {

    $form = \Drupal::formBuilder()->getForm('Drupal\photos\Form\PhotosForm');
    $form['#attached']['library'][] = 'photos/global-styling';

    return [
      '#theme' => 'photos_template',
      '#form' => $form,
    ];
  }

}
