<?php

namespace CustomerScope\Tests\Model;

use CustomerScope\Model\CustomerQuery as CustomerScopeCustomerQuery;
use CustomerScope\Model\Scope;
use CustomerScope\Tests\AbstractCustomerScopeTest;
use Propel\Runtime\Collection\ObjectCollection;
use Thelia\Model\Customer;

/**
 * Tests for the CustomerQuery scope extensions.
 */
class CustomerQueryTest extends AbstractCustomerScopeTest
{
    /**
     * Build a scope array to use in scope query filters.
     * @param string $code Scope code.
     * @param int $entityId Scope entity id.
     * @return array
     */
    protected static function makeScopeArray($code, $entityId)
    {
        return [
           'ScopeEntity' => $code,
           'EntityId' => $entityId,
        ];
    }

    /**
     * Assert that a Propel collection contains exactly some customers.
     * @param array $expectedCustomers Expected customers (can be empty).
     * @param ObjectCollection $actualCustomers Collection to assert.
     */
    protected function assertCustomers(array $expectedCustomers, ObjectCollection $actualCustomers)
    {
        if (empty($expectedCustomers)) {
            $this->assertEmpty($expectedCustomers);
        } else {
            $this->assertNotEmpty($actualCustomers);
            $this->assertEquals(count($expectedCustomers), $actualCustomers->count());
            foreach ($expectedCustomers as $customer) {
                $this->assertContains($customer, $actualCustomers);
            }
        }
    }

    /**
     * @covers CustomerScope\Model\CustomerQuery::filterByScopes()
     */
    public function testFilterByNoScopes()
    {
        $customers = CustomerScopeCustomerQuery::create()
            ->filterByScopes([])
            ->filterByFirstname(self::$testCustomersFirstNameFilter)
            ->find();

        $this->assertCustomers(self::$testCustomers, $customers);
    }

    /**
     * @covers CustomerScope\Model\CustomerQuery::filterByScope()
     */
    public function testFilterByEmptyScope()
    {
        $customers = CustomerScopeCustomerQuery::create()
            ->filterByScope([])
            ->filterByFirstname(self::$testCustomersFirstNameFilter)
            ->find();

        $this->assertCustomers(self::$testCustomers, $customers);
    }

    /**
     * @covers CustomerScope\Model\CustomerQuery::filterByScope()
     */
    public function testFilterByNonExistingScope()
    {
        $customers = CustomerScopeCustomerQuery::create()
            ->filterByScope(self::makeScopeArray(self::$nonExistingScopeCode, 1))
            ->filterByFirstname(self::$testCustomersFirstNameFilter)
            ->find();

        $this->assertCustomers([], $customers);
    }

    /**
     * @covers CustomerScope\Model\CustomerQuery::filterByScope()
     */
    public function testFilterOneScopeOneCustomer()
    {
        /** @var Customer $firstCustomer */
        $firstCustomer = self::$testCustomers[0];
        /** @var Scope $firstScope */
        $firstScope = self::$testScopes[0];
        $firstScopeFirstEntity = self::$testEntitiesInstances[$firstScope->getEntityClass()][0];

        $this->handler->registerCustomerScope($firstCustomer->getId(), $firstScopeFirstEntity);

        $customers = CustomerScopeCustomerQuery::create()
            ->filterByScope(self::makeScopeArray($firstScope->getEntity(), $firstScopeFirstEntity->getId()))
            ->filterByFirstname(self::$testCustomersFirstNameFilter)
            ->find();

        $this->assertCustomers([$firstCustomer], $customers);
    }

    /**
     * @depends testFilterOneScopeOneCustomer
     * @covers CustomerScope\Model\CustomerQuery::filterByScopes()
     */
    public function testFilterCustomerInMultipleScopes()
    {
        /** @var Customer $firstCustomer */
        $firstCustomer = self::$testCustomers[0];
        /** @var Scope $firstScope */
        $firstScope = self::$testScopes[0];
        $firstScopeFirstEntity = self::$testEntitiesInstances[$firstScope->getEntityClass()][0];
        /** @var Scope $secondScope */
        $secondScope = self::$testScopes[1];
        $secondScopeFirstEntity = self::$testEntitiesInstances[$secondScope->getEntityClass()][0];

        $this->handler->registerCustomerScope($firstCustomer->getId(), $secondScopeFirstEntity);

        $customers = CustomerScopeCustomerQuery::create()
            ->filterByScopes([
                self::makeScopeArray($firstScope->getEntity(), $firstScopeFirstEntity->getId()),
                self::makeScopeArray($secondScope->getEntity(), $secondScopeFirstEntity->getId()),
            ])
            ->filterByFirstname(self::$testCustomersFirstNameFilter)
            ->find();

        $this->assertCustomers([$firstCustomer], $customers);
    }

    /**
     * @depends testFilterCustomerInMultipleScopes
     * @covers CustomerScope\Model\CustomerQuery::filterByScope()
     */
    public function testFilterOneScopeMultipleCustomers()
    {
        /** @var Customer $firstCustomer */
        $firstCustomer = self::$testCustomers[0];
        /** @var Customer $secondCustomer */
        $secondCustomer = self::$testCustomers[1];
        /** @var Scope $firstScope */
        $firstScope = self::$testScopes[0];
        $firstScopeFirstEntity = self::$testEntitiesInstances[$firstScope->getEntityClass()][0];

        $this->handler->registerCustomerScope($secondCustomer->getId(), $firstScopeFirstEntity);

        $customers = CustomerScopeCustomerQuery::create()
            ->filterByScope(self::makeScopeArray($firstScope->getEntity(), $firstScopeFirstEntity->getId()))
            ->filterByFirstname(self::$testCustomersFirstNameFilter)
            ->find();

        $this->assertCustomers([$firstCustomer, $secondCustomer], $customers);
    }

    /**
     * @depends testFilterCustomerInMultipleScopes
     * @covers CustomerScope\Model\CustomerQuery::filterByScopes()
     */
    public function testFilterCustomerInSeperateScope()
    {
        /** @var Customer $firstCustomer */
        $firstCustomer = self::$testCustomers[0];
        /** @var Customer $secondCustomer */
        $secondCustomer = self::$testCustomers[1];
        /** @var Customer $thirdCustomer */
        $thirdCustomer = self::$testCustomers[2];
        /** @var Scope $firstScope */
        $firstScope = self::$testScopes[0];
        $firstScopeFirstEntity = self::$testEntitiesInstances[$firstScope->getEntityClass()][0];
        /** @var Scope $secondScope */
        $secondScope = self::$testScopes[1];
        $secondScopeFirstEntity = self::$testEntitiesInstances[$secondScope->getEntityClass()][0];

        $this->handler->registerCustomerScope($thirdCustomer->getId(), $secondScopeFirstEntity);

        $customers = CustomerScopeCustomerQuery::create()
            ->filterByScopes([
                self::makeScopeArray($firstScope->getEntity(), $firstScopeFirstEntity->getId()),
                self::makeScopeArray($secondScope->getEntity(), $secondScopeFirstEntity->getId()),
            ])
            ->filterByFirstname(self::$testCustomersFirstNameFilter)
            ->find();

        $this->assertCustomers([$firstCustomer, $secondCustomer, $thirdCustomer], $customers);
    }
}
