<?php

namespace CustomerScope\Model\Base;

use \Exception;
use \PDO;
use CustomerScope\Model\Scope as ChildScope;
use CustomerScope\Model\ScopeI18nQuery as ChildScopeI18nQuery;
use CustomerScope\Model\ScopeQuery as ChildScopeQuery;
use CustomerScope\Model\Map\ScopeTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'scope' table.
 *
 *
 *
 * @method     ChildScopeQuery orderByScopeGroupId($order = Criteria::ASC) Order by the scope_group_id column
 * @method     ChildScopeQuery orderByEntity($order = Criteria::ASC) Order by the entity column
 * @method     ChildScopeQuery orderByEntityClass($order = Criteria::ASC) Order by the entity_class column
 * @method     ChildScopeQuery orderByPosition($order = Criteria::ASC) Order by the position column
 * @method     ChildScopeQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildScopeQuery orderByCreatedAt($order = Criteria::ASC) Order by the created_at column
 * @method     ChildScopeQuery orderByUpdatedAt($order = Criteria::ASC) Order by the updated_at column
 *
 * @method     ChildScopeQuery groupByScopeGroupId() Group by the scope_group_id column
 * @method     ChildScopeQuery groupByEntity() Group by the entity column
 * @method     ChildScopeQuery groupByEntityClass() Group by the entity_class column
 * @method     ChildScopeQuery groupByPosition() Group by the position column
 * @method     ChildScopeQuery groupById() Group by the id column
 * @method     ChildScopeQuery groupByCreatedAt() Group by the created_at column
 * @method     ChildScopeQuery groupByUpdatedAt() Group by the updated_at column
 *
 * @method     ChildScopeQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildScopeQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildScopeQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildScopeQuery leftJoinScopeGroup($relationAlias = null) Adds a LEFT JOIN clause to the query using the ScopeGroup relation
 * @method     ChildScopeQuery rightJoinScopeGroup($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ScopeGroup relation
 * @method     ChildScopeQuery innerJoinScopeGroup($relationAlias = null) Adds a INNER JOIN clause to the query using the ScopeGroup relation
 *
 * @method     ChildScopeQuery leftJoinCustomerScope($relationAlias = null) Adds a LEFT JOIN clause to the query using the CustomerScope relation
 * @method     ChildScopeQuery rightJoinCustomerScope($relationAlias = null) Adds a RIGHT JOIN clause to the query using the CustomerScope relation
 * @method     ChildScopeQuery innerJoinCustomerScope($relationAlias = null) Adds a INNER JOIN clause to the query using the CustomerScope relation
 *
 * @method     ChildScopeQuery leftJoinScopeI18n($relationAlias = null) Adds a LEFT JOIN clause to the query using the ScopeI18n relation
 * @method     ChildScopeQuery rightJoinScopeI18n($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ScopeI18n relation
 * @method     ChildScopeQuery innerJoinScopeI18n($relationAlias = null) Adds a INNER JOIN clause to the query using the ScopeI18n relation
 *
 * @method     ChildScope findOne(ConnectionInterface $con = null) Return the first ChildScope matching the query
 * @method     ChildScope findOneOrCreate(ConnectionInterface $con = null) Return the first ChildScope matching the query, or a new ChildScope object populated from the query conditions when no match is found
 *
 * @method     ChildScope findOneByScopeGroupId(int $scope_group_id) Return the first ChildScope filtered by the scope_group_id column
 * @method     ChildScope findOneByEntity(string $entity) Return the first ChildScope filtered by the entity column
 * @method     ChildScope findOneByEntityClass(string $entity_class) Return the first ChildScope filtered by the entity_class column
 * @method     ChildScope findOneByPosition(int $position) Return the first ChildScope filtered by the position column
 * @method     ChildScope findOneById(int $id) Return the first ChildScope filtered by the id column
 * @method     ChildScope findOneByCreatedAt(string $created_at) Return the first ChildScope filtered by the created_at column
 * @method     ChildScope findOneByUpdatedAt(string $updated_at) Return the first ChildScope filtered by the updated_at column
 *
 * @method     array findByScopeGroupId(int $scope_group_id) Return ChildScope objects filtered by the scope_group_id column
 * @method     array findByEntity(string $entity) Return ChildScope objects filtered by the entity column
 * @method     array findByEntityClass(string $entity_class) Return ChildScope objects filtered by the entity_class column
 * @method     array findByPosition(int $position) Return ChildScope objects filtered by the position column
 * @method     array findById(int $id) Return ChildScope objects filtered by the id column
 * @method     array findByCreatedAt(string $created_at) Return ChildScope objects filtered by the created_at column
 * @method     array findByUpdatedAt(string $updated_at) Return ChildScope objects filtered by the updated_at column
 *
 */
abstract class ScopeQuery extends ModelCriteria
{

    /**
     * Initializes internal state of \CustomerScope\Model\Base\ScopeQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'thelia', $modelName = '\\CustomerScope\\Model\\Scope', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildScopeQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildScopeQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof \CustomerScope\Model\ScopeQuery) {
            return $criteria;
        }
        $query = new \CustomerScope\Model\ScopeQuery();
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
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildScope|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = ScopeTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ScopeTableMap::DATABASE_NAME);
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
     * @return   ChildScope A model object, or null if the key is not found
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT SCOPE_GROUP_ID, ENTITY, ENTITY_CLASS, POSITION, ID, CREATED_AT, UPDATED_AT FROM scope WHERE ID = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $obj = new ChildScope();
            $obj->hydrate($row);
            ScopeTableMap::addInstanceToPool($obj, (string) $key);
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
     * @return ChildScope|array|mixed the result, formatted by the current formatter
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
     * $objs = $c->findPks(array(12, 56, 832), $con);
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
     * @return ChildScopeQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(ScopeTableMap::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ChildScopeQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(ScopeTableMap::ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the scope_group_id column
     *
     * Example usage:
     * <code>
     * $query->filterByScopeGroupId(1234); // WHERE scope_group_id = 1234
     * $query->filterByScopeGroupId(array(12, 34)); // WHERE scope_group_id IN (12, 34)
     * $query->filterByScopeGroupId(array('min' => 12)); // WHERE scope_group_id > 12
     * </code>
     *
     * @see       filterByScopeGroup()
     *
     * @param     mixed $scopeGroupId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildScopeQuery The current query, for fluid interface
     */
    public function filterByScopeGroupId($scopeGroupId = null, $comparison = null)
    {
        if (is_array($scopeGroupId)) {
            $useMinMax = false;
            if (isset($scopeGroupId['min'])) {
                $this->addUsingAlias(ScopeTableMap::SCOPE_GROUP_ID, $scopeGroupId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($scopeGroupId['max'])) {
                $this->addUsingAlias(ScopeTableMap::SCOPE_GROUP_ID, $scopeGroupId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ScopeTableMap::SCOPE_GROUP_ID, $scopeGroupId, $comparison);
    }

    /**
     * Filter the query on the entity column
     *
     * Example usage:
     * <code>
     * $query->filterByEntity('fooValue');   // WHERE entity = 'fooValue'
     * $query->filterByEntity('%fooValue%'); // WHERE entity LIKE '%fooValue%'
     * </code>
     *
     * @param     string $entity The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildScopeQuery The current query, for fluid interface
     */
    public function filterByEntity($entity = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($entity)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $entity)) {
                $entity = str_replace('*', '%', $entity);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(ScopeTableMap::ENTITY, $entity, $comparison);
    }

    /**
     * Filter the query on the entity_class column
     *
     * Example usage:
     * <code>
     * $query->filterByEntityClass('fooValue');   // WHERE entity_class = 'fooValue'
     * $query->filterByEntityClass('%fooValue%'); // WHERE entity_class LIKE '%fooValue%'
     * </code>
     *
     * @param     string $entityClass The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildScopeQuery The current query, for fluid interface
     */
    public function filterByEntityClass($entityClass = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($entityClass)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $entityClass)) {
                $entityClass = str_replace('*', '%', $entityClass);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(ScopeTableMap::ENTITY_CLASS, $entityClass, $comparison);
    }

    /**
     * Filter the query on the position column
     *
     * Example usage:
     * <code>
     * $query->filterByPosition(1234); // WHERE position = 1234
     * $query->filterByPosition(array(12, 34)); // WHERE position IN (12, 34)
     * $query->filterByPosition(array('min' => 12)); // WHERE position > 12
     * </code>
     *
     * @param     mixed $position The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildScopeQuery The current query, for fluid interface
     */
    public function filterByPosition($position = null, $comparison = null)
    {
        if (is_array($position)) {
            $useMinMax = false;
            if (isset($position['min'])) {
                $this->addUsingAlias(ScopeTableMap::POSITION, $position['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($position['max'])) {
                $this->addUsingAlias(ScopeTableMap::POSITION, $position['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ScopeTableMap::POSITION, $position, $comparison);
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id > 12
     * </code>
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildScopeQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(ScopeTableMap::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(ScopeTableMap::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ScopeTableMap::ID, $id, $comparison);
    }

    /**
     * Filter the query on the created_at column
     *
     * Example usage:
     * <code>
     * $query->filterByCreatedAt('2011-03-14'); // WHERE created_at = '2011-03-14'
     * $query->filterByCreatedAt('now'); // WHERE created_at = '2011-03-14'
     * $query->filterByCreatedAt(array('max' => 'yesterday')); // WHERE created_at > '2011-03-13'
     * </code>
     *
     * @param     mixed $createdAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildScopeQuery The current query, for fluid interface
     */
    public function filterByCreatedAt($createdAt = null, $comparison = null)
    {
        if (is_array($createdAt)) {
            $useMinMax = false;
            if (isset($createdAt['min'])) {
                $this->addUsingAlias(ScopeTableMap::CREATED_AT, $createdAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($createdAt['max'])) {
                $this->addUsingAlias(ScopeTableMap::CREATED_AT, $createdAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ScopeTableMap::CREATED_AT, $createdAt, $comparison);
    }

    /**
     * Filter the query on the updated_at column
     *
     * Example usage:
     * <code>
     * $query->filterByUpdatedAt('2011-03-14'); // WHERE updated_at = '2011-03-14'
     * $query->filterByUpdatedAt('now'); // WHERE updated_at = '2011-03-14'
     * $query->filterByUpdatedAt(array('max' => 'yesterday')); // WHERE updated_at > '2011-03-13'
     * </code>
     *
     * @param     mixed $updatedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildScopeQuery The current query, for fluid interface
     */
    public function filterByUpdatedAt($updatedAt = null, $comparison = null)
    {
        if (is_array($updatedAt)) {
            $useMinMax = false;
            if (isset($updatedAt['min'])) {
                $this->addUsingAlias(ScopeTableMap::UPDATED_AT, $updatedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($updatedAt['max'])) {
                $this->addUsingAlias(ScopeTableMap::UPDATED_AT, $updatedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ScopeTableMap::UPDATED_AT, $updatedAt, $comparison);
    }

    /**
     * Filter the query by a related \CustomerScope\Model\ScopeGroup object
     *
     * @param \CustomerScope\Model\ScopeGroup|ObjectCollection $scopeGroup The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildScopeQuery The current query, for fluid interface
     */
    public function filterByScopeGroup($scopeGroup, $comparison = null)
    {
        if ($scopeGroup instanceof \CustomerScope\Model\ScopeGroup) {
            return $this
                ->addUsingAlias(ScopeTableMap::SCOPE_GROUP_ID, $scopeGroup->getId(), $comparison);
        } elseif ($scopeGroup instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(ScopeTableMap::SCOPE_GROUP_ID, $scopeGroup->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByScopeGroup() only accepts arguments of type \CustomerScope\Model\ScopeGroup or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ScopeGroup relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildScopeQuery The current query, for fluid interface
     */
    public function joinScopeGroup($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ScopeGroup');

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
            $this->addJoinObject($join, 'ScopeGroup');
        }

        return $this;
    }

    /**
     * Use the ScopeGroup relation ScopeGroup object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \CustomerScope\Model\ScopeGroupQuery A secondary query class using the current class as primary query
     */
    public function useScopeGroupQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinScopeGroup($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ScopeGroup', '\CustomerScope\Model\ScopeGroupQuery');
    }

    /**
     * Filter the query by a related \CustomerScope\Model\CustomerScope object
     *
     * @param \CustomerScope\Model\CustomerScope|ObjectCollection $customerScope  the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildScopeQuery The current query, for fluid interface
     */
    public function filterByCustomerScope($customerScope, $comparison = null)
    {
        if ($customerScope instanceof \CustomerScope\Model\CustomerScope) {
            return $this
                ->addUsingAlias(ScopeTableMap::ID, $customerScope->getScopeId(), $comparison);
        } elseif ($customerScope instanceof ObjectCollection) {
            return $this
                ->useCustomerScopeQuery()
                ->filterByPrimaryKeys($customerScope->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByCustomerScope() only accepts arguments of type \CustomerScope\Model\CustomerScope or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the CustomerScope relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildScopeQuery The current query, for fluid interface
     */
    public function joinCustomerScope($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('CustomerScope');

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
            $this->addJoinObject($join, 'CustomerScope');
        }

        return $this;
    }

    /**
     * Use the CustomerScope relation CustomerScope object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \CustomerScope\Model\CustomerScopeQuery A secondary query class using the current class as primary query
     */
    public function useCustomerScopeQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinCustomerScope($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'CustomerScope', '\CustomerScope\Model\CustomerScopeQuery');
    }

    /**
     * Filter the query by a related \CustomerScope\Model\ScopeI18n object
     *
     * @param \CustomerScope\Model\ScopeI18n|ObjectCollection $scopeI18n  the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildScopeQuery The current query, for fluid interface
     */
    public function filterByScopeI18n($scopeI18n, $comparison = null)
    {
        if ($scopeI18n instanceof \CustomerScope\Model\ScopeI18n) {
            return $this
                ->addUsingAlias(ScopeTableMap::ID, $scopeI18n->getId(), $comparison);
        } elseif ($scopeI18n instanceof ObjectCollection) {
            return $this
                ->useScopeI18nQuery()
                ->filterByPrimaryKeys($scopeI18n->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByScopeI18n() only accepts arguments of type \CustomerScope\Model\ScopeI18n or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ScopeI18n relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildScopeQuery The current query, for fluid interface
     */
    public function joinScopeI18n($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ScopeI18n');

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
            $this->addJoinObject($join, 'ScopeI18n');
        }

        return $this;
    }

    /**
     * Use the ScopeI18n relation ScopeI18n object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \CustomerScope\Model\ScopeI18nQuery A secondary query class using the current class as primary query
     */
    public function useScopeI18nQuery($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        return $this
            ->joinScopeI18n($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ScopeI18n', '\CustomerScope\Model\ScopeI18nQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildScope $scope Object to remove from the list of results
     *
     * @return ChildScopeQuery The current query, for fluid interface
     */
    public function prune($scope = null)
    {
        if ($scope) {
            $this->addUsingAlias(ScopeTableMap::ID, $scope->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the scope table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ScopeTableMap::DATABASE_NAME);
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
            ScopeTableMap::clearInstancePool();
            ScopeTableMap::clearRelatedInstancePool();

            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $affectedRows;
    }

    /**
     * Performs a DELETE on the database, given a ChildScope or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or ChildScope object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(ScopeTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(ScopeTableMap::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();


        ScopeTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            ScopeTableMap::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }
    }

    // timestampable behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     ChildScopeQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7)
    {
        return $this->addUsingAlias(ScopeTableMap::UPDATED_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     ChildScopeQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7)
    {
        return $this->addUsingAlias(ScopeTableMap::CREATED_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     ChildScopeQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst()
    {
        return $this->addDescendingOrderByColumn(ScopeTableMap::UPDATED_AT);
    }

    /**
     * Order by update date asc
     *
     * @return     ChildScopeQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst()
    {
        return $this->addAscendingOrderByColumn(ScopeTableMap::UPDATED_AT);
    }

    /**
     * Order by create date desc
     *
     * @return     ChildScopeQuery The current query, for fluid interface
     */
    public function lastCreatedFirst()
    {
        return $this->addDescendingOrderByColumn(ScopeTableMap::CREATED_AT);
    }

    /**
     * Order by create date asc
     *
     * @return     ChildScopeQuery The current query, for fluid interface
     */
    public function firstCreatedFirst()
    {
        return $this->addAscendingOrderByColumn(ScopeTableMap::CREATED_AT);
    }

    // i18n behavior

    /**
     * Adds a JOIN clause to the query using the i18n relation
     *
     * @param     string $locale Locale to use for the join condition, e.g. 'fr_FR'
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'. Defaults to left join.
     *
     * @return    ChildScopeQuery The current query, for fluid interface
     */
    public function joinI18n($locale = 'en_US', $relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $relationName = $relationAlias ? $relationAlias : 'ScopeI18n';

        return $this
            ->joinScopeI18n($relationAlias, $joinType)
            ->addJoinCondition($relationName, $relationName . '.Locale = ?', $locale);
    }

    /**
     * Adds a JOIN clause to the query and hydrates the related I18n object.
     * Shortcut for $c->joinI18n($locale)->with()
     *
     * @param     string $locale Locale to use for the join condition, e.g. 'fr_FR'
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'. Defaults to left join.
     *
     * @return    ChildScopeQuery The current query, for fluid interface
     */
    public function joinWithI18n($locale = 'en_US', $joinType = Criteria::LEFT_JOIN)
    {
        $this
            ->joinI18n($locale, null, $joinType)
            ->with('ScopeI18n');
        $this->with['ScopeI18n']->setIsWithOneToMany(false);

        return $this;
    }

    /**
     * Use the I18n relation query object
     *
     * @see       useQuery()
     *
     * @param     string $locale Locale to use for the join condition, e.g. 'fr_FR'
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'. Defaults to left join.
     *
     * @return    ChildScopeI18nQuery A secondary query class using the current class as primary query
     */
    public function useI18nQuery($locale = 'en_US', $relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinI18n($locale, $relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ScopeI18n', '\CustomerScope\Model\ScopeI18nQuery');
    }

    // sortable behavior

    /**
     * Returns the objects in a certain list, from the list scope
     *
     * @param int $scope Scope to determine which objects node to return
     *
     * @return    ChildScopeQuery The current query, for fluid interface
     */
    public function inList($scope)
    {

        static::sortableApplyScopeCriteria($this, $scope, 'addUsingAlias');

        return $this;
    }

    /**
     * Filter the query based on a rank in the list
     *
     * @param     integer   $rank rank
     * @param int $scope Scope to determine which objects node to return

     *
     * @return    ChildScopeQuery The current query, for fluid interface
     */
    public function filterByRank($rank, $scope)
    {

        return $this
            ->inList($scope)
            ->addUsingAlias(ScopeTableMap::RANK_COL, $rank, Criteria::EQUAL);
    }

    /**
     * Order the query based on the rank in the list.
     * Using the default $order, returns the item with the lowest rank first
     *
     * @param     string $order either Criteria::ASC (default) or Criteria::DESC
     *
     * @return    ChildScopeQuery The current query, for fluid interface
     */
    public function orderByRank($order = Criteria::ASC)
    {
        $order = strtoupper($order);
        switch ($order) {
            case Criteria::ASC:
                return $this->addAscendingOrderByColumn($this->getAliasedColName(ScopeTableMap::RANK_COL));
                break;
            case Criteria::DESC:
                return $this->addDescendingOrderByColumn($this->getAliasedColName(ScopeTableMap::RANK_COL));
                break;
            default:
                throw new \Propel\Runtime\Exception\PropelException('ChildScopeQuery::orderBy() only accepts "asc" or "desc" as argument');
        }
    }

    /**
     * Get an item from the list based on its rank
     *
     * @param     integer   $rank rank
     * @param int $scope Scope to determine which objects node to return
     * @param     ConnectionInterface $con optional connection
     *
     * @return    ChildScope
     */
    public function findOneByRank($rank, $scope, ConnectionInterface $con = null)
    {

        return $this
            ->filterByRank($rank, $scope)
            ->findOne($con);
    }

    /**
     * Returns a list of objects
     *
     * @param int $scope Scope to determine which objects node to return

     * @param      ConnectionInterface $con    Connection to use.
     *
     * @return     mixed the list of results, formatted by the current formatter
     */
    public function findList($scope, $con = null)
    {

        return $this
            ->inList($scope)
            ->orderByRank()
            ->find($con);
    }

    /**
     * Get the highest rank
     *
     * @param int $scope Scope to determine which objects node to return
     * @param     ConnectionInterface optional connection
     *
     * @return    integer highest position
     */
    public function getMaxRank($scope, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection(ScopeTableMap::DATABASE_NAME);
        }
        // shift the objects with a position lower than the one of object
        $this->addSelectColumn('MAX(' . ScopeTableMap::RANK_COL . ')');

                static::sortableApplyScopeCriteria($this, $scope);
        $stmt = $this->doSelect($con);

        return $stmt->fetchColumn();
    }

    /**
     * Get the highest rank by a scope with a array format.
     *
     * @param     mixed $scope      The scope value as scalar type or array($value1, ...).

     * @param     ConnectionInterface optional connection
     *
     * @return    integer highest position
     */
    public function getMaxRankArray($scope, ConnectionInterface $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(ScopeTableMap::DATABASE_NAME);
        }
        // shift the objects with a position lower than the one of object
        $this->addSelectColumn('MAX(' . ScopeTableMap::RANK_COL . ')');
        static::sortableApplyScopeCriteria($this, $scope);
        $stmt = $this->doSelect($con);

        return $stmt->fetchColumn();
    }

    /**
     * Get an item from the list based on its rank
     *
     * @param     integer   $rank rank
     * @param      int $scope        Scope to determine which suite to consider
     * @param     ConnectionInterface $con optional connection
     *
     * @return ChildScope
     */
    static public function retrieveByRank($rank, $scope = null, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection(ScopeTableMap::DATABASE_NAME);
        }

        $c = new Criteria;
        $c->add(ScopeTableMap::RANK_COL, $rank);
                static::sortableApplyScopeCriteria($c, $scope);

        return static::create(null, $c)->findOne($con);
    }

    /**
     * Reorder a set of sortable objects based on a list of id/position
     * Beware that there is no check made on the positions passed
     * So incoherent positions will result in an incoherent list
     *
     * @param     mixed               $order id => rank pairs
     * @param     ConnectionInterface $con   optional connection
     *
     * @return    boolean true if the reordering took place, false if a database problem prevented it
     */
    public function reorder($order, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection(ScopeTableMap::DATABASE_NAME);
        }

        $con->beginTransaction();
        try {
            $ids = array_keys($order);
            $objects = $this->findPks($ids, $con);
            foreach ($objects as $object) {
                $pk = $object->getPrimaryKey();
                if ($object->getPosition() != $order[$pk]) {
                    $object->setPosition($order[$pk]);
                    $object->save($con);
                }
            }
            $con->commit();

            return true;
        } catch (\Propel\Runtime\Exception\PropelException $e) {
            $con->rollback();
            throw $e;
        }
    }

    /**
     * Return an array of sortable objects ordered by position
     *
     * @param     Criteria  $criteria  optional criteria object
     * @param     string    $order     sorting order, to be chosen between Criteria::ASC (default) and Criteria::DESC
     * @param     ConnectionInterface $con       optional connection
     *
     * @return    array list of sortable objects
     */
    static public function doSelectOrderByRank(Criteria $criteria = null, $order = Criteria::ASC, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection(ScopeTableMap::DATABASE_NAME);
        }

        if (null === $criteria) {
            $criteria = new Criteria();
        } elseif ($criteria instanceof Criteria) {
            $criteria = clone $criteria;
        }

        $criteria->clearOrderByColumns();

        if (Criteria::ASC == $order) {
            $criteria->addAscendingOrderByColumn(ScopeTableMap::RANK_COL);
        } else {
            $criteria->addDescendingOrderByColumn(ScopeTableMap::RANK_COL);
        }

        return ChildScopeQuery::create(null, $criteria)->find($con);
    }

    /**
     * Return an array of sortable objects in the given scope ordered by position
     *
     * @param     int       $scope  the scope of the list
     * @param     string    $order  sorting order, to be chosen between Criteria::ASC (default) and Criteria::DESC
     * @param     ConnectionInterface $con    optional connection
     *
     * @return    array list of sortable objects
     */
    static public function retrieveList($scope, $order = Criteria::ASC, ConnectionInterface $con = null)
    {
        $c = new Criteria();
        static::sortableApplyScopeCriteria($c, $scope);

        return ChildScopeQuery::doSelectOrderByRank($c, $order, $con);
    }

    /**
     * Return the number of sortable objects in the given scope
     *
     * @param     int       $scope  the scope of the list
     * @param     ConnectionInterface $con    optional connection
     *
     * @return    array list of sortable objects
     */
    static public function countList($scope, ConnectionInterface $con = null)
    {
        $c = new Criteria();
        $c->add(ScopeTableMap::SCOPE_COL, $scope);

        return ChildScopeQuery::create(null, $c)->count($con);
    }

    /**
     * Deletes the sortable objects in the given scope
     *
     * @param     int       $scope  the scope of the list
     * @param     ConnectionInterface $con    optional connection
     *
     * @return    int number of deleted objects
     */
    static public function deleteList($scope, ConnectionInterface $con = null)
    {
        $c = new Criteria();
        static::sortableApplyScopeCriteria($c, $scope);

        return ScopeTableMap::doDelete($c, $con);
    }

    /**
     * Applies all scope fields to the given criteria.
     *
     * @param  Criteria $criteria Applies the values directly to this criteria.
     * @param  mixed    $scope    The scope value as scalar type or array($value1, ...).
     * @param  string   $method   The method we use to apply the values.
     *
     */
    static public function sortableApplyScopeCriteria(Criteria $criteria, $scope, $method = 'add')
    {

        $criteria->$method(ScopeTableMap::SCOPE_GROUP_ID, $scope, Criteria::EQUAL);

    }

    /**
     * Adds $delta to all Rank values that are >= $first and <= $last.
     * '$delta' can also be negative.
     *
     * @param      int $delta Value to be shifted by, can be negative
     * @param      int $first First node to be shifted
     * @param      int $last  Last node to be shifted
     * @param      int $scope Scope to use for the shift
     * @param      ConnectionInterface $con Connection to use.
     */
    static public function sortableShiftRank($delta, $first, $last = null, $scope = null, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ScopeTableMap::DATABASE_NAME);
        }

        $whereCriteria = new Criteria(ScopeTableMap::DATABASE_NAME);
        $criterion = $whereCriteria->getNewCriterion(ScopeTableMap::RANK_COL, $first, Criteria::GREATER_EQUAL);
        if (null !== $last) {
            $criterion->addAnd($whereCriteria->getNewCriterion(ScopeTableMap::RANK_COL, $last, Criteria::LESS_EQUAL));
        }
        $whereCriteria->add($criterion);
                static::sortableApplyScopeCriteria($whereCriteria, $scope);

        $valuesCriteria = new Criteria(ScopeTableMap::DATABASE_NAME);
        $valuesCriteria->add(ScopeTableMap::RANK_COL, array('raw' => ScopeTableMap::RANK_COL . ' + ?', 'value' => $delta), Criteria::CUSTOM_EQUAL);

        $whereCriteria->doUpdate($valuesCriteria, $con);
        ScopeTableMap::clearInstancePool();
    }

} // ScopeQuery
