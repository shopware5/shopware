<?php declare(strict_types=1);

namespace Shopware\Product\Tests;



use Doctrine\DBAL\Connection;
use Shopware\Product\Writer\Generator;
use Shopware\Product\Writer\SqlGateway;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ApiTest extends KernelTestCase
{
    const UUID = 'AA-BB-CC';

    /**
     * @var SqlGateway
     */
    private $writer;

    /**
     * @var Connection
     */
    private $connection;

    public function setUp()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();
        $this->writer = $container->get('shopware.product.writer');
        $this->connection = $container->get('dbal_connection');

        $this->connection->beginTransaction();
    }

    public function tearDown()
    {
        $this->connection->rollBack();
        parent::tearDown();
    }

    public function test_gen()
    {
        (new Generator(self::$kernel->getContainer()))->generate();
        $this->assertTrue(true);
    }

    public function test_collection()
    {
        self::assertGreaterThan(
            0,
            count(self::$kernel->getContainer()->get('shopware.product.field_collection')->getFields())
        );
    }

    public function test_insert()
    {
        $this->writer->insert([
            'uuid' => self::UUID
        ]);

        $product = $this->connection->fetchAssoc('SELECT * FROM product WHERE uuid=:uuid', ['uuid' => self::UUID]);

        self::assertSame(self::UUID, $product['uuid']);
    }

    public function test_update()
    {
        $this->writer->insert([
            'uuid' => self::UUID
        ]);

        $this->writer->update(self::UUID, [
            'title' => '_THE_TITLE_',
            'the_unknown_field' => 'do nothing?',
            'availableFrom' => new \DateTime('2011-01-01T15:03:01.012345Z'),
            'availableTo' => new \DateTime('2011-01-01T15:03:01.012345Z'),
        ]);

        $product = $this->connection->fetchAssoc('SELECT * FROM product WHERE uuid=:uuid', ['uuid' => self::UUID]);

        self::assertSame(self::UUID, $product['uuid']);
        self::assertSame('_THE_TITLE_', $product['title']);
        self::assertSame('2011-01-01 15:03:01', $product['available_from']);
        self::assertSame('2011-01-01 15:03:01', $product['available_to']);
    }

}