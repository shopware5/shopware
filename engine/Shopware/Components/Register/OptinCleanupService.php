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

namespace Shopware\Components\Register;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use PDO;
use Shopware_Components_Config;

class OptinCleanupService implements OptinCleanupServiceInterface
{
    /**
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Shopware_Components_Config $config, Connection $connection)
    {
        $this->config = $config;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanup()
    {
        $interval = max($this->config->get('optintimetodelete'), 1);

        try {
            $this->connection->beginTransaction();
            $queryBuilder = $this->connection->createQueryBuilder();

            $ids = $queryBuilder->select('optin.id')
                ->from('s_core_optin', 'optin')
                ->where('type != "swRegister"')
                ->andWhere('type LIKE "sw%"')
                ->andWhere('datum < NOW() - INTERVAL :interval DAY')
                ->setParameter(':interval', $interval)
                ->execute()
                ->fetchAll(PDO::FETCH_COLUMN);

            if (!$ids) {
                $this->connection->commit();

                return false;
            }

            $queryBuilder = $this->connection->createQueryBuilder();
            $queryBuilder->delete('s_core_optin')
                ->where('id IN (:groupIds)')
                ->setParameter(':groupIds', $ids, Connection::PARAM_INT_ARRAY)
                ->execute();

            $queryBuilder = $this->connection->createQueryBuilder();
            $queryBuilder->update('s_user', 'user')
                ->set('register_opt_in_id', 'NULL')
                ->where('register_opt_in_id IN (:groupIds)')
                ->setParameter(':groupIds', $ids, Connection::PARAM_INT_ARRAY)
                ->execute();

            $this->connection->commit();
        } catch (DBALException $exp) {
            $this->connection->rollBack();

            return false;
        }

        return true;
    }
}
