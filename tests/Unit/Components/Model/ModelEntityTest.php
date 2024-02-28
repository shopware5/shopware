<?php
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

namespace Shopware\Tests\Unit\Components\Model;

use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Article\Article as Product;
use Shopware\Models\Article\Configurator\Template\Template;
use Shopware\Models\Article\Link;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Country\Area;
use Shopware\Models\Country\Country;
use Shopware\Models\Document\Document;
use Shopware\Models\Document\Element;
use Shopware\Models\Tax\Tax;
use Shopware\Models\Voucher\Code;
use Shopware\Models\Voucher\Voucher;

class ModelEntityTest extends TestCase
{
    public function testCanAssignProperties(): void
    {
        $product = new Product();

        $data = [
            'name' => 'foo',
            'description' => 'bar',
        ];

        $product->fromArray($data);

        static::assertSame('foo', $product->getName());
        static::assertSame('bar', $product->getDescription());
    }

    public function testCanReAssignProperties(): void
    {
        $product = new Product();
        $product->setName('lorem');
        $product->setDescription('bar');

        $data = [
            'name' => 'foo',
        ];

        $product->fromArray($data);

        static::assertSame('foo', $product->getName());
        static::assertSame('bar', $product->getDescription());
    }

    public function testCanAssignOneToOne(): void
    {
        $product = new Product();

        $data = [
            'configuratorTemplate' => [
                'active' => true,
                'ean' => 'baz',
            ],
        ];

        $product->fromArray($data);

        static::assertNotNull($product->getConfiguratorTemplate());
        static::assertTrue($product->getConfiguratorTemplate()->getActive());
        static::assertSame('baz', $product->getConfiguratorTemplate()->getEan());

        // configuratorTemplate is the owning side of relation, so article has to be set
        static::assertSame($product, $product->getConfiguratorTemplate()->getArticle());
    }

    public function testLoopsArePreventedOneToOne(): void
    {
        $product = new Product();

        $data = [
            'name' => 'foo',
            'configuratorTemplate' => [
                'ean' => 'foo',
                'article' => [
                    'name' => 'bar',
                ],
            ],
        ];

        $product->fromArray($data);

        static::assertNotNull($product->getConfiguratorTemplate());
        static::assertSame($product, $product->getConfiguratorTemplate()->getArticle());
    }

    public function testCanAssignOneToOneByInstance(): void
    {
        $product = new Product();

        $tax = new Tax();
        $tax->setName('foobar');

        $template = new Template();
        $template->setEan('foo');

        $data = [
            'tax' => $tax,
            'configuratorTemplate' => $template,
        ];

        $product->fromArray($data);

        static::assertSame($tax, $product->getTax());
        static::assertSame($template, $product->getConfiguratorTemplate());
    }

    public function testCanReAssignOneToOne(): void
    {
        $product = new Product();

        $template = new Template();
        $template->setEan('foo');

        $product->setConfiguratorTemplate($template);

        $data = [
            'configuratorTemplate' => [
                'active' => true,
            ],
        ];

        $product->fromArray($data);

        static::assertNotNull($product->getConfiguratorTemplate());
        static::assertTrue($product->getConfiguratorTemplate()->getActive());
        static::assertSame('foo', $product->getConfiguratorTemplate()->getEan());
    }

    public function testCanEmptyArrayDoesNotOverrideOneToOne(): void
    {
        $product = new Product();

        $template = new Template();
        $template->setEan('foo');
        $template->setActive(true);

        $product->setConfiguratorTemplate($template);

        $data = [
            'configuratorTemplate' => [],
        ];

        $product->fromArray($data);

        static::assertNotNull($product->getConfiguratorTemplate());
        static::assertTrue($product->getConfiguratorTemplate()->getActive());
        static::assertSame('foo', $product->getConfiguratorTemplate()->getEan());
    }

