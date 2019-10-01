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

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundleDBAL;

use Shopware\Bundle\CustomerSearchBundle\BaseCustomer;
use Shopware\Bundle\CustomerSearchBundle\Condition\HasAddressWithCountryCondition;
use Shopware\Bundle\CustomerSearchBundle\CustomerNumberSearchResult;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class CustomerNumberSearchTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    private const COUNTRY_ID_GERMANY = 2;
    private const COUNTRY_ID_NETHERLANDS = 21;
    private const ADDRESS_COUNTRY_IDS = [self::COUNTRY_ID_GERMANY, self::COUNTRY_ID_GERMANY, self::COUNTRY_ID_NETHERLANDS];
    private const CUSTOMERNUMBERS = ['lorem', 'ipsum', 'dolor', 'sit', 'amet'];

    /**
     * Assert, that a search using no condition returns exactly the expected customers
     * and no duplicates.
     */
    public function testSearchWithoutCondition(): void
    {
        $result = $this->search(
            new Criteria(),
            self::CUSTOMERNUMBERS,
            array_map(['self', 'customerFromNumber'], self::CUSTOMERNUMBERS)
        );

        self::assertSearchResultEqualsInput($result);
    }

    /**
     * Assert, that a search using the HasAddressWithCountryCondition returns exactly
     * the expected customers and no duplicates.
     *
     * This is a regression test for SW-24307.
     */
    public function testSearchWithHasAddressWithCountryCondition(): void
    {
        $criteria = new Criteria();
        $criteria->addCondition(new HasAddressWithCountryCondition([self::COUNTRY_ID_GERMANY]));

        $result = $this->search(
            $criteria,
            self::CUSTOMERNUMBERS,
            array_map(['self', 'customerFromNumber'], self::CUSTOMERNUMBERS)
        );

        self::assertSearchResultEqualsInput($result);
    }

    private static function assertSearchResultEqualsInput(CustomerNumberSearchResult $result): void
    {
        static::assertCount(count(self::CUSTOMERNUMBERS), $result->getCustomers());
        static::assertCount(0, array_diff(self::CUSTOMERNUMBERS, self::getCustomerNumbers($result)));
    }

    private static function getCustomerNumbers(CustomerNumberSearchResult $searchResult): array
    {
        return array_map(static function ($customer) {
            /* @var BaseCustomer $customer */
            return $customer->getNumber();
        }, $searchResult->getCustomers());
    }

    private static function customerFromNumber(string $customerNumber = 'none'): array
    {
        return [
            'number' => $customerNumber,
            'email' => sprintf('%s@example.com', $customerNumber),
            'active' => true,
            'addresses' => array_map(static function ($id) {
                return ['country_id' => $id];
            }, self::ADDRESS_COUNTRY_IDS),
        ];
    }
}
