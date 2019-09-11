<?php
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

namespace Shopware\Tests\Functional\Models\CustomerStream;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Api\Resource\CustomerStream as CustomerStreamResource;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Customer\Customer;
use Shopware\Models\CustomerStream\CustomerStream;
use Shopware\Models\CustomerStream\CustomerStreamRepositoryInterface;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class CustomerStreamRepositoryTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    private const TEST_CUSTOMER_MAIL = 'someone@example.com';

    /**
     * @var CustomerStreamRepositoryInterface
     */
    private static $repository;

    /**
     * @var Connection
     */
    private static $dbalConnection;

    /**
     * @var ModelManager
     */
    private static $modelManager;

    /**
     * @var CustomerStreamResource
     */
    private static $customerStreamResource;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$repository = Shopware()->Container()->get('shopware.customer_stream.repository');
        self::$dbalConnection = Shopware()->Container()->get('dbal_connection');
        self::$modelManager = Shopware()->Container()->get('models');
        self::$customerStreamResource = Shopware()->Container()->get('shopware.api.customer_stream');
    }

    /**
     * Entries in s_campaigns_mailaddresses should be taken into account only once per user, when the
     * number of customers per stream is calculated. The streams condition is not important for this testcase.
     *
     * This is a regression test for SW-24307.
     */
    public function testCountDoesNotIncrease(): void
    {
        $stream = (new CustomerStream())->fromArray(self::getCustomerStreamData());
        $customer = (new Customer())->fromArray(self::getCustomerData());

        self::$modelManager->persist($stream);
        self::$modelManager->persist($customer);
        self::$modelManager->flush();

        self::$customerStreamResource->buildSearchIndex(0, true);
        self::$customerStreamResource->indexStream($stream);

        $countBefore = self::$repository->fetchStreamsCustomerCount([$stream->getId()]);
        $customerCountBefore = $countBefore[$stream->getId()]['customer_count'];

        self::$dbalConnection->insert('s_campaigns_mailaddresses', self::getCampaignsEntryData());
        self::$dbalConnection->insert('s_campaigns_mailaddresses', self::getCampaignsEntryData());

        $countAfter = self::$repository->fetchStreamsCustomerCount([$stream->getId()]);
        $customerCountAfter = $countAfter[$stream->getId()]['customer_count'];

        static::assertSame($customerCountBefore, $customerCountAfter);
    }

    private static function getCampaignsEntryData(): array
    {
        return [
            'customer' => 0,
            'groupID' => 1,
            'email' => self::TEST_CUSTOMER_MAIL,
            'lastmailing' => 0,
            'lastread' => 0,
            'added' => date('now'),
            'double_optin_confirmed' => null,
        ];
    }

    private static function getCustomerStreamData(): array
    {
        return [
            'name' => 'Teststream',
            'static' => false,
            'description' => 'Stream created for testing purposes',
            'conditions' => '{"Shopware\\Bundle\\CustomerSearchBundle\\Condition\\HasOrderCountCondition":{"minimumOrderCount":0}}',
            'indexStream' => true,
        ];
    }

    private static function getCustomerData(): array
    {
        return [
            'salutation' => 'mr',
            'firstname' => 'Some',
            'lastname' => 'One',
            'email' => self::TEST_CUSTOMER_MAIL,
            'password' => 'loremIpsumDolorSitAmet',
        ];
    }
}
