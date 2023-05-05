<?php

namespace Drupal\graphql_schema\Wrappers;

/**
 * Helper class that wraps entity queries.
 */
interface QueryConnectionInterface {

  /**
   * Retrieves the count of the results.
   *
   * @return int
   *   The count of result items.
   */
  public function total();

  /**
   * Retrieves the result items.
   *
   * @return array|\GraphQL\Deferred
   *   The result items.
   */
  public function items();

}
