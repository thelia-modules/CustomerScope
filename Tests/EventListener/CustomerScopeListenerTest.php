<?php

namespace CustomerScope\Tests\EventListener;

use CustomerScope\CustomerScope as CustomerScopeModule;
use CustomerScope\EventListener\CustomerScopeListener as CustomerScopeEventListener;
use CustomerScope\Tests\AbstractCustomerScopeTest;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Thelia\Core\Event\Customer\CustomerLoginEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\Customer;

/**
 * Tests for the CustomerScope event listener.
 */
class CustomerScopeListenerTest extends AbstractCustomerScopeTest
{
    /**
     * The test request.
     * @var Request
     */
    protected $request;

    /**
     * The customer scope event listener under test.
     * @var CustomerScopeEventListener
     */
    protected $listener;

    /**
     * Test event dispatcher.
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    public function setUp()
    {
        parent::setUp();

        $this->request = $this->container->get("request");

        $this->dispatcher = new EventDispatcher();

        // register the listener under test
        $this->dispatcher->addSubscriber(new CustomerScopeEventListener($this->request));
    }

    /**
     * @covers CustomerScope\EventListener\CustomerScope::setCustomerScopeToSession()
     */
    public function testScopesAreAddedToSessionAtLogin()
    {
        /** @var Customer $customer */
        $customer = self::$testCustomers[0];

        $this->dispatcher->dispatch(TheliaEvents::CUSTOMER_LOGIN, new CustomerLoginEvent($customer));

        $this->assertTrue($this->request->getSession()->has(CustomerScopeModule::getModuleCode()));
    }
}
