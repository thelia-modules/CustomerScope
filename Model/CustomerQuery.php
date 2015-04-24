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
     * Filter the query by scope. Scopes are defined by (ScopeEntity + EntityId).
     * Customers that belongs to at least one scope will be returned.
     *
     * Example scope:
     * <code>
     * [
     *     'ScopeEntity' => ScopeEntity::ENTITY_CODE_STORE,
     *     'EntityId' => $store->getId(),
     * ]
     * </code>
     *
     * @param ModelCriteria $query The query to filter.
     * @param array $scopes An array of scopes.
     * @return ModelCriteria $this The query.
     */
    public static function addScopesFilter(ModelCriteria $query, array $scopes)
    {
        if (!is_array($scopes) || empty($scopes)) {
            return $query;
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

        // build the conditions for each scope
        $scopeConditionsNames = [];
        foreach ($scopes as $scope) {
            if (!isset($scope['ScopeEntity']) || !isset($scope['EntityId'])) {
                continue;
            }

            $scopeEntity = $scope['ScopeEntity'];
            $entityId = $scope['EntityId'];

            $conditionName = 'condition_customer_scope_' . $scopeEntity . '_' . $entityId;

            $query
                ->condition(
                    'condition_scope_entity',
                    ScopeTableMap::ENTITY . Criteria::EQUAL . "?",
                    $scopeEntity,
                    \PDO::PARAM_STR
                )
                ->condition(
                    'condition_entity_id',
                    CustomerScopeTableMap::ENTITY_ID . Criteria::EQUAL . "?",
                    $entityId,
                    \PDO::PARAM_INT
                )
                ->combine(
                    ['condition_scope_entity', 'condition_entity_id'],
                    Criteria::LOGICAL_AND,
                    $conditionName
                );

            $scopeConditionsNames[] = $conditionName;
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
     * @return CustomerQuery
     */
    public function filterByScopes(array $scopes)
    {
        return self::addScopesFilter($this, $scopes);
    }

    /**
     * @see \CustomerScope\Model\CustomerQuery::addScopesFilter()
     *
     * @param ModelCriteria $query
     * @param array $scope
     * @return ModelCriteria
     */
    public static function addScopeFilter(ModelCriteria $query, array $scope)
    {
        return self::addScopesFilter($query, [$scope]);
    }

    /**
     * @see \CustomerScope\Model\CustomerQuery::addScopesFilter()
     *
     * @param array $scope The scope.
     * @return CustomerQuery
     */
    public function filterByScope(array $scope)
    {
        return self::addScopeFilter($this, $scope);
    }
}
