<?php

namespace CustomerScope\Tests\Model;

use CustomerScope\Model\Base\Scope;
use CustomerScope\Tests\AbstractCustomerScopeTest;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Thelia\Model\Area;
use Thelia\Model\CountryQuery;

class ScopeEntityHelperTest extends AbstractCustomerScopeTest
{
    /**
     * @covers ScopeEntityHelper::getChilds()
     * @covers ScopeEntityHelper::hasChild()
     */
    public function testScopeEntityHasChild()
    {
        $testScopeEntity = self::$testEntitiesInstances["area"]["Europe"];
        $hasChild = $this->helper->hasChild($testScopeEntity);
        $this->assertTrue($hasChild);
        $testChild = $this->helper->getChilds($testScopeEntity);
        $this->assertNotNull($testChild);
    }

    /**
     * @covers ScopeEntityHelper::getChilds()
     * @covers ScopeEntityHelper::hasChild()
     */
    public function testScopeEntityHasNoChild()
    {
        $testScopeEntity = self::$testEntitiesInstances["country"]["France"];
        $hasChild = $this->helper->hasChild($testScopeEntity);
        $this->assertFalse($hasChild);
        $testChild = $this->helper->getChilds($testScopeEntity);
        $this->assertNull($testChild);
    }

    /**
     * @covers ScopeEntityHelper::getParent()
     */
    public function testScopeEntityHasParent()
    {
        $testScopeEntity = self::$testEntitiesInstances["country"]["France"];
        $testParent = $this->helper->getParent($testScopeEntity);
        $this->assertNotNull($testParent);
        $this->assertInstanceOf('Thelia\Model\Area', $testParent);
    }

    /**
     * @covers ScopeEntityHelper::getParent()
     */
    public function testScopeEntityHasNoParent()
    {
        $testScopeEntity = self::$testEntitiesInstances["area"]["Europe"];
        $testParent = $this->helper->getParent($testScopeEntity);
        $this->assertFalse($testParent);
    }

    /**
     * @covers ScopeEntityHelper::createEntityQueryByScope()
     */
    public function testValidScopeHasEntityQuery()
    {
        /** @var Scope $scope */
        foreach (self::$testScopes as $scope) {
            $entityQuery = $this->helper->createEntityQueryByScope($scope);
            $this->assertNotNull($entityQuery);
            $this->assertInstanceOf('Propel\Runtime\ActiveQuery\ModelCriteria', $entityQuery);
        }
    }

    /**
     * @covers ScopeEntityHelper::getScopeByEntity()
     */
    public function testNonAssociatedEntityHasNoScope()
    {
        $scope = $this->helper->getScopeByEntity(new self::$nonScopeEntityClassName());
        $this->assertNull($scope);
    }

    /**
     * @covers ScopeEntityHelper::getScopeByEntity()
     */
    public function testCanGetScopeForAssociatedEntity()
    {
        foreach (self::$scopeFixtures as $scopeGroupCode => $scopes) {
            foreach ($scopes as $scopeCode => $scopeParams) {
                foreach (self::$testEntitiesInstances[$scopeCode] as $scopeEntity) {
                    $scope = $this->helper->getScopeByEntity($scopeEntity);
                    $this->assertNotNull($scope);
                    $this->assertInstanceOf('CustomerScope\Model\Scope', $scope);
                    $this->assertEquals($scopeParams['class'], $scope->getEntityClass());
                }
            }
        }
    }

    /**
     * @covers ScopeEntityHelper::getScopeByEntity()
     */
    public function testGetScopeByEntityNonExisting()
    {
        $scope = $this->helper->getScopeByEntity(new self::$nonScopeEntityClassName);
        $this->assertNull($scope);
    }

    /**
     * @covers ScopeEntityHelper::getScopeByType()
     */
    public function testGetScopeByTypeExisting()
    {
        foreach (self::$scopeFixtures as $scopeGroupCode => $scopes) {
            foreach ($scopes as $scopeCode => $scopeParams) {
                $scope = $this->helper->getScopeByType($scopeCode);
                $this->assertNotNull($scope);
                $this->assertInstanceOf('CustomerScope\Model\Scope', $scope);
                $this->assertEquals($scopeParams['class'], $scope->getEntityClass());
            }
        }
    }

    /**
     * @covers ScopeEntityHelper::getScopeByType()
     */
    public function testGetScopeByTypeNonExisting()
    {
        $scope = $this->helper->getScopeByType(self::$nonExistingScopeCode);
        $this->assertNull($scope);
    }

    /**
     * @covers ScopeEntityHelper::getEntityByScope()
     */
    public function testExistGetEntityByScope()
    {
        foreach (self::$testScopes as $scope) {
            $scopeEntity = $this->helper->getEntityByScope($scope, 1);
            $this->assertNotNull($scopeEntity);
            $this->assertInstanceOf('Propel\Runtime\ActiveRecord\ActiveRecordInterface', $scopeEntity);
        }
    }

    /**
     * @covers ScopeEntityHelper::getEntityByScope()
     */
    public function testEntityNoExistGetEntityByScope()
    {
        foreach (self::$testScopes as $scope) {
            $scopeEntity = $this->helper->getEntityByScope($scope, self::TEST_ENTITIES_COUNT+30000);
            $this->assertNull($scopeEntity);
        }
    }

    /**
     * @covers ScopeEntityHelper::getEntityByType()
     */
    public function testExistGetEntityByType()
    {
        foreach (self::$scopeFixtures as $scopeGroupCode => $scopes) {
            foreach ($scopes as $scopeCode => $scopeParams) {
                $scopeEntity = $this->helper->getEntityByType($scopeCode, 1);
                $this->assertNotNull($scopeEntity);
                $this->assertInstanceOf('Propel\Runtime\ActiveRecord\ActiveRecordInterface', $scopeEntity);
            }
        }
    }

    /**
     * @covers ScopeEntityHelper::getEntityByType()
     */
    public function testEntityNoExistGetEntityByType()
    {
        foreach (self::$scopeFixtures as $scopeGroupCode => $scopes) {
            foreach ($scopes as $scopeCode => $scopeParams) {
                $scopeEntity = $this->helper->getEntityByType($scopeCode, self::TEST_ENTITIES_COUNT+30000);
                $this->assertNull($scopeEntity);
            }
        }
    }

    /**
     * @covers ScopeEntityHelper::getEntityByType()
     */
    public function testTypeNoExistGetEntityByType()
    {
        $scopeEntity = $this->helper->getEntityByType(self::$nonExistingScopeCode, 1);
        $this->assertNull($scopeEntity);
    }
}