    public function testCanRemoveOneToOne(): void
    {
        $product = new Product();
        $product->setName('Dummy');

        $template = new Template();
        $template->setEan('foo');

        $product->setConfiguratorTemplate($template);
        $template->setArticle($product);

        $data = [
            'configuratorTemplate' => null,
        ];

        $product->fromArray($data);

        static::assertNull($product->getConfiguratorTemplate());
        static::assertNull($template->getArticle());
    }

    public function testCanAssignManyToOne(): void
    {
        $product = new Product();

        $data = [
            'supplier' => [
                'name' => 'foo',
            ],
        ];

        $product->fromArray($data);

        $supplier = $product->getSupplier();
        static::assertNotNull($supplier);
        static::assertSame('foo', $supplier->getName());
    }

    public function testCanAssignManyToOneByInstance(): void
    {
        $product = new Product();

        $supplier = new Supplier();
        $supplier->setName('test');

        $data = [
            'supplier' => $supplier,
        ];

        $product->fromArray($data);

        static::assertSame($supplier, $product->getSupplier());
    }

    public function testCanReAssignManyToOne(): void
    {
        $product = new Product();

        $supplier = new Supplier();
        $supplier->setName('test');
        $supplier->setDescription('description');

        $product->setSupplier($supplier);

        static::assertSame($supplier, $product->getSupplier());

        $data = [
            'supplier' => [
                'name' => 'foo',
            ],
        ];

        $product->fromArray($data);

        $supplier = $product->getSupplier();
        static::assertNotNull($supplier);
        static::assertSame('foo', $supplier->getName());

        // 19 tax rate should be preserved
        static::assertSame('description', $supplier->getDescription());
    }

    public function testCanEmptyArrayDoesNotOverrideManyToOne(): void
    {
        $product = new Product();

        $supplier = new Supplier();
        $supplier->setName('test');
        $supplier->setDescription('description');

        $product->setSupplier($supplier);

        static::assertSame($supplier, $product->getSupplier());

        $data = [
            'supplier' => [],
        ];

        $product->fromArray($data);

        $supplier = $product->getSupplier();
        static::assertNotNull($supplier);
        static::assertSame('test', $supplier->getName());
        static::assertSame('description', $supplier->getDescription());
    }

    public function testCanRemoveManyToOne(): void
    {
        $product = new Product();

        $supplier = new Supplier();
        $supplier->setName('test');
        $supplier->setDescription('description');

        $product->setSupplier($supplier);

        static::assertSame($supplier, $product->getSupplier());

        $data = [
            'supplier' => null,
        ];

        $product->fromArray($data);

        static::assertNull($product->getSupplier());
    }

    public function testCanReAssignWithAnotherIdThrowsExceptionManyToOne(): void
    {
        $product = new Product();

        $supplier = new Supplier();
        $supplier->setName('test');
        $supplier->setDescription('description');
        $this->setId($supplier);

        $product->setSupplier($supplier);

        static::assertSame($supplier, $product->getSupplier());

        $data = [
            'supplier' => [
                'id' => '2',
                'name' => 'foo',
            ],
        ];

        $this->expectException(InvalidArgumentException::class);
        $product->fromArray($data);
    }

    public function testCanAssignOneToMany(): void
    {
        $product = new Product();

        $data = [
            'links' => [
                [
                    'id' => 4,
                    'name' => 'batz',
                ],
                [
                    'name' => 'foobar',
                ],
            ],
        ];

        $product->fromArray($data);

        static::assertCount(2, $product->getLinks());
    }

    public function testCanAssignOneToManyByInstance(): void
    {
        $product = new Product();

        $link0 = new Link();
        $link0->setName('dummy');

        $data = [
            'links' => [
                $link0,
                [
                    'name' => 'batz',
                ],
            ],
        ];

        $product->fromArray($data);

        static::assertCount(2, $product->getLinks());

        static::assertContains($link0, $product->getLinks());
    }

