<?php

namespace CustomerScope;

use CustomerScope\Model\CustomerScopeQuery;
use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Install\Database;
use Thelia\Module\BaseModule;

/**
 * Class CustomerScope
 * @package CustomerScope
 */
class CustomerScope extends BaseModule
{
    /**
     * Translation Message Domain
     */
    const MESSAGE_DOMAIN = 'customerscope';

    /**
     * {@inheritdoc}
     * - Insert the module's DB model
     */
    public function postActivation(ConnectionInterface $con = null)
    {
        /**
         * Create the module tables if they do not exists
         */
        $database = new Database($con);
        try {
            CustomerScopeQuery::create()->findOne();
        } catch (\Exception $e) {
            $database->insertSql(null, [__DIR__ . DS . 'Config' . DS . 'thelia.sql']);
        }
    }
}
