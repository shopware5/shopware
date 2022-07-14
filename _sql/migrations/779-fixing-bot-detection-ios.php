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

class Migrations_Migration779 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->fixStandardBlacklist();

        $this->fixCustomerBlacklists();
    }

    private function fixStandardBlacklist()
    {
        $blacklistConfig = $this->connection
            ->query("SELECT * FROM s_core_config_elements WHERE name = 'botBlackList'")
            ->fetchAll(PDO::FETCH_ASSOC)
        ;

        if (empty($blacklistConfig)) {
            return;
        }

        $botList = $this->getFilteredBotList($blacklistConfig[0]['value']);
        $statement = $this->connection->prepare('
            UPDATE s_core_config_elements
            SET value = :value 
            WHERE id = :id
        ');
        $statement->execute([
            'value' => $botList,
            'id' => $blacklistConfig[0]['id'],
        ]);
    }

    private function fixCustomerBlacklists()
    {
        $blacklistConfig = $this->connection
            ->query("SELECT v.* FROM s_core_config_values as v INNER JOIN s_core_config_elements as e on v.element_id = e.id WHERE e.name = 'botBlackList'")
            ->fetchAll(PDO::FETCH_ASSOC)
        ;

        if (empty($blacklistConfig)) {
            return;
        }

        foreach ($blacklistConfig as $config) {
            $botList = $this->getFilteredBotList($config['value']);
            $statement = $this->connection->prepare('
                UPDATE s_core_config_values
                SET value = :value 
                WHERE id = :id
            ');
            $statement->execute([
                'value' => $botList,
                'id' => $config['id'],
            ]);
        }
    }

    /**
     * @param string $botConfiguration
     *
     * @return string
     */
    private function getFilteredBotList($botConfiguration)
    {
        $botList = explode(';', unserialize($botConfiguration));
        $botList = array_filter($botList, function ($bot) {
            return $bot !== 'legs';
        });

        return serialize(implode(';', $botList));
    }
}