    public function testCanOverWriteAssignOneToMany(): void
    {
        $product = new Product();

        $link0 = new Link();
        $link0->setName('dummy');
        $link0->setLink('lorem');

        $product->getLinks()->add($link0);

        static::assertContains($link0, $product->getLinks());

        $data = [
            'links' => [
                [
                    'name' => 'batz',
                ],
            ],
        ];

        $product->fromArray($data);

        static::assertCount(1, $product->getLinks());
        static::assertNotContains($link0, $product->getLinks());

        static::assertSame('batz', $product->getLinks()->current()->getName());
    }

    public function testCanRemoveOneToMany(): void
    {
        $product = new Product();

        $link0 = new Link();
        $link0->setName('dummy');
        $link0->setLink('lorem');

        $product->getLinks()->add($link0);

        static::assertContains($link0, $product->getLinks());

        $data = [
            'links' => null,
        ];

        $product->fromArray($data);

        static::assertCount(0, $product->getLinks());
    }

    public function testCanUpdateOneToManyById(): void
    {
        $product = new Product();

        $link0 = new Link();
        $link0->setName('dummy');
        $link0->setLink('lorem');
        $this->setId($link0);

        $product->getLinks()->add($link0);

        static::assertContains($link0, $product->getLinks());

        $data = [
            'links' => [
                [
                    'id' => 1,
                    'name' => 'batz',
                ],
                [
                    'name' => 'foo',
                ],
            ],
        ];

        $product->fromArray($data);

        static::assertCount(2, $product->getLinks());
        static::assertContains($link0, $product->getLinks());

        static::assertSame('batz', $product->getLinks()->first()->getName());
        static::assertSame('foo', $product->getLinks()->next()->getName());
    }

    public function testCanUpdateOneToMany(): void
    {
        $product = new Product();

        $link0 = new Link();
        $link0->setName('dummy');
        $link0->setLink('lorem');
        $this->setId($link0);

        $product->getLinks()->add($link0);

        static::assertContains($link0, $product->getLinks());

        $data = [
            'links' => [
                [
                    'id' => 2,
                    'name' => 'batz',
                ],
            ],
        ];

        $product->fromArray($data);

        static::assertCount(1, $product->getLinks());
        static::assertNotContains($link0, $product->getLinks());

        static::assertSame('batz', $product->getLinks()->first()->getName());
    }

    public function testCanSetElementsOnDocument(): void
    {
        $element = new Element();
        $element->setName('dummy');

        $document = new Document();
        $document->setElements([$element]);

        static::assertCount(1, $document->getElements());
        static::assertSame('dummy', $document->getElements()->first()->getName());
    }

    public function testCanSetElementsOnDocumentWithArrayCollection(): void
    {
        $element = new Element();
        $element->setName('dummy');

        $document = new Document();
        $document->setElements(new ArrayCollection([$element]));

        static::assertCount(1, $document->getElements());
        static::assertSame('dummy', $document->getElements()->first()->getName());
    }

    public function testCanSetCodesOnVoucher(): void
    {
        $voucher = new Voucher();

        $data = [
            [
                'code' => 'dummy',
            ],
        ];
        $voucher->setCodes($data);

        static::assertCount(1, $voucher->getCodes());
        static::assertSame('dummy', $voucher->getCodes()->first()->getCode());
    }

    public function testCanSetCodesOnVoucherWithArrayCollection(): void
    {
        $code = new Code();
        $code->setCode('dummy');

        $voucher = new Voucher();
        $voucher->setCodes(new ArrayCollection([$code]));

        static::assertCount(1, $voucher->getCodes());
        static::assertSame('dummy', $voucher->getCodes()->first()->getCode());
    }

    public function testCanSetCountriesOnArea(): void
    {
        $country = new Country();
        $country->setName('dummy');

        $area = new Area();
        $area->setCountries([$country]);

        static::assertCount(1, $area->getCountries());
        static::assertSame('dummy', $area->getCountries()->first()->getName());
    }

    protected function setId(ModelEntity $entity): void
    {
        $property = (new ReflectionClass($entity))->getProperty('id');

        $property->setAccessible(true);
        $property->setValue($entity, 1);
    }
}
