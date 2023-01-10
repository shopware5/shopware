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

namespace Shopware\Tests\Unit\Bundle\StoreFrontBundle\Service\Core;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\CountryGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\CurrencyGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\CustomerGroupGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\PriceGroupDiscountGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\ShopGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\TaxGateway;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ShopContextFactory;
use Shopware\Bundle\StoreFrontBundle\Struct\Country;
use Shopware\Bundle\StoreFrontBundle\Struct\Country\Area;
use Shopware\Bundle\StoreFrontBundle\Struct\Currency;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;

class ShopContextFactoryTest extends TestCase
{
    public function testShouldCreateShopContextWithFallbackCountryAndArea(): void
    {
        $customerGroupGatewayMock = $this->getMockBuilder(CustomerGroupGateway::class)->disableOriginalConstructor()->getMock();
        $customerGroupGatewayMock->method('getList')->willReturn(['EK' => new Group()]);

        $taxGatewayMock = $this->getMockBuilder(TaxGateway::class)->disableOriginalConstructor()->getMock();
        $taxGatewayMock->expects(static::once())->method('getRules')->willReturn([]);

        $countryGatewayMock = $this->getMockBuilder(CountryGateway::class)->disableOriginalConstructor()->setMethods(['getFallbackCountry', 'getArea'])->getMock();
        $country = new Country();
        $country->setAreaId(1);

        $countryGatewayMock->expects(static::once())->method('getFallbackCountry')->willReturn($country);
        $countryGatewayMock->expects(static::once())->method('getArea')->willReturn(new Area());

        $priceGroupDiscountGatewayMock = $this->getMockBuilder(PriceGroupDiscountGateway::class)->disableOriginalConstructor()->getMock();
        $priceGroupDiscountGatewayMock->expects(static::once())->method('getPriceGroups')->willReturn([]);

        $shop = new Shop();
        $shop->setCurrency(new Currency());

        $shopGatewayMock = $this->getMockBuilder(ShopGateway::class)->disableOriginalConstructor()->getMock();
        $shopGatewayMock->method('get')->willReturn($shop);
        $currencyGateway = $this->getMockBuilder(CurrencyGateway::class)->disableOriginalConstructor()->getMock();

        $shopContextFactory = new ShopContextFactory(
            $customerGroupGatewayMock,
            $taxGatewayMock,
            $countryGatewayMock,
            $priceGroupDiscountGatewayMock,
            $shopGatewayMock,
            $currencyGateway
        );

        $shopContext = $shopContextFactory->create('', 1);

        static::assertInstanceOf(Country::class, $shopContext->getCountry());
        static::assertInstanceOf(Area::class, $shopContext->getArea());
    }
}
