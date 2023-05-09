<?php

namespace Drupal\quiltme\Plugin\GraphQL\InputTypes;

use Drupal\graphql\Plugin\GraphQL\InputTypes\InputTypePluginBase;

/**
 * @GraphQLInputType(
 *   id = "user_submission_input",
 *   name = "UserSubmissionInput",
 *   fields = {
 *     "title" = "String",
 *     "email" = "String",
 *     "image" = "String",
 *     "backgroundImage" = "String"
 *   }
 * )
 */
class UserSubmissionInput extends InputTypePluginBase {
}
