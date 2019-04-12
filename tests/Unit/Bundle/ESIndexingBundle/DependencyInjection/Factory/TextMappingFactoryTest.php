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

namespace Shopware\Tests\Unit\Bundle\ESIndexingBundle\DependencyInjection\Factory;

use Elasticsearch\Client;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\ESIndexingBundle\DependencyInjection\Factory\TextMappingFactory;
use Shopware\Bundle\ESIndexingBundle\TextMapping\TextMappingES6;

class TextMappingFactoryTest extends TestCase
{
    public function testReturnsES6WhenESIsNotEnabled()
    {
        $factory = new TextMappingFactory(true, null);

        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();

        $textMapping = $factory->factory($client);

        static::assertInstanceOf(TextMappingES6::class, $textMapping);
    }

    public function testReturnsES6WhenClientReturn6()
    {
        $factory = new TextMappingFactory(true, null);

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()->setMethods(['info'])->getMock();

        $textMapping = $factory->factory($client);

        static::assertInstanceOf(TextMappingES6::class, $textMapping);
    }

    public function testReturnsES6WhenVersionIs6()
    {
        $factory = new TextMappingFactory(true, 6);

        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();

        $textMapping = $factory->factory($client);

        static::assertInstanceOf(TextMappingES6::class, $textMapping);
    }
}
