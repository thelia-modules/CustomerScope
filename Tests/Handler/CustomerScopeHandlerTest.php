<?php

namespace CustomerScope\Tests\Handler;

use CustomerScope\Model\Scope;
use CustomerScope\Tests\AbstractCustomerScopeTest;
use Thelia\Model\Customer;

/**
 * Tests for CustomerScopeHandler.
 */
class CustomerScopeHandlerTest extends AbstractCustomerScopeTest
{
    /**
     * @covers CustomerScopeHandler::getCustomerScopeEntity()
     * @covers CustomerScopeHandler::getCustomerScopeEntities()
     */
    public function testNullCustomerHasNoScope()
    {
        $scopeEntity = $this->handler->getCustomerScopeEntity(null);
        $this->assertNull($scopeEntity);

        $scopeEntities = $this->handler->getCustomerScopeEntities(null);
        $this->assertEmpty($scopeEntities);
    }

    /**
     * @covers CustomerScopeHandler::getCustomerScopeEntity()
     * @covers CustomerScopeHandler::getCustomerScopeEntities()
     */
    public function testCustomerHasNoScopeInitially()
    {
        /** @var Customer $customer */
        foreach (self::$testCustomers as $customer) {
            $scopeEntity = $this->handler->getCustomerScopeEntity($customer->getId());
            $this->assertNull($scopeEntity);

            $scopeEntities = $this->handler->getCustomerScopeEntities($customer->getId());
            $this->assertEmpty($scopeEntities);
        }
    }

    /**
     * @covers CustomerScopeHandler::registerCustomerScope()
     * @covers CustomerScopeHandler::getCustomerScopeEntity()
     * @covers CustomerScopeHandler::getCustomerScopeEntities()
     */
    public function testCanRegisterAndGetCustomerScopeEntity()
    {
        /** @var Customer $firstCustomer */
        $firstCustomer = self::$testCustomers[0];
        /** @var Scope $firstScope */
        $firstScope = self::$testScopes[0];
        $firstScopeEntity = self::$testEntitiesInstances[$firstScope->getEntityClass()][0];

        // register the customer scope
        $this->handler->registerCustomerScope($firstCustomer->getId(), $firstScopeEntity);

        // check that we can get back the scope entity
        $scopeEntity = $this->handler->getCustomerScopeEntity($firstCustomer->getId());
        $this->assertNotNull($scopeEntity);
        $this->assertEquals($firstScopeEntity, $scopeEntity);

        $scopeEntities = $this->handler->getCustomerScopeEntities($firstCustomer->getId());
        $this->assertNotEmpty($scopeEntities);
        $this->assertEquals(1, count($scopeEntities));
        $this->assertContains($firstScopeEntity, $scopeEntities);
    }

    /**
     * @depends testCanRegisterAndGetCustomerScopeEntity
     * @covers CustomerScopeHandler::registerCustomerScope()
     * @covers CustomerScopeHandler::getCustomerScopeEntity()
     * @covers CustomerScopeHandler::getCustomerScopeEntities()
     */
    public function testCannotRegisterSameCustomerScopeEntityTwice()
    {
        // rerun the previous test, which should do nothing more
        $this->testCanRegisterAndGetCustomerScopeEntity();
    }

    /**
     * @depends testCanRegisterAndGetCustomerScopeEntity
     * @covers CustomerScopeHandler::getCustomerScopeEntity()
     * @covers CustomerScopeHandler::getCustomerScopeEntities()
     */
    public function testOtherCustomersStillHaveNoScope()
    {
        for ($i = 1; $i < count(self::$testCustomers); ++$i) {
            /** @var Customer $customer */
            $customer = self::$testCustomers[$i];

            $scopeEntity = $this->handler->getCustomerScopeEntity($customer->getId());
            $this->assertNull($scopeEntity);

            $scopeEntities = $this->handler->getCustomerScopeEntities($customer->getId());
            $this->assertEmpty($scopeEntities);
        }
    }

    /**
     * @depends testCanRegisterAndGetCustomerScopeEntity
     * @covers CustomerScopeHandler::registerCustomerScope()
     * @covers CustomerScopeHandler::getCustomerScopeEntity()
     * @covers CustomerScopeHandler::getCustomerScopeEntities()
     */
    public function testCanRegisterTwoScopeEntities()
    {
        /** @var Customer $firstCustomer */
        $firstCustomer = self::$testCustomers[0];
        /** @var Scope $firstScope */
        $firstScope = self::$testScopes[0];
        $firstScopeEntity = self::$testEntitiesInstances[$firstScope->getEntityClass()][0];
        $secondScopeEntity = self::$testEntitiesInstances[$firstScope->getEntityClass()][1];

        // register the customer scope
        $this->handler->registerCustomerScope($firstCustomer->getId(), $secondScopeEntity);

        // check that we still get the previous scope entity first
        $scopeEntity = $this->handler->getCustomerScopeEntity($firstCustomer->getId());
        $this->assertNotNull($scopeEntity);
        $this->assertEquals($firstScopeEntity, $scopeEntity);

        // check that we get both scopes
        $scopeEntities = $this->handler->getCustomerScopeEntities($firstCustomer->getId());
        $this->assertNotEmpty($scopeEntities);
        $this->assertEquals(2, count($scopeEntities));
        $this->assertContains($firstScopeEntity, $scopeEntities);
        $this->assertContains($secondScopeEntity, $scopeEntities);
    }

    /**
     * @depends testCanRegisterTwoScopeEntities
     * @covers CustomerScopeHandler::registerCustomerScope()
     * @covers CustomerScopeHandler::getCustomerScopeEntity()
     * @covers CustomerScopeHandler::getCustomerScopeEntities()
     */
    public function testCanRegisterScopeEntityFromAnotherScope()
    {
        /** @var Customer $firstCustomer */
        $firstCustomer = self::$testCustomers[0];
        /** @var Scope $firstScope */
        $firstScope = self::$testScopes[0];
        $firstScopeEntity = self::$testEntitiesInstances[$firstScope->getEntityClass()][0];
        $secondScopeEntity = self::$testEntitiesInstances[$firstScope->getEntityClass()][1];
        /** @var Scope $secondScope */
        $secondScope = self::$testScopes[1];
        $newScopeEntity = self::$testEntitiesInstances[$secondScope->getEntityClass()][0];

        // register the customer scope
        $this->handler->registerCustomerScope($firstCustomer->getId(), $newScopeEntity);

        // check that we still get the first scope entity
        $scopeEntity = $this->handler->getCustomerScopeEntity($firstCustomer->getId());
        $this->assertNotNull($scopeEntity);
        $this->assertEquals($firstScopeEntity, $scopeEntity);

        // check that we can get our new scope entity
        $scopeEntities = $this->handler->getCustomerScopeEntities($firstCustomer->getId());
        $this->assertNotEmpty($scopeEntities);
        $this->assertEquals(3, count($scopeEntities));
        $this->assertContains($firstScopeEntity, $scopeEntities);
        $this->assertContains($secondScopeEntity, $scopeEntities);
        $this->assertContains($newScopeEntity, $scopeEntities);
    }
}
