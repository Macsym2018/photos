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
      '#validated' => TRUE,
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
      '#validated' => TRUE,
      '#ajax' => [
        'wrapper' => 'dummy-photos',
        'callback' => [$this, 'createPhotosPreview'],
        'event' => 'change',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Please wait'),
        ],
      ],
      '#suffix' => '<div class="dummy-photos"></div></div>',
      '#options' => ['default' => $this->t('Select')],
      '#prefix' => '<div id="loanduration-wrapper">',

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
        $albums = ['default' => $this->t('Select')];

        $almumsCurrentUser = $this->httpClient->getAlbumsByUserId($userId);
        if ($almumsCurrentUser) {
          $albums += $almumsCurrentUser;
        }
        else {
          $response->addCommand(new HtmlCommand('.error-messages', 'Albums with this user ID not found'));
        }
        // $albums += ['default' => $this->t('Select')];
        $form['album']['#options'] = $albums;
        $form_state->setValue('album', 'default');
        $form_state->setRebuild(TRUE);
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
   * Show phoots from album.
   *
   * @param array $form
   *   Current form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The complete form array.
   *
   * @return Drupal\Core\Ajax\AjaxResponse
   *   Return Ajax response.
   */
  public function createPhotosPreview(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $albumId = $form['album']['#value'];
    $photos = $this->httpClient->getAlbumPhotos($albumId);

    if ($photos) {
      foreach ($photos as $photo) {
        $render .= '<h1>' . $photo['title'] . '</h1>';
        $render .= '<img src=' . $photo['url'] . '>';
      }

      $response->addCommand(new HtmlCommand('.dummy-photos', $render));
    }
    else {
      $response->addCommand(new HtmlCommand('.error-messages', 'Photos with this album ID not found'));
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
