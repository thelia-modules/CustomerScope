<?php

namespace CustomerScope\Tests;

use CustomerScope\Handler\CustomerScopeHandler;
use CustomerScope\Model\CustomerQuery;
use CustomerScope\Model\Scope;
use CustomerScope\Model\ScopeEntityHelper;
use CustomerScope\Model\ScopeGroup;
use CustomerScope\Model\ScopeQuery;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Propel;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Security\SecurityContext;
use Thelia\Core\Translation\Translator;
use Thelia\Model\Area;
use Thelia\Model\AreaQuery;
use Thelia\Model\Country;
use Thelia\Model\CountryQuery;
use Thelia\Model\Customer;
use Thelia\Model\CustomerTitle;
use Thelia\Model\CustomerTitleQuery;
use Thelia\Tests\ContainerAwareTestCase;

/**
 * Base class for CustomerScope tests.
 *
 * Create test customers.
 * Create test scope groups and scopes.
 * Create test scope entities.
 */
abstract class AbstractCustomerScopeTest extends ContainerAwareTestCase
{
    /**
     * Number of customers to make available for tests.
     * @var int
     */
    const TEST_CUSTOMERS_COUNT = 3;

    /**
     * Number of test scope entities to make available for tests (for each class).
     * @var int
     */
    const TEST_ENTITIES_COUNT = 2;

    /**
     * Test scope groups and scopes to create.
     * Structure:
     *     scopeGroupCode => [
     *         scopeCode => scopeEntityClassName
     *     ]
     * @var array
     */
    protected static $scopeFixtures = [
        "testgroup" => [
            "area" => ["class" => "Thelia\\Model\\Area", "position" => 1],
            "country" => ["class" => "Thelia\\Model\\Country", "position" => 2],
        ]
    ];

    protected static $scopeEntityFixtures = [
        "Europe" => [
                "France",
                "Portugal"
        ],
        "Asie" => [
                "Chine",
                "Japon"
        ],
        "Océanie" => []
    ];

    /**
     * A code for a non existing scope.
     * @var string
     */
    protected static $nonExistingScopeCode = "customer-scope-unit-test-non-existing-scope-";

    /**
     * A class not associated with any scopes.
     * @var string
     */
    protected static $nonScopeEntityClassName = "Thelia\\Model\\Coupon";

    /**
     * Unique first name that can be used to filter only test customers.
     * @var string
     */
    protected static $testCustomersFirstNameFilter = "customer-scope-unit-test-";

    /**
     * Customers to be used for tests.
     * @var array
     */
    protected static $testCustomers = [];

    /**
     * Scope groups to be used for tests.
     * @var array
     */
    protected static $testScopeGroups = [];

    /**
     * Scopes to be used for tests.
     * @var array
     */
    protected static $testScopes = [];

    /**
     * Objects to be used as test scope entities.
     * Structure:
     *     className => [instances]
     * @var array
     */
    protected static $testEntitiesInstances = [];

    /**
     * The scope handler.
     * @var CustomerScopeHandler
     */
    protected $handler;

    /**
     * @var ScopeEntityHelper
     */
    protected $helper;

    protected function buildContainer(ContainerBuilder $container)
    {

    }

    public static function setUpBeforeClass()
    {
        Propel::getConnection()->beginTransaction();

        self::ensureNamesAssertions();
        self::makeTestCustomers(self::TEST_CUSTOMERS_COUNT);
        self::makeTestScopes();
        self::makeTestEntities();
    }

    public function setUp()
    {
        parent::setUp();

        /** @var Request $request */
        $request = $this->container->get("request");
        /** @var SecurityContext $securityContext */
        $securityContext = $this->container->get("thelia.securitycontext");
        /** @var Translator $translator */
        $translator = $this->container->get("thelia.translator");

        // TODO: the handler and the helper should be tested before being used
        $this->handler = new CustomerScopeHandler($request, $securityContext, $translator);
        $this->helper = new ScopeEntityHelper();
    }

    public static function tearDownAfterClass()
    {
        Propel::getConnection()->rollBack();

        // clear the static variables for the next class instance
        self::$testCustomers = [];
        self::$testScopeGroups = [];
        self::$testScopes = [];
        self::$testEntitiesInstances = [];
    }

    /**
     * Ensure that the assertions made on names reserved for testing are true.
     */
    protected static function ensureNamesAssertions()
    {
        // make sure our non-existing scope actually does not exists
        while (ScopeQuery::create()->findOneByEntity(self::$nonExistingScopeCode) !== null) {
            self::$nonExistingScopeCode .= rand(0, 9);
        }

        // make sure our non-associated scope entity class is actually not associated with any scopes
        if (ScopeQuery::create()->findOneByEntityClass(self::$nonScopeEntityClassName) !== null) {
            // we cannot just pick any name, so we have to throw
            throw new \Exception(
                "Failed to assert that " . self::$nonScopeEntityClassName . " is not used as a scope entity."
                . " Edit " . get_class() . " to pick another class."
            );
        }

        // make sure that the firstname for our customers is not already used
        while (CustomerQuery::create()->findOneByFirstname(self::$testCustomersFirstNameFilter) !== null) {
            self::$testCustomersFirstNameFilter .= rand(0, 9);
        }
    }

    /**
     * Create test customers.
     * @param int $count Number of customers to create.
     * @throws PropelException
     */
    protected static function makeTestCustomers($count)
    {
        // make sure we have a customer title and country available, as they are required to create a customer
        if (null === $customerTitle = CustomerTitleQuery::create()->findOneByByDefault(true)) {
            $customerTitle = new CustomerTitle();
            $customerTitle->save();
        }

        if (null === $country = CountryQuery::create()->findOneByByDefault(true)) {
            $country = new Country();
            $country->save();
        }

        // Customer::createOrUpdate() uses the Translator, make sure it has been instantiated
        new Translator(new Container());

        for ($i = 0; $i < $count; ++$i) {
            $customer = new Customer();
            $customer->createOrUpdate(
                $customerTitle->getId(),
                self::$testCustomersFirstNameFilter,
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                $country->getId(),
                "foo",
                "foo"
            );

            self::$testCustomers[$i] = $customer;
        }
    }

    /**
     * Create the test scope groups, scopes and scope entities.
     * @throws PropelException
     */
    protected static function makeTestScopes()
    {
        foreach (self::$scopeFixtures as $scopeGroupCode => $scopes) {
            // create the scope group
            $scopeGroup = (new ScopeGroup())
                ->setCode($scopeGroupCode);
            $scopeGroup->save();

            self::$testScopeGroups[] = $scopeGroup;

            foreach ($scopes as $scopeCode => $scopeParams) {
                // create the scope
                $scope = (new Scope())
                    ->setScopeGroup($scopeGroup)
                    ->setEntity($scopeCode)
                    ->setEntityClass($scopeParams["class"])
                    ->setPosition($scopeParams["position"]);
                $scope->save();


                self::$testScopes[] = $scope;
            }
        }
    }

    /**
     * Create test scope entities
     * @throws PropelException
     */
    protected static function makeTestEntities()
    {
        foreach (self::$scopeEntityFixtures as $areaName => $childs) {
            $area = new Area();
            $area->setName($areaName)
                ->save();
            self::$testEntitiesInstances["area"][$areaName] = $area;

            if (is_array($childs)) {
                foreach ($childs as $child) {
                    $country = new Country();
                    $country->setIsoalpha2($child)
                        ->setAreaId($area->getId())
                        ->save();

                    self::$testEntitiesInstances["country"][$child] = $country;
                }
            }
        }
    }
}
