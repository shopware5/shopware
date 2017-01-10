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

namespace Shopware\Components\Plugin;

use DateTime;
use Doctrine\DBAL\Connection;
use Shopware\Models\Plugin\Plugin;

/**
 * Class CronjobSynchronizer
 * @package Shopware\Components\Plugin
 */
class CronjobSynchronizer
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * CronjobSyncronizer constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param Plugin $plugin
     * @param array $cronjobs
     * @throws \InvalidArgumentException
     */
    public function synchronize(Plugin $plugin, array $cronjobs)
    {
        foreach ($cronjobs as $cronjob) {
            $this->addCronjob($plugin, $cronjob);
        }

        $this->removeNotExistingEntries($plugin->getId(), array_column($cronjobs, 'action'));
    }

    /**
     * @param Plugin $plugin
     * @param $cronjob
     */
    private function addCronjob(Plugin $plugin, $cronjob)
    {
        $cronjob['pluginID'] = $plugin->getId();

        // Prevent SQL Error when using reserved word
        if (isset($cronjob['interval'])) {
            $cronjob['`interval`'] = $cronjob['interval'];
            unset($cronjob['interval']);
        }

        $id = $this->connection->fetchColumn('SELECT id FROM s_crontab WHERE `action` = ? AND pluginID = ?', [$cronjob['action'], $plugin->getId()]);

        if ($id) {
            // Don't overwrite user cronjob state
            unset($cronjob['active']);

            $this->connection->update('s_crontab', $cronjob, ['id' => $id]);
        } else {
            $cronjob['next'] = new DateTime();
            $this->connection->insert('s_crontab', $cronjob, ['next' => 'datetime']);
        }
    }

    /**
     * @param int $pluginId
     * @param array $cronjobActions
     */
    private function removeNotExistingEntries($pluginId, array $cronjobActions)
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->delete('s_crontab');
        $builder->where('action NOT IN (:cronjobActions)');
        $builder->andWhere('pluginID = :pluginId');
        $builder->setParameter(':cronjobActions', $cronjobActions, Connection::PARAM_STR_ARRAY);
        $builder->setParameter(':pluginId', $pluginId);
        $builder->execute();
    }
}
