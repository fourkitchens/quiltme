<?php

namespace Drupal\nodes\Plugin\GraphQL\DataProducer;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\graphql_schema\Wrappers\QueryConnection;
use GraphQL\Error\UserError;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryInterface;

/**
 * Query to Nodes.
 *
 * @DataProducer(
 *   id = "query_nodes",
 *   name = @Translation("Load nodes"),
 *   description = @Translation("Loads a list of nodes by bundle."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Group connection")
 *   ),
 *   consumes = {
 *    "bundle" = @ContextDefinition("string",
 *       label = @Translation("Group bundle"),
 *       required = FALSE,
 *       default_value = NULL
 *     ),
 *    "conditions" = @ContextDefinition("any",
 *       label = @Translation("Conditions"),
 *       multiple = TRUE,
 *       required = FALSE
 *     ),
 *     "condition_group" = @ContextDefinition("any",
 *       label = @Translation("Condition Nodes"),
 *       required = FALSE
 *     ),
 *     "offset" = @ContextDefinition("integer",
 *       label = @Translation("Offset"),
 *       required = FALSE
 *     ),
 *     "limit" = @ContextDefinition("integer",
 *       label = @Translation("Limit"),
 *       required = FALSE
 *     ),
 *     "access" = @ContextDefinition("boolean",
 *       label = @Translation("Check access"),
 *       required = FALSE,
 *       default_value = TRUE
 *     )
 *   }
 * )
 */
class QueryNodes extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  const MAX_LIMIT = 100;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * QueryNodes constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   *
   * @codeCoverageIgnore
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Resolver.
   *
   * @param string|null $bundle
   *   The Group bundle.
   * @param array|null $conditions
   *   Conditions to filter.
   * @param array|null $condition_group
   *   Condition group to filter.
   * @param int $offset
   *   Offset of results.
   * @param int $limit
   *   The number of results.
   * @param bool $access
   *   Check entities access.
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *   The metadata object.
   *
   * @return \Drupal\graphql_schema_v1\Wrappers\QueryConnection
   *   Connection for listing results.
   */
  public function resolve(?string $bundle, ?array $conditions, ?array $condition_group, int $offset, int $limit, bool $access, RefinableCacheableDependencyInterface $metadata) {
    if ($limit > self::MAX_LIMIT) {
      throw new UserError(sprintf('Exceeded maximum query limit: %s.', self::MAX_LIMIT));
    }

    $storage = $this->entityTypeManager->getStorage('node');
    $entityType = $storage->getEntityType();
    $query = $storage->getQuery()
      ->currentRevision()
      ->accessCheck($access);

    if ($bundle) {
      $query->condition($entityType->getKey('bundle'), $bundle);
    }

    if ($conditions) {
      $this->addConditions($query, $conditions);
    }

    if ($condition_group) {
      $this->addConditionGroup($query, $condition_group);
    }

    $query->range($offset, $limit);

    $metadata->addCacheTags($entityType->getListCacheTags());
    $metadata->addCacheContexts($entityType->getListCacheContexts());

    return new QueryConnection($query);
  }

  /**
   * Adds conditions to the node query.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface|\Drupal\Core\Entity\Query\ConditionInterface $query
   *   The query entity.
   * @param array|null $conditions
   *   The conditions to be added.
   */
  private function addConditions($query, $conditions) {
    // Loop through conditions to add them into the query.
    foreach ($conditions as $condition) {
      if (empty($condition['operator'])) {
        $condition['operator'] = '=';
      }
      if ($condition['value'] == 'NULL') {
        $condition['value'] = NULL;
      }
      // Set the condition in the query.
      $query->condition(
        $condition['name'],
        $condition['value'],
        $condition['operator']
      );
    }

  }

  /**
   * Adds condition nodes to the node query.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   The query entity.
   * @param array|null $condition_group
   *   The condition group to be added.
   */
  private function addConditionGroup(QueryInterface $query, $condition_group) {
    $operator = $condition_group['operator'];

    if ($operator === 'AND') {
      $query_condition = $query->andConditionGroup();
    }
    else {
      $query_condition = $query->orConditionGroup();
    }

    $this->addConditions($query_condition, $condition_group['conditions']);
    $query->condition($query_condition);
  }

}