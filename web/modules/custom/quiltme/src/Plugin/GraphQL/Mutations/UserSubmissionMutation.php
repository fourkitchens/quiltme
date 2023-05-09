<?php

namespace Drupal\quiltme\Plugin\GraphQL\Mutations;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql_core\Plugin\GraphQL\Mutations\Entity\CreateEntityBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Simple mutation for creating a new article node.
 *
 * @GraphQLMutation(
 *   id = "create_user_submission",
 *   entity_type = "node",
 *   entity_bundle = "user_submission",
 *   secure = true,
 *   name = "createUserSubmission",
 *   type = "EntityCrudOutput!",
 *   arguments = {
 *     "input" = "UserSubmissionInput"
 *   }
 * )
 */
class UserSubmissionMutation extends CreateEntityBase {

  /**
   * {@inheritDoc}
   */
  protected function extractEntityInput($value, array $args, ResolveContext $context, ResolveInfo $info) {
    return [
      'title' => $args['input']['email'],
      'field_user_email' => $args['input']['email'],
      'field_original_image' => $args['input']['image'],
      'field_background_image' => $args['input']['backgroundImage'],
    ];
  }

}
