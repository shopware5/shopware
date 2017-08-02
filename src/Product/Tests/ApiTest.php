<?php declare(strict_types=1);

namespace Shopware\Product\Tests;

use Doctrine\DBAL\Connection;
use Shopware\Product\Writer\Api\Field;
use Shopware\Product\Writer\Generator;
use Shopware\Product\Writer\SqlGateway;
use Shopware\Product\Writer\Writer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ApiTest extends KernelTestCase
{
    const UUID = 'AA-BB-CC';


    /**
     * @var Connection
     */
    private $connection;

    public function setUp()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();
        $this->connection = $container->get('dbal_connection');

        $this->connection->beginTransaction();
    }

    private function getWriter(): Writer
    {
        return self::$kernel->getContainer()->get('shopware.product.writer');
    }

    public function tearDown()
    {
        $this->connection->rollBack();
        parent::tearDown();
    }

    public function test_gen()
    {
        (new Generator(self::$kernel->getContainer()))->generateAll();
        $this->assertTrue(true);
    }

    public function test_collection()
    {
        self::assertGreaterThan(
            0,
            count(self::$kernel->getContainer()->get('shopware.product.field_collection')->getFields(Field::class))
        );
    }

    public function test_insert()
    {
        $this->getWriter()->insert([
            'uuid' => self::UUID,
            'the_unknown_field' => 'do nothing?',
        ]);

        $product = $this->connection->fetchAssoc('SELECT * FROM product WHERE uuid=:uuid', [
            'uuid' => self::UUID
        ]);

        self::assertSame(self::UUID, $product['uuid']);
    }

    public function test_update()
    {
        $this->getWriter()->insert([
            'uuid' => self::UUID
        ]);

        $this->getWriter()->update(self::UUID, [
            'title' => '_THE_TITLE_',
            'the_unknown_field' => 'do nothing?',
            'description' => '<p>no html</p>',
            'descriptionLong' => '<p>html</p>',
            'availableFrom' => new \DateTime('2011-01-01T15:03:01.012345Z'),
            'availableTo' => new \DateTime('2011-01-01T15:03:01.012345Z'),
            'supplier' => [
                'uuid' => 'SWAG-PRODUCT-SUPPLIER-UUID-1',
            ],
            'mainDetailUuid' => 'SW10003',
        ]);

        $product = $this->connection->fetchAssoc('SELECT * FROM product WHERE uuid=:uuid', ['uuid' => self::UUID]);

        self::assertSame(self::UUID, $product['uuid']);
        self::assertSame('_THE_TITLE_', $product['title']);
        self::assertSame('2011-01-01 15:03:01', $product['available_from']);
        self::assertSame('2011-01-01 15:03:01', $product['available_to']);
        self::assertSame('no html', $product['description']);
        self::assertSame('<p>html</p>', $product['description_long']);
        self::assertSame('SWAG-PRODUCT-SUPPLIER-UUID-1', $product['supplier_uuid']);
        self::assertSame('SW10003', $product['main_detail_uuid']);
    }

    public function test_update_invalid()
    {
        $this->getWriter()->insert([
            'uuid' => self::UUID
        ]);

        $tooLongValue = '';
        for($i = 0; $i < 512; $i++) {
            $tooLongValue .= '#';
        }

        $this->expectException(\InvalidArgumentException::class);
        $this->getWriter()->update(self::UUID, [
            'title' => $tooLongValue,
        ]);
    }

}