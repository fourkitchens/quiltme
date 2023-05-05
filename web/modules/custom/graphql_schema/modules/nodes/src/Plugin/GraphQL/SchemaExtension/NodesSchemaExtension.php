<?php

namespace Drupal\nodes\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_schema\Wrappers\QueryConnection;
use Drupal\node\NodeInterface;
use GraphQL\Error\Error;
/**
 * A schema extension for Nodes.
 *
 * @SchemaExtension(
 *   id = "nodes_extension",
 *   name = "Nodes Schema Extension",
 *   description = "A simple extension that adds nodes base related fields.",
 *   schema = "graphql_schema"
 * )
 */
class NodesSchemaExtension extends SdlSchemaExtensionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();
    $this->addNodeInterfaceTypeResolver($registry);
    $this->addQueryFields($registry, $builder);
    $this->addArticleFields($registry, $builder);
    $this->addMutationFields($registry, $builder);
    $this->addConnectionFields($registry, $builder);
  }

  /**
   * Add NodeInterface type resolver.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   *   The registry interface.
   */
  protected function addNodeInterfaceTypeResolver(ResolverRegistryInterface $registry) : void {
    // Tell GraphQL how to resolve types of a common interface.
    $registry->addTypeResolver('NodeInterface', function ($value) {
      if ($value instanceof NodeInterface) {
        switch ($value->bundle()) {
          case 'article':
            return 'NodeArticle';
        }
      }
      throw new Error('Could not resolve content type.');
    });
  }

  /**
   * Add Query fields resolvers.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   *   The registry interface.
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   *   The builder.
   */
  protected function addQueryFields(ResolverRegistryInterface $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Query', 'article',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('node'))
        ->map('bundles', $builder->fromValue(['article']))
        ->map('id', $builder->fromArgument('id'))
    );

    $registry->addFieldResolver('Query', 'articles',
      $builder->produce('query_nodes')
        ->map('bundle', $builder->fromValue('article'))
        ->map('conditions', $builder->fromArgument('conditions'))
        ->map('offset', $builder->fromArgument('offset'))
        ->map('limit', $builder->fromArgument('limit'))
    );
  }

  /**
   * Add Article fields resolvers.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   *   The registry interface.
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   *   The builder.
   */
  protected function addArticleFields(ResolverRegistryInterface $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('NodeArticle', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('NodeArticle', 'title',
      $builder->produce('entity_label')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('NodeArticle', 'url',
      $builder->callback(function () {
        return 'test';
      })
    );
  }

  /**
   * Add Mutation field resolvers.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   *   The registry interface.
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   *   The builder object.
   */
  protected function addMutationFields(ResolverRegistryInterface $registry, ResolverBuilder $builder): void {
  }

  /**
   * Add NodeConnection fields resolvers.
   *
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   *   The registry interface.
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   *   The builder.
   */
  protected function addConnectionFields(ResolverRegistryInterface $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('NodeConnection', 'total',
      $builder->callback(function (QueryConnection $connection) {
        return $connection->total();
      })
    );
    $registry->addFieldResolver('NodeConnection', 'items',
      $builder->callback(function (QueryConnection $connection) {
        return $connection->items();
      })
    );
  }
}
