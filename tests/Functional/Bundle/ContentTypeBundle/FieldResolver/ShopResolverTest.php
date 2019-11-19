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

namespace Shopware\Tests\Functional\Bundle\ContentTypeBundle\FieldResolver;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\ContentTypeBundle\Field\Shopware\ShopField;
use Shopware\Bundle\ContentTypeBundle\FieldResolver\AbstractResolver;
use Shopware\Bundle\ContentTypeBundle\FieldResolver\ShopResolver;
use Shopware\Bundle\ContentTypeBundle\Structs\Field;

class ShopResolverTest extends TestCase
{
    /**
     * @var AbstractResolver
     */
    private $service;

    /**
     * @var Field
     */
    private $field;

    protected function setUp(): void
    {
        $this->service = Shopware()->Container()->get(ShopResolver::class);
        $this->field = new Field();
        $this->field->setType(new ShopField());
    }

    public function testResolve(): void
    {
        $this->service->add(1, $this->field);
        $this->service->resolve();

        $shop = $this->service->get(1, $this->field);
        static::assertIsArray($shop);
        static::assertEquals(Shopware()->Shop()->getId(), $shop['id']);
    }
}
