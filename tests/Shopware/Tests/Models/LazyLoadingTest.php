<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Shopware\Models\Customer\Group;
use Shopware\Models\Customer\Customer;

class LazyLoadingTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    private $em;

    public static function tearDownAfterClass()
    {
        Shopware()->Db()->query("DELETE FROM s_user WHERE email LIKE 'lazyloadtest@shopware.com';");
        Shopware()->Db()->query("DELETE FROM s_user WHERE email LIKE 'lazyloadtest2@shopware.com';");
        Shopware()->Db()->query("DELETE FROM s_core_customergroups WHERE description LIKE 'testGroup'");
    }

    public function setUp()
    {
        $this->em = Shopware()->Models();
        $this->em->getConfiguration()->setSQLLogger($this->logger);
    }

    private function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public function testCanCreateEntity()
    {
        $groupKey = $this->generateRandomString(5);

        $group = new Group();
        $group->setKey($groupKey);
        $group->setName('testGroup');
        $group->setTax(true);
        $group->setTaxInput(true);
        $group->setMode(1);

        $anotherGroup = new Group();
        $anotherGroup->setKey($this->generateRandomString(5));
        $anotherGroup->setName('testGroup');
        $anotherGroup->setTax(true);
        $anotherGroup->setTaxInput(true);
        $anotherGroup->setMode(1);

        $customer = new Customer();
        $customer->setEmail('lazyloadtest@shopware.com');
        $customer->setGroup($group);

        $this->em->persist($customer);
        $this->em->persist($group);
        $this->em->persist($anotherGroup);

        $this->em->flush();
        $this->em->clear();

        $this->assertNotEmpty($customer->getId());
        $this->assertNotEmpty($customer->getGroup()->getId());

        return $customer;
    }

    /**
     * @depends testCanCreateEntity
     */
    public function testLoadExplicit(Shopware\Models\Customer\Customer $customer)
    {
        $customerId  = $customer->getId();
        $groupId  = $customer->getGroup()->getId();
        $groupKey = $customer->getGroup()->getKey();
        $customer    = null;
        $this->em->clear();

        /** @var Customer $customer */
        $customer = $this->em->getRepository('Shopware\Models\Customer\Customer')->find($customerId);

        /** @var Group $group */
        $group = $this->em->getRepository('Shopware\Models\Customer\Group')->find($groupId);

        $this->assertEquals($customer->getId(), $customerId);
        $this->assertEquals($customer->getGroupKey(), $groupKey);
        $this->assertEquals($group->getKey(), $groupKey);
        $this->assertEquals($customer->getGroupKey(), $group->getKey());
    }

    /**
     * @depends testCanCreateEntity
     */
    public function testDqlJoinQuery(Shopware\Models\Customer\Customer $customer)
    {
        $customerId  = $customer->getId();
        $groupKey = $customer->getGroup()->getKey();
        $customer = null;
        $this->em->clear();

        $query = $this->em->createQuery("SELECT p, g FROM Shopware\Models\Customer\Customer p JOIN p.group g WHERE p.id = :customerId");
        $query->setParameter('customerId', $customerId);

        /** @var Customer $customer */
        $customer = $query->getOneOrNullResult();
        $group = $customer->getGroup();

        $this->assertEquals($customer->getId(), $customerId);
        $this->assertEquals($group->getKey(), $groupKey);
        $this->assertEquals($customer->getGroupKey(), $groupKey);
        $this->assertEquals($customer->getGroupKey(), $group->getKey());
    }

    /**
     * @depends testCanCreateEntity
     */
    public function testDqlFetchEagerQuery(Shopware\Models\Customer\Customer $customer)
    {
        $customerId  = $customer->getId();
        $groupKey = $customer->getGroup()->getKey();
        $customer    = null;
        $this->em->clear();

        $query = $this->em->createQuery("SELECT p FROM Shopware\Models\Customer\Customer p WHERE p.id = :customerId");
        $query->setFetchMode('Shopware\Models\Customer\Customer', 'group', ClassMetadata::FETCH_EAGER);
        $query->setParameter('customerId', $customerId);

        /** @var Customer $customer */
        $customer = $query->getOneOrNullResult();
        $group = $customer->getGroup();

        $this->assertEquals($customer->getId(), $customerId);
        $this->assertEquals($group->getKey(), $groupKey);
        $this->assertEquals($customer->getGroupKey(), $groupKey);
        $this->assertEquals($customer->getGroupKey(), $group->getKey());
    }

    /**
     * @depends testCanCreateEntity
     */
    public function testDqlLazyQuery(Shopware\Models\Customer\Customer $customer)
    {
        $customerId  = $customer->getId();
        $groupKey = $customer->getGroup()->getKey();
        $customer    = null;
        $this->em->clear();

        $query = $this->em->createQuery("SELECT p FROM Shopware\Models\Customer\Customer p WHERE p.id = :customerId");
        $query->setParameter('customerId', $customerId);

        /** @var Customer $customer */
        $customer = $query->getOneOrNullResult();
        $group = $customer->getGroup();

        $this->assertEquals($customer->getId(), $customerId);
        $this->assertEquals($group->getKey(), $groupKey);
        $this->assertEquals($customer->getGroupKey(), $groupKey);
        $this->assertEquals($customer->getGroupKey(), $group->getKey());
    }

    /**
     * @depends testCanCreateEntity
     */
    public function testLazyLoad(Shopware\Models\Customer\Customer $customer)
    {
        $customerId  = $customer->getId();
        $groupKey = $customer->getGroup()->getKey();
        $customer    = null;
        $this->em->clear();

        /** @var Customer $customer */
        $customer = $this->em->getRepository('Shopware\Models\Customer\Customer')->find($customerId);
        $group = $customer->getGroup();

        $this->assertEquals($customer->getId(), $customerId);
        $this->assertEquals($group->getKey(), $groupKey);
        $this->assertEquals($customer->getGroupKey(), $groupKey);
        $this->assertEquals($customer->getGroupKey(), $group->getKey());
    }

    /**
     * @depends testCanCreateEntity
     */
    public function testCanCreateEntityWithReference(Shopware\Models\Customer\Customer $customer)
    {
        $groupId = $customer->getGroup()->getId();
        $groupKey = $customer->getGroup()->getKey();
        $customer    = null;
        $this->em->clear();

        $customer = new Customer();
        $customer->setGroup($this->em->getReference('Shopware\Models\Customer\Group', $groupId));

        $this->assertEmpty($customer->getId());
        $this->assertEquals($groupId, $customer->getGroup()->getId());
        $this->assertEquals($groupKey, $customer->getGroup()->getKey());

        return $customer;
    }

    public function testCanCreateEntityWithNewGroup()
    {
        $this->em->clear();
        $groupKey = $this->generateRandomString(5);
        $group = new Group();
        $group->setKey($groupKey);

        $customer = new Customer();
        $customer->setGroup($group);

        $this->assertEmpty($customer->getId());
        $this->assertEquals($group, $customer->getGroup());
        $this->assertEmpty($customer->getGroup()->getId());
        $this->assertEquals($groupKey, $customer->getGroup()->getKey());

        return $customer;
    }

    /**
     * @depends testCanCreateEntity
     */
    public function testCanUpdateEntityWithReference(Shopware\Models\Customer\Customer $customer)
    {
        $groupId = $customer->getGroup()->getId();
        $groupKey = $customer->getGroup()->getKey();

        $customer = new Customer();
        $customer->setEmail('lazyloadtest2@shopware.com');

        $this->em->persist($customer);
        $this->em->flush();
        $customerId = $customer->getId();

        $this->em->clear();

        $customer = $this->em->find('Shopware\Models\Customer\Customer', $customerId);
        $group = $this->em->getReference('Shopware\Models\Customer\Group', $groupId);
        $customer->setGroup($group);

        $this->assertNotEmpty($customer->getId());
        $this->assertEquals($group, $customer->getGroup());
        $this->assertNotEmpty($customer->getGroup()->getId());
        $this->assertEquals($groupKey, $customer->getGroup()->getKey());

        return $customer;
    }

    public function testOneToManyLoading()
    {
        $article = $this->em->find('Shopware\Models\Article\Supplier', 2);

        $this->assertNotEmpty(end($article->getArticles()->toArray())->getId());
    }
}
