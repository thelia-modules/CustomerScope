<?php

namespace CustomerScope\Loop;

use CustomerScope\Model\CustomerScope;
use CustomerScope\Model\CustomerScopeQuery;
use CustomerScope\Model\Map\CustomerScopeTableMap;
use CustomerScope\Model\Map\ScopeTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

/**
 * Loop on customer scopes.
 */
class CustomerScopeLoop extends BaseLoop implements PropelSearchLoopInterface
{
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntListTypeArgument("customer_id"),
            Argument::createIntListTypeArgument("scope_id"),
            Argument::createIntListTypeArgument("entity_id"),
            Argument::createAnyTypeArgument("entity")
        );
    }

    public function buildModelCriteria()
    {
        $customerScopeQuery = CustomerScopeQuery::create();

        $customerScopeQuery->addJoinObject(
            new Join(
                CustomerScopeTableMap::SCOPE_ID,
                ScopeTableMap::ID,
                Criteria::INNER_JOIN
            ),
            "customer_scope_join_scope"
        );

        if (null !== $customerId = $this->getArgValue("customer_id")) {
            $customerScopeQuery->filterByCustomerId($customerId, Criteria::IN);
        }

        if (null !== $scopeId = $this->getArgValue("scope_id")) {
            $customerScopeQuery->filterByScopeId($scopeId, Criteria::IN);
        }

        if (null !== $entityId = $this->getArgValue("entity_id")) {
            $customerScopeQuery->filterByEntityId($entityId, Criteria::IN);
        }

        if (null !== $entity = $this->getArgValue("entity")) {
            $customerScopeQuery->addJoinCondition(
                "customer_scope_join_scope",
                ScopeTableMap::ENTITY . Criteria::EQUAL . "?",
                $entity,
                null,
                \PDO::PARAM_STR
            );
        }

        $customerScopeQuery->withColumn(ScopeTableMap::ENTITY, "scope_entity");

        return $customerScopeQuery;
    }

    public function parseResults(LoopResult $loopResult)
    {
        /** @var CustomerScope $customerScope */
        foreach ($loopResult->getResultDataCollection() as $customerScope) {
            $loopResultRow = new LoopResultRow($customerScope);
            $loopResultRow
                ->set("CUSTOMER_SCOPE_CUSTOMER_ID", $customerScope->getCustomerId())
                ->set("CUSTOMER_SCOPE_SCOPE_ID", $customerScope->getScopeId())
                ->set("CUSTOMER_SCOPE_ENTITY_ID", $customerScope->getEntityId())
                ->set("CUSTOMER_SCOPE_SCOPE_ENTITY", $customerScope->getVirtualColumn("scope_entity"));

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}
