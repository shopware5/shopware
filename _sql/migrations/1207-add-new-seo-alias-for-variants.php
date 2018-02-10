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

class Migrations_Migration1207 extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up($modus)
    {
        $statement = $this->connection->prepare("SELECT * FROM s_core_config_elements WHERE name = 'seoqueryalias'");
        $statement->execute();
        $config = $statement->fetch(PDO::FETCH_ASSOC);

        if (!empty($config)) {
            $value = unserialize($config['value']);
            if (strpos($value, 'variants=') === false) {
                $value .= ',
variants=var';

                $statement = $this->connection->prepare('UPDATE s_core_config_elements SET value = ? WHERE id = ?');
                $statement->execute([serialize($value), $config['id']]);
            }

            $statement = $this->connection->prepare('SELECT * FROM s_core_config_values WHERE element_id = ?');
            $statement->execute([$config['id']]);
            $values = $statement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($values as $shopValue) {
                if (empty($shopValue) || empty($shopValue['value'])) {
                    continue;
                }

                $value = unserialize($shopValue['value']);
                if (strpos($value, 'variants=') !== false) {
                    continue;
                }

                $value .= ',
variants=var';

                $statement = $this->connection->prepare('UPDATE s_core_config_values SET value = ? WHERE id = ?');
                $statement->execute([serialize($value), $shopValue['id']]);
            }
        }
    }
}
