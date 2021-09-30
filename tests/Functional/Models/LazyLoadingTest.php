<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Functional\Models;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Random;
use Shopware\Models\Article\Configurator\Template\Price as ConfiguratorPrice;
use Shopware\Models\Article\Notification;
use Shopware\Models\Article\Price as ProductPrice;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Customer\Group;
use Shopware\Models\Newsletter\Address;
use Shopware\Models\Newsletter\ContainerType\Article as NewsletterProduct;
use Shopware\Models\Premium\Premium;

class LazyLoadingTest extends TestCase
{
    /**
     * @var EntityManager
     */
    private $em;

    public static function tearDownAfterClass(): void
    {
        Shopware()->Db()->query("DELETE FROM s_user WHERE email LIKE 'lazyloadtest@shopware.com';");
        Shopware()->Db()->query("DELETE FROM s_user WHERE email LIKE 'lazyloadtest2@shopware.com';");
        Shopware()->Db()->query("DELETE FROM s_core_customergroups WHERE description LIKE 'testGroup'");
    }

    public function setUp(): void
    {
        $this->em = Shopware()->Models();
    }

    public function testCanCreateEntity(): Customer
    {
        $groupKey = Random::getAlphanumericString(5);

        $group = new Group();
        $group->setKey($groupKey);
        $group->setName('testGroup');
        $group->setTax(true);
        $group->setTaxInput(true);
        $group->setMode(true);

        $anotherGroup = new Group();
        $anotherGroup->setKey(Random::getAlphanumericString(5));
        $anotherGroup->setName('testGroup');
        $anotherGroup->setTax(true);
        $anotherGroup->setTaxInput(true);
        $anotherGroup->setMode(true);

        $customer = new Customer();
        $customer->setEmail('lazyloadtest@shopware.com');
        $customer->setGroup($group);

        $this->em->persist($customer);
        $this->em->persist($group);
        $this->em->persist($anotherGroup);

        $this->em->flush();
        $this->em->clear();

        static::assertNotEmpty($customer->getId());
        static::assertNotNull($customer->getGroup());
        static::assertNotEmpty($customer->getGroup()->getId());

        return $customer;
    }

    /**
     * @depends testCanCreateEntity
     */
    public function testLoadExplicit(Customer $customer): void
    {
        $customerId = $customer->getId();
        static::assertNotNull($customer->getGroup());
        $groupId = $customer->getGroup()->getId();
        $groupKey = $customer->getGroup()->getKey();
        $this->em->clear();

        $customer = $this->em->getRepository(Customer::class)->find($customerId);
        static::assertNotNull($customer);
        $group = $this->em->getRepository(Group::class)->find($groupId);
        static::assertNotNull($group);

        static::assertEquals($customer->getId(), $customerId);
        static::assertEquals($customer->getGroupKey(), $groupKey);
        static::assertEquals($group->getKey(), $groupKey);
        static::assertEquals($customer->getGroupKey(), $group->getKey());
    }

    /**
     * @depends testCanCreateEntity
     */
    public function testDqlJoinQuery(Customer $customer): void
    {
        $customerId = $customer->getId();
        static::assertNotNull($customer->getGroup());
        $groupKey = $customer->getGroup()->getKey();
        $this->em->clear();

        $query = $this->em->createQuery("SELECT p, g FROM Shopware\Models\Customer\Customer p JOIN p.group g WHERE p.id = :customerId");
        $query->setParameter('customerId', $customerId);

        $customer = $query->getOneOrNullResult();
        $group = $customer->getGroup();

        static::assertEquals($customer->getId(), $customerId);
        static::assertEquals($group->getKey(), $groupKey);
        static::assertEquals($customer->getGroupKey(), $groupKey);
        static::assertEquals($customer->getGroupKey(), $group->getKey());
    }

    /**
     * @depends testCanCreateEntity
     */
    public function testDqlFetchEagerQuery(Customer $customer): void
    {
        $customerId = $customer->getId();
        static::assertNotNull($customer->getGroup());
        $groupKey = $customer->getGroup()->getKey();
        $this->em->clear();

        $query = $this->em->createQuery("SELECT p FROM Shopware\Models\Customer\Customer p WHERE p.id = :customerId");
        $query->setFetchMode(Customer::class, 'group', ClassMetadataInfo::FETCH_EAGER);
        $query->setParameter('customerId', $customerId);

        $customer = $query->getOneOrNullResult();
        $group = $customer->getGroup();

        static::assertEquals($customer->getId(), $customerId);
        static::assertEquals($group->getKey(), $groupKey);
        static::assertEquals($customer->getGroupKey(), $groupKey);
        static::assertEquals($customer->getGroupKey(), $group->getKey());
    }

