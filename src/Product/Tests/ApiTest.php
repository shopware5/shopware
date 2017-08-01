<?php declare(strict_types=1);

namespace Shopware\Product\Tests;



use Doctrine\DBAL\Connection;
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
//            'available_from' => new \DateTime('2011-01-01T15:03:01.012345Z'),
//            'available_to' => new \DateTime('2011-01-01T15:03:01.012345Z'),
        ]);

        $product = $this->connection->fetchAssoc('SELECT * FROM product WHERE uuid=:uuid', ['uuid' => self::UUID]);

        self::assertSame(self::UUID, $product['uuid']);
        self::assertSame('_THE_TITLE_', $product['title']);
    }

}