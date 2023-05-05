<?php

namespace Drupal\graphql_schema\Wrappers;

use Drupal\Core\Entity\Query\QueryInterface;
use GraphQL\Deferred;
use Drupal\Core\Entity\Query\QueryException;
use GraphQL\Error\UserError;

/**
 * Helper class that wraps entity queries.
 */
class QueryConnection implements QueryConnectionInterface {

  /**
   * The Query property.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $query;

  /**
   * QueryConnection constructor.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   Query interface.
   */
  public function __construct(QueryInterface $query) {
    $this->query = $query;
  }

  /**
   * Retrieves the count of the results.
   *
   * @return int
   *   The count of result items.
   */
  public function total() {
    $query = clone $this->query;
    $query->range(NULL, NULL)->count();
    try {
      return $query->execute();
    }
    catch (QueryException $e) {
      throw new UserError(sprintf($e->getMessage()));
    }
  }

  /**
   * Retrieves the result items.
   *
   * @return array|\GraphQL\Deferred
   *   The result items.
   */
  public function items() {
    try {
      $result = $this->query->execute();
    }
    catch (QueryException $e) {
      throw new UserError(sprintf($e->getMessage()));
    }

    if (empty($result)) {
      return [];
    }

    /** @var \Drupal\graphql\GraphQL\Buffers\EntityBuffer $buffer */
    $buffer = \Drupal::service('graphql.buffer.entity');
    $callback = $buffer->add($this->query->getEntityTypeId(), array_values($result));
    return new Deferred(function () use ($callback) {
      return $callback();
    });
  }

}