    /**
     * @depends testCanCreateEntity
     */
    public function testDqlLazyQuery(Customer $customer): void
    {
        $customerId = $customer->getId();
        static::assertNotNull($customer->getGroup());
        $groupKey = $customer->getGroup()->getKey();
        $this->em->clear();

        $query = $this->em->createQuery("SELECT p FROM Shopware\Models\Customer\Customer p WHERE p.id = :customerId");
        $query->setParameter('customerId', $customerId);

        $customer = $query->getOneOrNullResult();
        $group = $customer->getGroup();

        static::assertEquals($customer->getId(), $customerId);
        static::assertEquals($group->getKey(), $groupKey);
        static::assertEquals($customer->getGroupKey(), $groupKey);
        static::assertEquals($customer->getGroupKey(), $group->getKey());
    }

    /**
     * @depends testCanCreateEntity
     */
    public function testLazyLoad(Customer $customer): void
    {
        $customerId = $customer->getId();
        static::assertNotNull($customer->getGroup());
        $groupKey = $customer->getGroup()->getKey();
        $this->em->clear();

        $customer = $this->em->getRepository(Customer::class)->find($customerId);
        static::assertNotNull($customer);
        $group = $customer->getGroup();
        static::assertNotNull($group);

        static::assertEquals($customer->getId(), $customerId);
        static::assertEquals($group->getKey(), $groupKey);
        static::assertEquals($customer->getGroupKey(), $groupKey);
        static::assertEquals($customer->getGroupKey(), $group->getKey());
    }

    /**
     * @depends testCanCreateEntity
     */
    public function testCanCreateEntityWithReference(Customer $customer): Customer
    {
        static::assertNotNull($customer->getGroup());
        $groupId = $customer->getGroup()->getId();
        $groupKey = $customer->getGroup()->getKey();
        $this->em->clear();

        $customer = new Customer();
        $group = $this->em->getReference(Group::class, $groupId);
        static::assertNotNull($group);
        $customer->setGroup($group);

        static::assertEmpty($customer->getId());
        static::assertNotNull($customer->getGroup());
        static::assertEquals($groupId, $customer->getGroup()->getId());
        static::assertEquals($groupKey, $customer->getGroup()->getKey());

        return $customer;
    }

    public function testCanCreateEntityWithNewGroup(): Customer
    {
        $this->em->clear();
        $groupKey = Random::getAlphanumericString(5);
        $group = new Group();
        $group->setKey($groupKey);

        $customer = new Customer();
        $customer->setGroup($group);

        static::assertEmpty($customer->getId());
        static::assertNotNull($customer->getGroup());
        static::assertEquals($group, $customer->getGroup());
        static::assertEmpty($customer->getGroup()->getId());
        static::assertEquals($groupKey, $customer->getGroup()->getKey());

        return $customer;
    }

    /**
     * @depends testCanCreateEntity
     */
    public function testCanUpdateEntityWithReference(Customer $customer): Customer
    {
        static::assertNotNull($customer->getGroup());
        $groupId = $customer->getGroup()->getId();
        $groupKey = $customer->getGroup()->getKey();

        $customer = new Customer();
        $customer->setEmail('lazyloadtest2@shopware.com');

        $this->em->persist($customer);
        $this->em->flush();
        $customerId = $customer->getId();

        $this->em->clear();

        $customer = $this->em->find(Customer::class, $customerId);
        static::assertNotNull($customer);
        $group = $this->em->getReference(Group::class, $groupId);
        static::assertNotNull($group);
        $customer->setGroup($group);

        static::assertNotEmpty($customer->getId());
        static::assertNotNull($customer->getGroup());
        static::assertEquals($group, $customer->getGroup());
        static::assertNotEmpty($customer->getGroup()->getId());
        static::assertEquals($groupKey, $customer->getGroup()->getKey());

        return $customer;
    }

