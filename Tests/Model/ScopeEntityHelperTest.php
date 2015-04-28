<?php

namespace CustomerScope\Tests\Model;

use CustomerScope\Model\Base\Scope;
use CustomerScope\Tests\AbstractCustomerScopeTest;
use Propel\Runtime\ActiveQuery\ModelCriteria;

class ScopeEntityHelperTest extends AbstractCustomerScopeTest
{
    /**
     * @covers CustomerScopeHandler::getEntityQueryByScope()
     */
    public function testValidScopeHasEntityQuery()
    {
        /** @var Scope $scope */
        foreach (self::$testScopes as $scope) {
            $entityQuery = $this->helper->createEntityQueryByScope($scope);
            $this->assertNotNull($entityQuery);
            $this->assertInstanceOf(ModelCriteria::class, $entityQuery);
        }
    }

    /**
     * @covers CustomerScopeHandler::getScopeByEntity()
     */
    public function testNonAssociatedEntityHasNoScope()
    {
        $scope = $this->helper->getScopeByEntity(new self::$nonScopeEntityClassName());
        $this->assertNull($scope);
    }

    /**
     * @covers CustomerScopeHandler::getScopeByEntity()
     */
    public function testCanGetScopeForAssociatedEntity()
    {
        foreach (self::$scopeFixtures as $scopeGroupCode => $scopes) {
            foreach ($scopes as $scopeCode => $scopeParams) {
                foreach (self::$testEntitiesInstances[$scopeParams["class"]] as $scopeEntity) {
                    $scope = $this->helper->getScopeByEntity($scopeEntity);
                    $this->assertNotNull($scope);
                    $this->assertInstanceOf(Scope::class, $scope);
                    $this->assertEquals($scopeParams['class'], $scope->getEntityClass());
                }
            }
        }
    }
}
