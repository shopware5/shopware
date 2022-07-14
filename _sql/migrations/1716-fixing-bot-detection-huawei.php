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

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration1716 extends AbstractMigration
{
    public function up($modus): void
    {
        $this->fixStandardBlacklist();

        $this->fixCustomerBlacklists();
    }

    private function fixStandardBlacklist(): void
    {
        $blacklistConfig = $this->connection
            ->query("SELECT * FROM s_core_config_elements WHERE name = 'botBlackList'")
            ->fetchAll(PDO::FETCH_ASSOC);

        if (empty($blacklistConfig)) {
            return;
        }

        $botList = $this->getFilteredBotList($blacklistConfig[0]['value']);
        $statement = $this->connection->prepare(
            'UPDATE s_core_config_elements
             SET value = :value
             WHERE id = :id'
        );
        $statement->execute([
            'value' => $botList,
            'id' => $blacklistConfig[0]['id'],
        ]);
    }

    private function fixCustomerBlacklists(): void
    {
        $blacklistConfig = $this->connection
            ->query(
                "SELECT v.*
                 FROM s_core_config_values as v
                 INNER JOIN s_core_config_elements as e on v.element_id = e.id
                 WHERE e.name = 'botBlackList'"
            )
            ->fetchAll(PDO::FETCH_ASSOC);

        if (empty($blacklistConfig)) {
            return;
        }

        foreach ($blacklistConfig as $config) {
            $botList = $this->getFilteredBotList($config['value']);
            $statement = $this->connection->prepare(
                'UPDATE s_core_config_values
                 SET value = :value
                 WHERE id = :id'
            );
            $statement->execute([
                'value' => $botList,
                'id' => $config['id'],
            ]);
        }
    }

    private function getFilteredBotList(string $botConfiguration): string
    {
        $botList = explode(';', unserialize($botConfiguration, ['allowed_classes' => false]));
        $botList = array_filter($botList, function ($bot) {
            return $bot !== 'core';
        });

        return serialize(implode(';', $botList));
    }
}
