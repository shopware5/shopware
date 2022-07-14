<?php

class Migrations_Migration1657 extends Shopware\Components\Migrations\AbstractMigration
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
            ->fetchAll(PDO::FETCH_ASSOC)
        ;

        if (empty($blacklistConfig)) {
            return;
        }

        $botList = $this->getFilteredBotList($blacklistConfig[0]['value']);
        $statement = $this->connection->prepare("
            UPDATE s_core_config_elements
            SET value = :value
            WHERE id = :id
        ");
        $statement->execute([
            'value' => $botList,
            'id' => $blacklistConfig[0]['id']
        ]);
    }

    private function fixCustomerBlacklists(): void
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
            $statement = $this->connection->prepare("
                UPDATE s_core_config_values
                SET value = :value
                WHERE id = :id
            ");
            $statement->execute([
                'value' => $botList,
                'id' => $config['id']
            ]);
        }
    }

    private function getFilteredBotList(string $botConfiguration): string
    {
        $botList = explode(';', unserialize($botConfiguration, ['allowed_classes' => false]));
        $botList = array_filter($botList, function ($bot) {
            return $bot !== 'core';
        });

        return serialize(implode(";", $botList));
    }
}
