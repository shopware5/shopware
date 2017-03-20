<?php

namespace Shopware\Framework\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;

class ConnectionFactory extends \Doctrine\Bundle\DoctrineBundle\ConnectionFactory
{
    /**
     * @var \PDO
     */
    private $connection;

    public function __construct(array $typesConfig, \PDO $connection = null)
    {
        parent::__construct($typesConfig);

        $this->connection = $connection;
    }

    /**
     * @inheritDoc
     */
    public function createConnection(
        array $params,
        Configuration $config = null,
        EventManager $eventManager = null,
        array $mappingTypes = []
    ) {
        $params['pdo'] = $this->connection;

        return parent::createConnection(
            $params,
            $config,
            $eventManager,
            $mappingTypes
        );
    }

}