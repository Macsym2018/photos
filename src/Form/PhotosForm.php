<?php

namespace Drupal\photos\Form;

use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\album\AlbumService;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Main class of form.
 *
 * @file
 * Contains \Drupal\photos\Form\PhotosForm.
 */

/**
 * Class PhotosForm create form and show photos depend on user Id.
 *
 * @package Drupal\photos\Form
 */
class PhotosForm extends FormBase {

  /**
   * Guzzle\Client instance.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * PhotosForm block constructor.
   *
   * @param \Drupal\album\AlbumService $http_client
   *   \Drupal\album\AlbumService instance.
   */
  public function __construct(AlbumService $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('album.album')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'photos_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['userid'] = [
      '#type' => 'textfield',
      '#title' => 'User ID',
      '#ajax' => [
        'wrapper' => 'loanduration-wrapper',
        'callback' => [$this, 'createAlbumsList'],
        'event' => 'change',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Verifying email..'),
        ],
      ],
      '#prefix' => '<div class="error-messages"></div>',

    ];

    $form['album'] = [
      '#type' => 'select',
      '#title' => 'Select Album',
      '#suffix' => '<div class="dummy-photos"></div>',
      '#options' => isset($albums) ? $albums : '',
      '#prefix' => '<div id="loanduration-wrapper">',
      '#suffix' => '</div>',
    ];

    return $form;
  }

  /**
   * Valid form and create a list that contain albums.
   *
   * @param array $form
   *   Current form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The complete form array.
   *
   * @return array
   *   Return message about error.
   */
  public function createAlbumsList(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $value = &$form['userid']['#value'];

    if (is_numeric($value)) {
      if (!ctype_digit($value)) {
        $response->addCommand(new HtmlCommand('.error-messages', 'It is not a whole number'));
      }
      else {

        $userId = $value;
        $albums = $this->httpClient->getAlbumsByUserId($userId);
        $form['album']['#options'] = $albums;

        return $form['album'];
        // $response->addCommand(new HtmlCommand('.error-messages', ''));
        // $response->addCommand(new HtmlCommand('.error-messages',
        // $form['album']));
      }
    }
    else {
      $response->addCommand(new HtmlCommand('.error-messages', 'It is not a number'));
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  /*public function validateForm(array &$form, FormStateInterface $form_state)
  {

  }*/

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
