<?php
/**
 * Created by PhpStorm.
 * User: vincent
 * Date: 24/04/2015
 * Time: 12:01
 */

namespace CustomerScope\Model;

use CustomerScope\Model\Base\Scope as BaseScope;
use DoctrineORMModuleTest\Assets\Entity\City;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Map\ColumnMap;

class ScopeEntityHelper
{
    /**
     * Return all the direct childs entities of an entity
     *
     * @param $scopeEntity object entity
     * @return null or collection of childs entity
     */
    public function getChilds($scopeEntity)
    {
        $parentScope = $this->getScopeByEntity($scopeEntity);
        $childScope = ScopeQuery::create()
            ->findOneByDirectParent($parentScope);

        if (null === $childScope) {
            return null;
        }

        $childScopeEntityQuery = $this->createEntityQueryByScope($childScope);

        $fkName = $this->getParentEntityFk($childScopeEntityQuery, $parentScope);

        return $childScopeEntityQuery->findBy($fkName, $scopeEntity->getId());
    }

    /**
     * Search if scope have child
     * @param $scopeEntity
     * @return bool true if childs exist false else
     */
    public function hasChild($scopeEntity)
    {
        $parentScope = $this->getScopeByEntity($scopeEntity);
        $child = ScopeQuery::create()
            ->findOneByDirectParent($parentScope);

        if (null !== $child) {
            return true;
        }
        return false;
    }

    /**
     * Get the foreign key between a child and his parent scope
     * @param ModelCriteria $childScopeEntityQuery
     * @param BaseScope $parentScope
     * @return string phpName of the foreign key
     * @throws \Exception if no foreign keys between the parameters
     */
    protected function getParentEntityFk(ModelCriteria $childScopeEntityQuery, BaseScope $parentScope)
    {
        foreach ($childScopeEntityQuery->getTableMap()->getForeignKeys() as $childForeignKey) {
            if ($childForeignKey->getRelatedTableName() === $parentScope->getEntity()) {
                return $childForeignKey->getPhpName();
            }
        }
        throw new \Exception("No foreign keys between child and parent scope");
    }

    /**
     * Get the direct parent entity of an entity by his entity
     *
     * @param $scopeEntity
     * @return object parent entity or false if no parent found
     */
    public function getParent($scopeEntity)
    {
        $childScope = $this->getScopeByEntity($scopeEntity);
        $parentScope = ScopeQuery::create()
            ->findOneByDirectChild($childScope);

        if (null === $parentScope) {
            return false;
        }

        $parentScopeEntityQuery = $this->createEntityQueryByScope($parentScope);

        /** @var ColumnMap $scopeForeignKey */
        foreach ($parentScopeEntityQuery->getTableMap()->getForeignKeys() as $scopeForeignKey) {
            if ($scopeForeignKey->getRelatedTableName() === $parentScope->getEntity()) {
                $fkValue = $scopeEntity->getByName($scopeForeignKey->getPhpName());
                $parentEntity = $this->getEntityByScope($parentScope, $fkValue);
            }
        }

        if (!isset($parentEntity)) {
            return false;
        }

        return $parentEntity;
    }

    /**
     * Get an instance of entity query for a specified scope
     *
     * @param BaseScope $scope
     * @return ModelCriteria
     */
    public function createEntityQueryByScope(BaseScope $scope)
    {
        $scopeEntityQueryClass = $scope->getEntityClass() . 'Query';

        return new $scopeEntityQueryClass;
    }

    /**
     * Get the instance of entity for the scope and entityId given
     *
     * @param BaseScope $scope
     * @param int $scopeEntityId
     * @return mixed
     */
    public function getEntityByScope(BaseScope $scope, $scopeEntityId)
    {
        $scopeEntityQuery = $this->createEntityQueryByScope($scope);

        return $scopeEntityQuery->findOneById($scopeEntityId);
    }

    /**
     * Get the Scope by his type
     *
     * @param string $scopeType The scope type (ex: store)
     * @return Scope
     */
    public function getScopeByType($scopeType)
    {
        return ScopeQuery::create()->findOneByEntity($scopeType);
    }

    /**
     * Get the instance of entity for the type and entityId given
     *
     * @param string $scopeType
     * @param $scopeEntityId
     * @return mixed
     */
    public function getEntityByType($scopeType, $scopeEntityId)
    {
        $scope = ScopeQuery::create()->findOneByEntity($scopeType);

        if ($scope !== null) {
            return $this->getEntityByScope($scope, $scopeEntityId);
        }

        return null;
    }

    /**
     * Get the scope of an entity
     *
     * @param mixed $scopeEntity
     * @return Scope
     */
    public function getScopeByEntity($scopeEntity)
    {
        $entityTableMap = $scopeEntity::TABLE_MAP;
        $entityClassName = ltrim((new $entityTableMap)->getOMClass(false), '\\');

        return ScopeQuery::create()->findOneByEntityClass($entityClassName);
    }
}