<?php

namespace CustomerScope\EventListener;

use CustomerScope\CustomerScope as ModuleCustomerScope;
use CustomerScope\Model\CustomerScopeQuery;
use Propel\Runtime\Collection\ObjectCollection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Customer\CustomerLoginEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Request;

/**
 * Listener for customer scope related events.
 */
class CustomerScopeListener implements EventSubscriberInterface
{
    /** @var Request */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::CUSTOMER_LOGIN => ["setCustomerScopeToSession", 100],
        ];
    }

    /**
     * Add Customer's scopes to his session
     *
     * @param CustomerLoginEvent $event
     */
    public function setCustomerScopeToSession(CustomerLoginEvent $event)
    {
        /** @var ObjectCollection $scopes */
        $scopes = CustomerScopeQuery::create()
            ->findByCustomerId(
                $event->getCustomer()->getId()
            );

        $this->request->getSession()->set(ModuleCustomerScope::getModuleCode(), $scopes->toArray());
    }
}
