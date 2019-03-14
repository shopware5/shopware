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

namespace Shopware\Components\Register;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Shopware_Components_Config;

class RegistrationCleanupService implements RegistrationCleanupServiceInterface
{
    /**
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(\Shopware_Components_Config $config, Connection $connection)
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

            $queryBuilder->delete('s_core_optin')
                ->where('type = "swRegister"')
                ->andWhere('datum < NOW() - INTERVAL :interval DAY')
                ->setParameter(':interval', $interval)
                ->execute();

            $queryBuilder->delete('s_user')
                ->where('doubleOptinEmailSentDate < NOW() - INTERVAL :interval DAY')
                ->andWhere('doubleOptinConfirmDate IS NULL')
                ->andWhere('doubleOptinRegister = true')
                ->andWhere('active = 0')
                ->setParameter(':interval', $interval)
                ->execute();

            $this->connection->commit();
        } catch (DBALException $exp) {
            return false;
        }

        return true;
    }
}
