<?php

namespace CustomerScope\Model;

use CustomerScope\Model\Map\CustomerScopeTableMap;
use CustomerScope\Model\Map\ScopeTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Thelia\Model\CustomerQuery as BaseCustomerQuery;
use Thelia\Model\Map\CustomerTableMap;

/**
 * Custom customer queries.
 * Can be used in two ways:
 *     - As a CustomerQuery replacement.
 *     - By using the provided static methods on an existing query.
 *     This query must extend CustomerQuery or have a join to the customer table.
 */
class CustomerQuery extends BaseCustomerQuery
{
    /**
     * {@inheritdoc}
     * @return CustomerQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof CustomerQuery) {
            return $criteria;
        }
        $query = new CustomerQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }


    /**
     * Filter the query by scope. Scopes are defined by (ScopeEntity + EntityId) sorted by entity type
     * Customers that belongs to at least one scope will be returned.
     *
     * Example scopes:
     * <code>
     *[[
     *    'ScopeEntity' => ScopeEntity::ENTITY_CODE_STORE,
     *    'EntityId' => $store->getId()
     *],
     *[
     *    'ScopeEntity' => ScopeEntity::ENTITY_CODE_STORE_GROUP,
     *    'EntityId' => $store->getId()
     *]]
     * </code>
     * @param ModelCriteria $query The query to filter.
     * @param array $scopes An array of scopes.
     * @param Boolean $withChild get customer of child or not
     * @return ModelCriteria $this The query.
     */
    public static function addScopesFilter(ModelCriteria $query, array $scopes, $withChild = true)
    {
        if (!is_array($scopes) || empty($scopes)) {
            return $query;
        }

        $scopesForQuery = [];

        foreach ($scopes as $scope) {
            if (!isset($scope['ScopeEntity']) || !isset($scope['EntityId'])) {
                continue;
            }
            $scopesForQuery[$scope['ScopeEntity']][] = $scope['EntityId'];
            if ($withChild === true) {
                $scopeEntity = (new ScopeEntityHelper())->getEntityByType($scope['ScopeEntity'], $scope['EntityId']);
                if (null === $scopeEntity) {
                    continue;
                }
                $scopesForQuery = self::addChildsToQueryArray($scopeEntity, $scopesForQuery);
            }
        }

        // join to customer_scope
        $query
            ->addJoin(
                CustomerTableMap::ID,
                CustomerScopeTableMap::CUSTOMER_ID,
                Criteria::LEFT_JOIN
            )
        // join to scope
            ->addJoin(
                CustomerScopeTableMap::SCOPE_ID,
                ScopeTableMap::ID,
                Criteria::LEFT_JOIN
            );

        $scopeConditionsNames = [];

        // build the conditions for each scope type
        if (!empty($scopesForQuery)) {
            foreach ($scopesForQuery as $scopeType => $scopes) {
                $conditionName = 'condition_customer_scope_' . $scopeType;
                $query
                    ->condition(
                        'condition_scope_entity',
                        ScopeTableMap::ENTITY . Criteria::EQUAL . '?',
                        $scopeType,
                        \PDO::PARAM_STR
                    )
                    ->condition(
                        'condition_entity_id',
                        CustomerScopeTableMap::ENTITY_ID . Criteria::IN . "(?)",
                        implode(',', $scopes),
                        \PDO::PARAM_STR
                    )
                    ->combine(
                        ['condition_scope_entity', 'condition_entity_id'],
                        Criteria::LOGICAL_AND,
                        $conditionName
                    );
                $scopeConditionsNames[] = $conditionName;
            }
        }

        // add the scope conditions
        if (!empty($scopeConditionsNames)) {
            $query
                ->where($scopeConditionsNames, Criteria::LOGICAL_OR)
                ->distinct();
        }

        return $query;
    }


    /**
     * @see \CustomerScope\Model\CustomerQuery::addScopesFilter()
     *
     * @param array $scopes
     * @param Boolean $withChild get customer of child or not
     * @return CustomerQuery
     */
    public function filterByScopes(array $scopes, $withChild = true)
    {
        return self::addScopesFilter($this, $scopes, $withChild);
    }

    /**
     * @see \CustomerScope\Model\CustomerQuery::addScopesFilter()
     *
     * @param ModelCriteria $query
     * @param array $scope
     * @param Boolean $withChild get customer of child or not
     * @return ModelCriteria
     */
    public static function addScopeFilter(ModelCriteria $query, array $scope, $withChild = true)
    {
        return self::addScopesFilter($query, [$scope], $withChild);
    }

    /**
     * @see \CustomerScope\Model\CustomerQuery::addScopesFilter()
     *
     * @param array $scope The scope.
     * @param Boolean $withChild get customer of child or not
     * @return CustomerQuery
     */
    public function filterByScope(array $scope, $withChild = true)
    {
        return self::addScopeFilter($this, $scope, $withChild);
    }

    /**
     * Store all child recursively in array formatted for the filter query
     * @param $scopeEntity
     * @param array $scopesForQuery
     * @return array
     */
    public static function addChildsToQueryArray($scopeEntity, array $scopesForQuery)
    {
        $scopeEntityHelper = new ScopeEntityHelper();

        $childs = $scopeEntityHelper->getChilds($scopeEntity);
        $childType = null;
        $childHaveChild = null;

        if (null === $childs) {
            return $scopesForQuery;
        }

        foreach ($childs as $child) {
            //If is the first child set type of child for array and look if this is the last level or not with hasChild
            if ($childType === null) {
                $childType = $scopeEntityHelper->getScopeByEntity($child)->getEntity();
                $childHaveChild = $scopeEntityHelper->hasChild($child);
            }

            //Store all childs in good type in the array for query
            $scopesForQuery[$childType][] = $child->getId();

            //If this is not the last level put also the child of child in array
            if ($childHaveChild === true) {
                $scopesForQuery = self::addChildsToQueryArray($child, $scopesForQuery);
            }
        }
        return $scopesForQuery;
    }
}
