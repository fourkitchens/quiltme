<?php

namespace Drupal\quiltme\Plugin\GraphQL\Mutations;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Mutations\MutationPluginBase;
use GraphQL\Type\Definition\ResolveInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Simple mutation for creating a new article node.
 *
 * @GraphQLMutation(
 *   id = "create_user_submission",
 *   secure = true,
 *   name = "createUserSubmission",
 *   type = "NodeUserSubmission!",
 *   arguments = {
 *     "input" = "UserSubmissionInput"
 *   }
 * )
 */
class UserSubmissionMutation extends MutationPluginBase implements ContainerFactoryPluginInterface {

  use DependencySerializationTrait;

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition):UserSubmissionMutation {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * Sets this mutation up with dependency injection.
   *
   * @param array $configuration
   *   Plugin config.
   * @param string $plugin_id
   *   Plugin id.
   * @param string $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager service.
   */
  public function __construct(array $configuration, string $plugin_id, string $plugin_definition, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritDoc}
   */
  public function resolve($value, array $args, ResolveContext $context, ResolveInfo $info) {
    $node_storage = $this->entityTypeManager->getStorage('node');
    $node_data = [
      'title' => $args['input']['email'],
      'field_user_email' => $args['input']['email'],
      'type' => 'user_submission',
      'status' => TRUE,
      'author' => 0,
    ];

    // Handle file upload here.
    $node = $node_storage->create($node_data);
    $node?->save();

    return $node;
  }

}
