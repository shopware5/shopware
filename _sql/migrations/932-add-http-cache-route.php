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

class Migrations_Migration932 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $tags = implode("\n", [
            'widgets/lastArticles detail',
            'widgets/checkout checkout,slt',
            'widgets/compare compare',
        ]);

        $this->addSql(sprintf(
            "UPDATE `s_core_config_elements` SET `value` = '%s' WHERE `name` = 'noCacheControllers'",
            serialize($tags)
        ));

        if ($modus == AbstractMigration::MODUS_INSTALL) {
            return;
        }

        $values = $this->connection->query(
            "SELECT configValues.*
             FROM s_core_config_values configValues
             INNER JOIN s_core_config_elements elements
             ON elements.id = configValues.element_id
             AND elements.name = 'noCacheControllers'"
        )->fetchAll(PDO::FETCH_ASSOC);

        foreach ($values as $value) {
            $controllers = unserialize($value['value']);
            $controllers = explode("\n", $controllers);

            foreach ($controllers as &$controller) {
                if ($controller === 'widgets/checkout checkout') {
                    $controller = 'widgets/checkout checkout,slt';
                }
            }

            $this->addSql(sprintf(
                "UPDATE s_core_config_values SET value = '%s' WHERE id = " . $value['id'],
                serialize(implode("\n", $controllers))
            ));
        }
    }
}
