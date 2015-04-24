<?php

namespace CustomerScope\Tests\Loop;

use CustomerScope\Loop\CustomerScopeLoop;
use CustomerScope\Model\Scope;
use CustomerScope\Tests\AbstractCustomerScopeTest;
use Propel\Runtime\Util\PropelModelPager;
use Thelia\Model\Customer;

/**
 * Tests for the CustomerScopeLoop.
 */
class CustomerScopeLoopTest extends AbstractCustomerScopeTest
{
    /**
     * The customer scope loop under test.
     * @var CustomerScopeLoop
     */
    protected $loop;

    /**
     * Test arguments.
     * @var array
     */
    protected $arguments = [];

    public function setUp()
    {
        parent::setUp();

        $this->loop = new CustomerScopeLoop($this->getContainer());

        /** @var Customer $firstCustomer */
        $firstCustomer = self::$testCustomers[0];
        /** @var Customer $secondCustomer */
        $secondCustomer = self::$testCustomers[1];
        /** @var Scope $firstScope */
        $firstScope = self::$testScopes[0];
        $firstScopeFirstEntity = self::$testEntitiesInstances[$firstScope->getEntityClass()][0];
        /** @var Scope $secondScope */
        $secondScope = self::$testScopes[1];
        $secondScopeFirstEntity = self::$testEntitiesInstances[$secondScope->getEntityClass()][0];

        $this->arguments["customer_id"] = $firstCustomer->getId();
        $this->arguments["scope_id"] = $firstScope->getId();
        $this->arguments["entity_id"] = $firstScopeFirstEntity->getId();
        $this->arguments["entity"] = $firstScope->getEntity();

        // insert some scopes, but we only want the first
        $this->handler->registerCustomerScope($firstCustomer->getId(), $firstScopeFirstEntity);

        $this->handler->registerCustomerScope($firstCustomer->getId(), $secondScopeFirstEntity);
        $this->handler->registerCustomerScope($secondCustomer->getId(), $firstScopeFirstEntity);
        $this->handler->registerCustomerScope($secondCustomer->getId(), $secondScopeFirstEntity);
    }

    /**
     * @covers CustomerScopeLoop::initializeArgs()
     */
    public function testHasNoMandatoryArguments()
    {
        $this->loop->initializeArgs([]);
    }

    /**
     * @covers CustomerScopeLoop::initializeArgs()
     */
    public function testAcceptsAllArguments()
    {
        $this->loop->initializeArgs($this->arguments);
    }

    /**
     * @covers CustomerScopeLoop::buildModelCriteria()
     * @covers CustomerScopeLoop::exec()
     * @covers CustomerScopeLoop::parseResults()
     */
    public function testHasExpectedOutput()
    {
        $this->loop->initializeArgs($this->arguments);

        $loopResult = $this->loop->exec(
            new PropelModelPager($this->loop->buildModelCriteria())
        );

        $this->assertEquals(1, $loopResult->getCount());

        $loopResult = $this->loop->parseResults($loopResult);

        $loopResult->rewind();
        $loopResultRow = $loopResult->current();
        $this->assertEquals($this->arguments["customer_id"], $loopResultRow->get("CUSTOMER_SCOPE_CUSTOMER_ID"));
        $this->assertEquals($this->arguments["scope_id"], $loopResultRow->get("CUSTOMER_SCOPE_SCOPE_ID"));
        $this->assertEquals($this->arguments["entity_id"], $loopResultRow->get("CUSTOMER_SCOPE_ENTITY_ID"));
        $this->assertEquals($this->arguments["entity"], $loopResultRow->get("CUSTOMER_SCOPE_SCOPE_ENTITY"));
    }
}
