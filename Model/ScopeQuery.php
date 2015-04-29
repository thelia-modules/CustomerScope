<?php

namespace CustomerScope\Model;

use CustomerScope\Model\Base\ScopeQuery as BaseScopeQuery;
use Propel\Runtime\ActiveQuery\Criteria;

class ScopeQuery extends BaseScopeQuery
{

    /**
     * @param Scope $scopeParent
     * @return Scope
     */
    public function findOneByDirectParent(Scope $scopeParent)
    {
        return self::filterByScopeGroupId($scopeParent->getScopeGroupId())
            ->filterByPosition($scopeParent->getPosition(), Criteria::GREATER_THAN)
            ->orderByPosition(Criteria::ASC)
            ->find()->getFirst();
    }

    /**
     * @param Scope $scopeChild
     * @return mixed
     */
    public function findOneByDirectChild(Scope $scopeChild)
    {
        return self::filterByScopeGroupId($scopeChild->getScopeGroupId())
            ->filterByPosition($scopeChild->getPosition(), Criteria::LESS_THAN)
            ->orderByPosition(Criteria::DESC)
            ->find()->getFirst();
    }
}
