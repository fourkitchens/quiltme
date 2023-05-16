<?php

namespace Drupal\quiltme_picsart;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use GuzzleHttp\Client as GuzzleClient;
use Drupal\media\Entity\Media;
use Drupal\file\FileRepositoryInterface;

/**
 * Defines the QuiltmePicsart service class.
 */
  class QuiltmePicsart {

  /**
   * The HTTP client to fetch the feed data with.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $client;

  /**
   * API options.
   */
  protected $apiOptions;

  /**
   * File system
   */
  protected $fileSystem;

  /**
   * File repository
   */
  protected $fileRepository;


    /**
   * AFT settings configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected ?ImmutableConfig $config;

  /**
   * Constructor for AFTConnect.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory interface.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system
   * @param \Drupal\file\FileRepositoryInterface $file_repository
   *   The file system
   */
  public function __construct(ConfigFactoryInterface $config_factory, FileSystemInterface $file_system, FileRepositoryInterface $file_repository) {
    $this->config = $config_factory->get('quiltme_picsart.api_settings');
    $this->apiKey = $this->config->get('api_key') ?? 'DdN5jspTz9ycUTv9mlgZxDtc4M0z1as9';
    $this->client = new GuzzleClient();
    $this->apiOptions = [];
    $this->fileSystem = $file_system;
    $this->fileRepository = $file_repository;
  }

    /**
     * Takes an array of options and returns a media id.
     *
     * @param array $options
     *   eg.
     *     $options = [
     *       'image_url' => 'https://pyxis.nymag.com/v1/imgs/c49/fa5/d43db44d60dee75b3ed58a3e57ef29d527-Vulturefest5333-1-copy.rvertical.w570.jpg',
     *       'reference_image_url' => 'https://i.etsystatic.com/16016281/r/il/f57f99/3099413915/il_1588xN.3099413915_qf9h.jpg',
     *       'level' => 'l5',
     *       'email' => 'laura@fourkitchens.com',
     *     ];
     *
     * @return int
     *   The media id.
     */
  public function getStyleTransferMediaId(array $options) : int {

    // Save the options for use in the API call.
    $this->apiOptions = $options;
    // Make the API call and get the returned image URL.
    $styleTransferImageUrl = $this->getStyleTransferImageUrl();

    // Get the contents of the image URL returned from PicsArt.
    $image_data = file_get_contents($styleTransferImageUrl);
    $directory = 'public://quiltme-api';
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    // Prepare the directory.
    $file_system = \Drupal::service('file_system');
    $file_system->prepareDirectory($directory, FileSystemInterface:: CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    $file_repository = \Drupal::service('file.repository');
    $image = $file_repository->writeData($image_data, "public://quiltme-api/{$options['email']} . '--style-transfer.jpg", FileSystemInterface::EXISTS_REPLACE);

    // Create image media.
    $image_media = Media::create([
      'name' => $options['email'] . '--style-transfer',
      'bundle' => 'image',
      'uid' => 1,
      'langcode' => 'en',
      'status' => 0,
      'field_media_image' => [
        'target_id' => $image->id(),
        'alt' => t($options['email'] . '--style-transfer'),
        'title' => t($options['email'] . '--style-transfer'),
      ],
    ]);
    $image_media->save();
    // Return the media ID.
    return $image_media->id();
  }

    /**
     * Make a http post to the PicsArt API and return the uri of the created file.
     *
     * @return string
     *   The file url returned by PicsArt.
     */
  private function getStyleTransferImageUrl() : string {
    try {
      $result = $this->client->post('https://api.picsart.io/tools/1.0/styletransfer', [
        'headers' => [
          'Accept' => 'application/json',
          'Content-Type' => 'application/x-www-form-urlencoded',
          'X-Picsart-API-Key' => $this->apiKey,
        ],
        'form_params' => $this->apiOptions,
      ]);
      $result = json_decode($result->getBody()->getContents(), TRUE);
      // Return the URL.
      return $result['data']['url'];
    } catch (\Exception $e) {
      echo 'Exception when calling StyleTransferApi: ', $e->getMessage(), PHP_EOL;
    }
  }

}