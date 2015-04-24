<?php

namespace CustomerScope\Model\Base;

use \Exception;
use \PDO;
use CustomerScope\Model\CustomerScope as ChildCustomerScope;
use CustomerScope\Model\CustomerScopeQuery as ChildCustomerScopeQuery;
use CustomerScope\Model\Map\CustomerScopeTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Thelia\Model\Customer;

/**
 * Base class that represents a query for the 'customer_scope' table.
 *
 *
 *
 * @method     ChildCustomerScopeQuery orderByCustomerId($order = Criteria::ASC) Order by the customer_id column
 * @method     ChildCustomerScopeQuery orderByScopeId($order = Criteria::ASC) Order by the scope_id column
 * @method     ChildCustomerScopeQuery orderByEntityId($order = Criteria::ASC) Order by the entity_id column
 * @method     ChildCustomerScopeQuery orderByScopeEntity($order = Criteria::ASC) Order by the scope_entity column
 *
 * @method     ChildCustomerScopeQuery groupByCustomerId() Group by the customer_id column
 * @method     ChildCustomerScopeQuery groupByScopeId() Group by the scope_id column
 * @method     ChildCustomerScopeQuery groupByEntityId() Group by the entity_id column
 * @method     ChildCustomerScopeQuery groupByScopeEntity() Group by the scope_entity column
 *
 * @method     ChildCustomerScopeQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildCustomerScopeQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildCustomerScopeQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildCustomerScopeQuery leftJoinCustomer($relationAlias = null) Adds a LEFT JOIN clause to the query using the Customer relation
 * @method     ChildCustomerScopeQuery rightJoinCustomer($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Customer relation
 * @method     ChildCustomerScopeQuery innerJoinCustomer($relationAlias = null) Adds a INNER JOIN clause to the query using the Customer relation
 *
 * @method     ChildCustomerScopeQuery leftJoinScope($relationAlias = null) Adds a LEFT JOIN clause to the query using the Scope relation
 * @method     ChildCustomerScopeQuery rightJoinScope($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Scope relation
 * @method     ChildCustomerScopeQuery innerJoinScope($relationAlias = null) Adds a INNER JOIN clause to the query using the Scope relation
 *
 * @method     ChildCustomerScope findOne(ConnectionInterface $con = null) Return the first ChildCustomerScope matching the query
 * @method     ChildCustomerScope findOneOrCreate(ConnectionInterface $con = null) Return the first ChildCustomerScope matching the query, or a new ChildCustomerScope object populated from the query conditions when no match is found
 *
 * @method     ChildCustomerScope findOneByCustomerId(int $customer_id) Return the first ChildCustomerScope filtered by the customer_id column
 * @method     ChildCustomerScope findOneByScopeId(int $scope_id) Return the first ChildCustomerScope filtered by the scope_id column
 * @method     ChildCustomerScope findOneByEntityId(int $entity_id) Return the first ChildCustomerScope filtered by the entity_id column
 * @method     ChildCustomerScope findOneByScopeEntity(string $scope_entity) Return the first ChildCustomerScope filtered by the scope_entity column
 *
 * @method     array findByCustomerId(int $customer_id) Return ChildCustomerScope objects filtered by the customer_id column
 * @method     array findByScopeId(int $scope_id) Return ChildCustomerScope objects filtered by the scope_id column
 * @method     array findByEntityId(int $entity_id) Return ChildCustomerScope objects filtered by the entity_id column
 * @method     array findByScopeEntity(string $scope_entity) Return ChildCustomerScope objects filtered by the scope_entity column
 *
 */
abstract class CustomerScopeQuery extends ModelCriteria
{

