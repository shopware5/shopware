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

namespace Shopware\Tests\Functional\Bundle\ContentTypeBundle\FieldResolver;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\ContentTypeBundle\Field\Shopware\ProductField;
use Shopware\Bundle\ContentTypeBundle\Field\Shopware\ProductGrid;
use Shopware\Bundle\ContentTypeBundle\FieldResolver\ProductResolver;
use Shopware\Bundle\ContentTypeBundle\Structs\Field;
use Shopware\Tests\Functional\Traits\ContainerTrait;

class ProductResolverTest extends TestCase
{
    use ContainerTrait;

    private ProductResolver $productResolver;

    private Connection $connection;

    private Field $field;

    protected function setUp(): void
    {
        $this->productResolver = $this->getContainer()->get(ProductResolver::class);
        $this->connection = $this->getContainer()->get(Connection::class);
        $this->field = new Field();
        $this->field->setType(new ProductField());
    }

    public function testResolve(): void
    {
        $id = $this->connection->fetchOne('SELECT ordernumber FROM s_articles_details ORDER BY id LIMIT 1');

        $this->productResolver->add($id, $this->field);
        $this->productResolver->resolve();

        $product = $this->productResolver->get($id, $this->field);
        static::assertIsArray($product);
        static::assertEquals($id, $product['ordernumber']);
    }

    public function testMultiResolve(): void
    {
        $idsArray = $this->connection->executeQuery('SELECT ordernumber FROM s_articles_details ORDER BY id LIMIT 5')->fetchFirstColumn();
        $ids = implode('|', $idsArray);
        $this->field->setType(new ProductGrid());

        $this->productResolver->add($ids, $this->field);
        $this->productResolver->resolve();

        $products = $this->productResolver->get($ids, $this->field);
        static::assertCount(\count($idsArray), $products);
    }
}