    public function testOneToManyLoading(): void
    {
        $supplier = $this->em->find(Supplier::class, 2);
        static::assertNotNull($supplier);
        $productArray = $supplier->getArticles()->toArray();
        static::assertNotEmpty(end($productArray)->getId());
    }

    /**
     * Test LazyLoading for:
     * - \Shopware\Models\Article\Notification::getArticleDetail()
     * - \Shopware\Models\Article\Notification::getCustomer()
     */
    public function testArticleNotification(): void
    {
        $conn = $this->em->getConnection();

        $orderNumber = $conn->fetchOne('SELECT ordernumber FROM s_articles_details');
        $email = $conn->fetchOne('SELECT email FROM s_user');

        $conn->insert('s_articles_notification', [
            'ordernumber' => $orderNumber,
            'mail' => $email,
        ]);

        $id = $conn->lastInsertId();

        $notification = $this->em->getRepository(Notification::class)->find($id);
        static::assertNotNull($notification);
        static::assertEquals($orderNumber, $notification->getArticleDetail()->getNumber());
        static::assertEquals($email, $notification->getCustomer()->getEmail());

        $conn->delete('s_articles_notification', ['id' => $id]);
    }

    /**
     * Test LazyLoading for:
     * - \Shopware\Models\Article\Price::getCustomerGroup()
     */
    public function testArticlePrice(): void
    {
        $price = $this->em->getRepository(ProductPrice::class)->findOneBy(['customerGroupKey' => 'ek']);
        static::assertInstanceOf(ProductPrice::class, $price);
        $group = $price->getCustomerGroup();
        static::assertNotNull($group);
        static::assertEquals('EK', $group->getKey());
    }

    /**
     * Test LazyLoading for:
     * - \Shopware\Models\Article\Configurator\Template\Price::getCustomerGroup()
     */
    public function testTemplatePrice(): void
    {
        $conn = $this->em->getConnection();
        $conn->insert('s_article_configurator_template_prices', [
            'customer_group_key' => 'ek',
        ]);
        $id = $conn->lastInsertId();

        $templatePrice = $this->em->getRepository(ConfiguratorPrice::class)->find($id);
        static::assertNotNull($templatePrice);
        static::assertNotNull($templatePrice->getCustomerGroup());
        static::assertEquals('EK', $templatePrice->getCustomerGroup()->getKey());

        $conn->delete('s_articles_notification', ['id' => $id]);
    }

    /**
     * Test LazyLoading for:
     * - \Shopware\Models\Newsletter\Address::getCustomer()
     */
    public function testNewsletterAddress(): void
    {
        $conn = $this->em->getConnection();
        $email = $conn->fetchOne('SELECT email FROM s_user');
        $conn->insert('s_campaigns_mailaddresses', [
            'email' => $email,
        ]);
        $id = $conn->lastInsertId();

        $address = $this->em->getRepository(Address::class)->find($id);
        static::assertInstanceOf(Address::class, $address);
        static::assertEquals($email, $address->getCustomer()->getEmail());

        $conn->delete('s_campaigns_mailaddresses', ['id' => $id]);
    }

    /**
     * Test LazyLoading for:
     * - \Shopware\Models\Premium\Premium::getArticleDetail()
     */
    public function testPremium(): void
    {
        $premium = $this->em->getRepository(Premium::class)->find(1);
        static::assertNotNull($premium);
        static::assertEquals('SW10209', $premium->getArticleDetail()->getNumber());
    }

    /**
     * Test LazyLoading for:
     * - \Shopware\Models\Newsletter\ContainerType\Article::getArticleDetail()
     */
    public function testArticleContainerType(): void
    {
        $conn = $this->em->getConnection();
        $orderNumber = $conn->fetchOne('SELECT ordernumber FROM s_articles_details ORDER by id');
        $conn->insert('s_campaigns_articles', [
            'articleordernumber' => $orderNumber,
        ]);

        $id = $conn->lastInsertId();

        $productContainerType = $this->em->getRepository(NewsletterProduct::class)->find($id);
        static::assertNotNull($productContainerType);
        static::assertEquals($orderNumber, $productContainerType->getArticleDetail()->getNumber());

        $conn->delete('s_campaigns_articles', ['id' => $id]);
    }
}
