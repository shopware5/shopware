<?php
declare(strict_types=1);

namespace Shopware\Storefront\Session;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

class SaveHandlerFactory
{
    /**
     * @var \PDO
     */
    private $connection;

    /**
     * @var string
     */
    private $table;

    public function __construct(Connection $connection, string $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    public function createSaveHandler(array $sessionOptions): ?\SessionHandlerInterface
    {
        if (empty($sessionOptions['save_handler']) || $sessionOptions['save_handler'] !== 'db') {
            $this->setPhpIniSettings($sessionOptions);

            return null;
        }

        return new PdoSessionHandler(
            $this->connection->getWrappedConnection(),
            [
                'db_table' => $this->table,
                'db_id_col' => 'id',
                'db_data_col' => 'data',
                'db_expiry_col' => 'expiry',
                'db_time_col' => 'modified',
                'db_lifetime_col' => 'lifetime',
                'lock_mode' => $sessionOptions['locking'] ? PdoSessionHandler::LOCK_TRANSACTIONAL : PdoSessionHandler::LOCK_NONE,
            ]
        );
    }

    private function setPhpIniSettings(array $sessionOptions): void
    {
        $sessionOptions = array_filter($sessionOptions);

        foreach ($sessionOptions as $key => $value) {
            ini_set('session.' . $key, $value);
        }
    }
}