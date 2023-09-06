<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Unit\Components\Compatibility;

use DateTime;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\CategoryServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Price;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule;
use Shopware\Bundle\StoreFrontBundle\Struct\Tax;
use Shopware\Components\Compatibility\LegacyStructConverter;
use Shopware\Components\ContainerAwareEventManager;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Model\ModelManager;
use Shopware\Tests\Functional\Traits\ShopContextTrait;
use Shopware_Components_Config;

class LegacyStructConverterTest extends TestCase
{
    use ShopContextTrait;

    public function testConvertListProductStructWithEmptyDescription(): void
    {
        $converter = $this->createConverter();

        $product = new ListProduct(1, 1, 'SW1000');
        $product->setTax(new Tax());

        $priceRule = new PriceRule();
        $priceRule->setCustomerGroup(new Group());
        $product->setListingPrice(new Price($priceRule));

        $convertedProduct = $converter->convertListProductStruct($product);

        static::assertNull($convertedProduct['description']);
    }

    public function testConvertPriceStructCreatesRegulationPrice(): void
    {
        $converter = $this->createConverter();

        $price = new Price(new PriceRule());
        $price->setCalculatedRegulationPrice(400.0);

        $data = $converter->convertPriceStruct($price);

        static::assertEquals(400.0, $data['regulationPrice']);
    }

    public function testConvertListProductStructWithReleaseDate(): void
    {
        $converter = $this->createConverter();

        $product = new ListProduct(1, 1, 'SW1000');
        $product->setTax(new Tax());
        $tomorrow = new DateTime('tomorrow');
        $product->setReleaseDate($tomorrow);

        $priceRule = new PriceRule();
        $priceRule->setCustomerGroup(new Group());
        $product->setListingPrice(new Price($priceRule));

        $convertedProduct = $converter->convertListProductStruct($product);

        $tomorrowString = $tomorrow->format('Y-m-d');
        static::assertSame($tomorrowString, $convertedProduct['sReleasedate']);
        static::assertSame($tomorrowString, $convertedProduct['sReleaseDate']);
    }

    private function createConverter(): LegacyStructConverter
    {
        $config = $this->createMock(Shopware_Components_Config::class);
        $config->method('get')->willReturnMap([
            ['useShortDescriptionInListing', null, true],
        ]);

        $contextService = $this->createMock(ContextServiceInterface::class);
        $contextService->method('getShopContext')->willReturn($this->createShopContext());

        $eventManager = $this->createMock(ContainerAwareEventManager::class);
        $eventManager->method('filter')->willReturnArgument(1);

        return new LegacyStructConverter(
            $config,
            $contextService,
            $eventManager,
            $this->createMock(MediaServiceInterface::class),
            $this->createMock(Connection::class),
            $this->createMock(ModelManager::class),
            $this->createMock(CategoryServiceInterface::class),
            $this->createMock(Container::class)
        );
    }
}
