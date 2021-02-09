<?php


/**
 * @file
 * Contains \Drupal\photos\Form\PhotosForm.
 */

namespace Drupal\photos\Form;
use \Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\album\AlbumService;
//use Drupal\album\Plugin\Block\AlbumBlock;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

class PhotosForm extends FormBase {

    /**
     * Guzzle\Client instance.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $httpClient;

    /**
     * Album block constructor.
     *
     * @param array $configuration
     *   The plugin configuration, i.e. an array with configuration values keyed
     *   by configuration option name. The special key 'context' may be used to
     *   initialize the defined contexts by setting it to an array of context
     *   values keyed by context names.
     * @param string $plugin_id
     *   The plugin_id for the plugin instance.
     * @param mixed $plugin_definition
     *   The plugin implementation definition.
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
     * @param array $configuration
     *   A configuration array containing information about the plugin instance.
     * @param string $plugin_id
     *   The plugin ID for the plugin instance.
     * @param mixed $plugin_definition
     *   The plugin implementation definition.
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
    public function buildForm(array $form, FormStateInterface $form_state)
    {



        $form['userid'] = array(
            '#type' => 'textfield',
            '#title' => t('User ID'),
          /*'#attributes' => [
            //define static name and id so we can easier select it
            // 'id' => 'select-colour',
            'name' => 'field_select_user',
          ],*/
            '#ajax' => [
              'wrapper' => 'loanduration-wrapper',
              'callback' => [$this, 'isNumberValidation'],
              # Событие, на которое будет срабатывать наш AJAX.
              'event' => 'change',
              # Настройки прогресса. Будет показана гифка с анимацией загрузки.
              'progress' => array(
                'type' => 'throbber',
                'message' => t('Verifying email..'),
              ),
            ],


          '#prefix' => '<div class="error-messages"></div>'

        );

      $form['album'] = array(
        '#type' => 'select',
        '#title' => t('Select Album'),
        '#suffix' => '<div class="dummy-photos"></div>',
        '#options' => isset($albums) ? $albums : '',
        '#prefix' => '<div id="loanduration-wrapper">',
        '#suffix' => '</div>',
        '#states' => [
          'visible' => [
            ':input[name="userid"]' => ['valid' => false ],
          ],
        ],

      );


        //var_dump($form['userid']);
        //die();

      //$albums = $this->httpClient->getAlbumsByUserId('10');


        return $form;
    }


  public function isNumberValidation(array &$form, FormStateInterface $form_state)
  {
    //$form['album'] = TRUE;
    $response = new AjaxResponse();
    $value = &$form['userid']['#value'];
    //$currentAlbums=&$form['album']['#options'];


    if (is_numeric($value)) {
      if (!ctype_digit($value)) {
        $response->addCommand(new HtmlCommand('.error-messages', 'It is not a whole number'));
      }else{

        $userId=$value;
        $albums = $this->httpClient->getAlbumsByUserId($userId);
        $form['album']['#options']=$albums;

        return $form['album'];
        //$response->addCommand(new HtmlCommand('.error-messages', ''));
        //$response->addCommand(new HtmlCommand('.error-messages', $form['album']));


      }
    }else {
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
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
      //drupal_set_message('Form submitted! Hooray!');
    }

}
