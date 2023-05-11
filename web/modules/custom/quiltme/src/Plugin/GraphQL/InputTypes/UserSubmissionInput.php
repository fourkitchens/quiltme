<?php

namespace Drupal\quiltme\Plugin\GraphQL\InputTypes;

use Drupal\graphql\Plugin\GraphQL\InputTypes\InputTypePluginBase;

/**
 * Custom Input type for User Submission.
 *
 * @GraphQLInputType(
 *   id = "user_submission_input",
 *   name = "UserSubmissionInput",
 *   fields = {
 *     "title" = "String",
 *     "email" = "String",
 *     "image" = "Upload",
 *     "backgroundImage" = "Upload"
 *   }
 * )
 */
class UserSubmissionInput extends InputTypePluginBase {
}
