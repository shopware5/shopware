<?php declare(strict_types=1);

namespace Shopware\Product\Writer;

use Doctrine\DBAL\Connection;

class SqlGateway
{
    /**
     * @var string
     */
    private $tableName;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->tableName = 'product';
    }

    public function insert(array $data): void
    {
        $this->connection->transactional(function() use ($data) {
            $affectedRows = $this->connection->insert(
                $this->tableName,
                $data
            );

            if(!$affectedRows) {
                throw new ExceptionNoInsertedRecord('Unable to insert data');
            }
        });
    }

    public function update(string $uuid, array $data): void
    {
        $this->connection->transactional(function() use ($uuid, $data) {
            $affectedRows = $this->connection->update(
                $this->tableName,
                $data,
                ['uuid' => $uuid]
            );

            if(0 === $affectedRows) {
                throw new ExceptionNoUpdatedRecord(sprintf('Unable to update "%s" - no rows updated', $uuid));
            }

            if(1 > $affectedRows) {
                throw new ExceptionMultipleUpdatedRecord(sprintf('Unable to update "%s" - multiple rows updated', $uuid));
            }
        });
    }

}