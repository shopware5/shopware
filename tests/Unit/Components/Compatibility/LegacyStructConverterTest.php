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

namespace Shopware\Tests\Unit\Components\Compatibility;

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
use Shopware_Components_Config;

class LegacyStructConverterTest extends TestCase
{
    public function testConvertListProductStruct(): void
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

    private function createConverter(): LegacyStructConverter
    {
        $config = $this->createMock(Shopware_Components_Config::class);
        $config->method('get')->willReturnMap([
            ['useShortDescriptionInListing', null, true],
        ]);

        $eventManager = $this->createMock(ContainerAwareEventManager::class);
        $eventManager->method('filter')->willReturnArgument(1);

        return new LegacyStructConverter(
            $config,
            $this->createMock(ContextServiceInterface::class),
            $eventManager,
            $this->createMock(MediaServiceInterface::class),
            $this->createMock(Connection::class),
            $this->createMock(ModelManager::class),
            $this->createMock(CategoryServiceInterface::class),
            $this->createMock(Container::class)
        );
    }
}
