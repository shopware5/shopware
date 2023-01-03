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

namespace Shopware\Tests\Functional\Bundle\StoreFrontBundle\Gateway\Dbal;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Gateway\CountryGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\CountryGateway;
use Shopware\Bundle\StoreFrontBundle\Struct\Country;
use Shopware\Bundle\StoreFrontBundle\Struct\Currency;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestContext;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class CountryGatewayTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    public function testGetFallbackCountry(): void
    {
        $countryGateway = $this->getCountryGateway();

        $shop = new Shop();
        $shop->setId(1);

        $testContext = new TestContext('', $shop, new Currency(), new Group(), new Group(), [], []);

        $country = $countryGateway->getFallbackCountry($testContext);

        static::assertInstanceOf(Country::class, $country);
        static::assertTrue($country->isActive());
        static::assertTrue($country->allowShipping());
        static::assertEquals('Deutschland', $country->getName());
    }

    public function testGetFallbackCountryDoesRecognizeTranslations(): void
    {
        $sqlFile = file_get_contents(__DIR__ . '/fixtures/country.sql');
        static::assertIsString($sqlFile);
        $this->getContainer()->get(Connection::class)->executeStatement($sqlFile);

        $countryGateway = $this->getCountryGateway();

        $shop = new Shop();
        $shop->setId(1);

        $testContext = new TestContext('', $shop, new Currency(), new Group(), new Group(), [], []);

        $country = $countryGateway->getFallbackCountry($testContext);

        static::assertInstanceOf(Country::class, $country);
        static::assertTrue($country->isActive());
        static::assertTrue($country->allowShipping());
        static::assertEquals('Österreich', $country->getName());
    }

    public function getCountryGateway(): CountryGateway
    {
        return $this->getContainer()->get(CountryGatewayInterface::class);
    }
}
