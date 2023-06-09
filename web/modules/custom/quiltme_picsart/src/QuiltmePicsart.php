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
  protected GuzzleClient $client;

  /**
   * API options.
   *
   * @var array
   */
  protected array $apiOptions;

  /**
   * File system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected FileSystemInterface $fileSystem;

  /**
   * File repository.
   *
   * @var \Drupal\file\FileRepositoryInterface
   */
  protected FileRepositoryInterface $fileRepository;

  /**
   * Configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected ?ImmutableConfig $config;

  /**
   * Constructor for dependency injection.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory interface.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   * @param \Drupal\file\FileRepositoryInterface $file_repository
   *   The file system.
   */
  public function __construct(ConfigFactoryInterface $config_factory, FileSystemInterface $file_system, FileRepositoryInterface $file_repository) {
    $this->config = $config_factory->get('quiltme_picsart.api_settings');
    $this->apiKey = $this->config->get('api_key') ?? 'CFrayF4ePDAui3Uf012F51kAabYRkRLY';
    $this->client = new GuzzleClient();
    $this->fileSystem = $file_system;
    $this->fileRepository = $file_repository;
  }

  /**
   * Takes an array of options and returns a media id.
   *
   * @param array $options
   *   Eg.
   *     $options = [
   *       'image_url' => 'https://pyxis.nymag.com/v1/imgs/c49/fa5/d43db44d60dee75b3ed58a3e57ef29d527-Vulturefest5333-1-copy.rvertical.w570.jpg',
   *       'reference_image_url' => 'https://i.etsystatic.com/16016281/r/il/f57f99/3099413915/il_1588xN.3099413915_qf9h.jpg',
   *       'level' => 'l5',
   *       'email' => 'laura@fourkitchens.com',
   *     ];.
   *
   * @return int
   *   The media id.
   */
  public function getStyleTransferMediaId(array $options) : int {

    // Make the API call and get the returned image URL.
    $styleTransferImageUrl = $this->getStyleTransferImageUrl($options);

    // Get the contents of the image URL returned from PicsArt.
    $image_data = file_get_contents($styleTransferImageUrl);
    $directory = 'public://quiltme-api';
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    // Prepare the directory.
    $random_number = rand(100000, 999999);
    $this->fileSystem->prepareDirectory($directory, FileSystemInterface:: CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    $image = $this->fileRepository->writeData($image_data, "public://quiltme-api/{$options['email']}-{$options['pattern_number']}-{$random_number}--style-transfer.jpg", FileSystemInterface::EXISTS_REPLACE);

    // Create image media.
    $image_media = Media::create([
      'name' => $options['email'] . '--style-transfer',
      'bundle' => 'image',
      'uid' => 1,
      'langcode' => 'en',
      'status' => 1,
      'field_media_image' => [
        'target_id' => $image->id(),
        'alt' => $options['email'] . '--style-transfer',
        'title' => $options['email'] . '--style-transfer',
      ],
    ]);
    $image_media->save();
    // Return the media ID.
    return $image_media->id();
  }

  /**
   * Upload image method.
   *
   * @param array $options
   *   The data to send along.
   */
  public function uploadImage(array $options) : array {

    try {
      $result = $this->client->post('https://api.picsart.io/tools/1.0/upload', [
        'headers' => [
          'Accept' => 'application/json',
          'Content-Type' => 'application/x-www-form-urlencoded',
          'X-Picsart-API-Key' => $this->apiKey,
        ],
        'form_params' => [
          'image_url' => $options['image_url'],
        ],
      ]);
      if ($result) {
        $result = json_decode($result->getBody()->getContents(), TRUE);
        return $result['data'];
      }
      else {
        return [
          'message' => "Failed to upload",
        ];
      }
    }
    catch (Exception $e) {
      return [
        'message' => "Failed to upload",
      ];
    }
  }

  /**
   * Make a http post to the PicsArt API and return the file URI.
   *
   * @return string
   *   The file url returned by PicsArt.
   */
  public function getStyleTransferImageUrl(array $options) : string {
    try {
      $result = $this->client->post('https://api.picsart.io/tools/1.0/styletransfer', [
        'headers' => [
          'Accept' => 'application/json',
          'Content-Type' => 'application/x-www-form-urlencoded',
          'X-Picsart-API-Key' => $this->apiKey,
        ],
        'form_params' => $options,
      ]);
      $result = json_decode($result->getBody()->getContents(), TRUE);
      // Return the URL.
      return $result['data']['url'];
    }
    catch (\Exception $e) {
      echo 'Exception when calling StyleTransferApi: ', $e->getMessage(), PHP_EOL;
    }
  }

}