    /**
     * Initializes internal state of \CustomerScope\Model\Base\CustomerScopeQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'thelia', $modelName = '\\CustomerScope\\Model\\CustomerScope', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildCustomerScopeQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildCustomerScopeQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof \CustomerScope\Model\CustomerScopeQuery) {
            return $criteria;
        }
        $query = new \CustomerScope\Model\CustomerScopeQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj = $c->findPk(array(12, 34, 56), $con);
     * </code>
     *
     * @param array[$customer_id, $scope_id, $entity_id] $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildCustomerScope|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = CustomerScopeTableMap::getInstanceFromPool(serialize(array((string) $key[0], (string) $key[1], (string) $key[2]))))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(CustomerScopeTableMap::DATABASE_NAME);
        }
        $this->basePreSelect($con);
        if ($this->formatter || $this->modelAlias || $this->with || $this->select
         || $this->selectColumns || $this->asColumns || $this->selectModifiers
         || $this->map || $this->having || $this->joins) {
            return $this->findPkComplex($key, $con);
        } else {
            return $this->findPkSimple($key, $con);
        }
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return   ChildCustomerScope A model object, or null if the key is not found
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT CUSTOMER_ID, SCOPE_ID, ENTITY_ID, SCOPE_ENTITY FROM customer_scope WHERE CUSTOMER_ID = :p0 AND SCOPE_ID = :p1 AND ENTITY_ID = :p2';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key[0], PDO::PARAM_INT);
            $stmt->bindValue(':p1', $key[1], PDO::PARAM_INT);
            $stmt->bindValue(':p2', $key[2], PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $obj = new ChildCustomerScope();
            $obj->hydrate($row);
            CustomerScopeTableMap::addInstanceToPool($obj, serialize(array((string) $key[0], (string) $key[1], (string) $key[2])));
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return ChildCustomerScope|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($dataFetcher);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(array(12, 56), array(832, 123), array(123, 456)), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ObjectCollection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection($this->getDbName());
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($dataFetcher);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return ChildCustomerScopeQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {
        $this->addUsingAlias(CustomerScopeTableMap::CUSTOMER_ID, $key[0], Criteria::EQUAL);
        $this->addUsingAlias(CustomerScopeTableMap::SCOPE_ID, $key[1], Criteria::EQUAL);
        $this->addUsingAlias(CustomerScopeTableMap::ENTITY_ID, $key[2], Criteria::EQUAL);

        return $this;
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ChildCustomerScopeQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {
        if (empty($keys)) {
            return $this->add(null, '1<>1', Criteria::CUSTOM);
        }
        foreach ($keys as $key) {
            $cton0 = $this->getNewCriterion(CustomerScopeTableMap::CUSTOMER_ID, $key[0], Criteria::EQUAL);
            $cton1 = $this->getNewCriterion(CustomerScopeTableMap::SCOPE_ID, $key[1], Criteria::EQUAL);
            $cton0->addAnd($cton1);
            $cton2 = $this->getNewCriterion(CustomerScopeTableMap::ENTITY_ID, $key[2], Criteria::EQUAL);
            $cton0->addAnd($cton2);
            $this->addOr($cton0);
        }

        return $this;
    }

    /**
     * Filter the query on the customer_id column
     *
     * Example usage:
     * <code>
     * $query->filterByCustomerId(1234); // WHERE customer_id = 1234
     * $query->filterByCustomerId(array(12, 34)); // WHERE customer_id IN (12, 34)
     * $query->filterByCustomerId(array('min' => 12)); // WHERE customer_id > 12
     * </code>
     *
     * @see       filterByCustomer()
     *
     * @param     mixed $customerId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildCustomerScopeQuery The current query, for fluid interface
     */
    public function filterByCustomerId($customerId = null, $comparison = null)
    {
        if (is_array($customerId)) {
            $useMinMax = false;
            if (isset($customerId['min'])) {
                $this->addUsingAlias(CustomerScopeTableMap::CUSTOMER_ID, $customerId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($customerId['max'])) {
                $this->addUsingAlias(CustomerScopeTableMap::CUSTOMER_ID, $customerId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(CustomerScopeTableMap::CUSTOMER_ID, $customerId, $comparison);
    }

    /**
     * Filter the query on the scope_id column
     *
     * Example usage:
     * <code>
     * $query->filterByScopeId(1234); // WHERE scope_id = 1234
     * $query->filterByScopeId(array(12, 34)); // WHERE scope_id IN (12, 34)
     * $query->filterByScopeId(array('min' => 12)); // WHERE scope_id > 12
     * </code>
     *
     * @see       filterByScope()
     *
     * @param     mixed $scopeId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildCustomerScopeQuery The current query, for fluid interface
     */
    public function filterByScopeId($scopeId = null, $comparison = null)
    {
        if (is_array($scopeId)) {
            $useMinMax = false;
            if (isset($scopeId['min'])) {
                $this->addUsingAlias(CustomerScopeTableMap::SCOPE_ID, $scopeId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($scopeId['max'])) {
                $this->addUsingAlias(CustomerScopeTableMap::SCOPE_ID, $scopeId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(CustomerScopeTableMap::SCOPE_ID, $scopeId, $comparison);
    }

    /**
     * Filter the query on the entity_id column
     *
     * Example usage:
     * <code>
     * $query->filterByEntityId(1234); // WHERE entity_id = 1234
     * $query->filterByEntityId(array(12, 34)); // WHERE entity_id IN (12, 34)
     * $query->filterByEntityId(array('min' => 12)); // WHERE entity_id > 12
     * </code>
     *
     * @param     mixed $entityId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildCustomerScopeQuery The current query, for fluid interface
     */
    public function filterByEntityId($entityId = null, $comparison = null)
    {
        if (is_array($entityId)) {
            $useMinMax = false;
            if (isset($entityId['min'])) {
                $this->addUsingAlias(CustomerScopeTableMap::ENTITY_ID, $entityId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($entityId['max'])) {
                $this->addUsingAlias(CustomerScopeTableMap::ENTITY_ID, $entityId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(CustomerScopeTableMap::ENTITY_ID, $entityId, $comparison);
    }

    /**
     * Filter the query on the scope_entity column
     *
     * Example usage:
     * <code>
     * $query->filterByScopeEntity('fooValue');   // WHERE scope_entity = 'fooValue'
     * $query->filterByScopeEntity('%fooValue%'); // WHERE scope_entity LIKE '%fooValue%'
     * </code>
     *
     * @param     string $scopeEntity The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildCustomerScopeQuery The current query, for fluid interface
     */
    public function filterByScopeEntity($scopeEntity = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($scopeEntity)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $scopeEntity)) {
                $scopeEntity = str_replace('*', '%', $scopeEntity);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(CustomerScopeTableMap::SCOPE_ENTITY, $scopeEntity, $comparison);
    }

    /**
     * Filter the query by a related \Thelia\Model\Customer object
     *
     * @param \Thelia\Model\Customer|ObjectCollection $customer The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildCustomerScopeQuery The current query, for fluid interface
     */
    public function filterByCustomer($customer, $comparison = null)
    {
        if ($customer instanceof \Thelia\Model\Customer) {
            return $this
                ->addUsingAlias(CustomerScopeTableMap::CUSTOMER_ID, $customer->getId(), $comparison);
        } elseif ($customer instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(CustomerScopeTableMap::CUSTOMER_ID, $customer->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByCustomer() only accepts arguments of type \Thelia\Model\Customer or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Customer relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildCustomerScopeQuery The current query, for fluid interface
     */
    public function joinCustomer($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Customer');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'Customer');
        }

        return $this;
    }

    /**
     * Use the Customer relation Customer object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \Thelia\Model\CustomerQuery A secondary query class using the current class as primary query
     */
    public function useCustomerQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinCustomer($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Customer', '\Thelia\Model\CustomerQuery');
    }

    /**
     * Filter the query by a related \CustomerScope\Model\Scope object
     *
     * @param \CustomerScope\Model\Scope|ObjectCollection $scope The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildCustomerScopeQuery The current query, for fluid interface
     */
    public function filterByScope($scope, $comparison = null)
    {
        if ($scope instanceof \CustomerScope\Model\Scope) {
            return $this
                ->addUsingAlias(CustomerScopeTableMap::SCOPE_ID, $scope->getId(), $comparison);
        } elseif ($scope instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(CustomerScopeTableMap::SCOPE_ID, $scope->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByScope() only accepts arguments of type \CustomerScope\Model\Scope or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Scope relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildCustomerScopeQuery The current query, for fluid interface
     */
    public function joinScope($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Scope');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'Scope');
        }

        return $this;
    }

    /**
     * Use the Scope relation Scope object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \CustomerScope\Model\ScopeQuery A secondary query class using the current class as primary query
     */
    public function useScopeQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinScope($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Scope', '\CustomerScope\Model\ScopeQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildCustomerScope $customerScope Object to remove from the list of results
     *
     * @return ChildCustomerScopeQuery The current query, for fluid interface
     */
    public function prune($customerScope = null)
    {
        if ($customerScope) {
            $this->addCond('pruneCond0', $this->getAliasedColName(CustomerScopeTableMap::CUSTOMER_ID), $customerScope->getCustomerId(), Criteria::NOT_EQUAL);
            $this->addCond('pruneCond1', $this->getAliasedColName(CustomerScopeTableMap::SCOPE_ID), $customerScope->getScopeId(), Criteria::NOT_EQUAL);
            $this->addCond('pruneCond2', $this->getAliasedColName(CustomerScopeTableMap::ENTITY_ID), $customerScope->getEntityId(), Criteria::NOT_EQUAL);
            $this->combine(array('pruneCond0', 'pruneCond1', 'pruneCond2'), Criteria::LOGICAL_OR);
        }

        return $this;
    }

    /**
     * Deletes all rows from the customer_scope table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(CustomerScopeTableMap::DATABASE_NAME);
        }
        $affectedRows = 0; // initialize var to track total num of affected rows
        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            CustomerScopeTableMap::clearInstancePool();
            CustomerScopeTableMap::clearRelatedInstancePool();

            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $affectedRows;
    }

    /**
     * Performs a DELETE on the database, given a ChildCustomerScope or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or ChildCustomerScope object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
     public function delete(ConnectionInterface $con = null)
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(CustomerScopeTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(CustomerScopeTableMap::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();


        CustomerScopeTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            CustomerScopeTableMap::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }
    }

} // CustomerScopeQuery
