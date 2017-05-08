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

class Migrations_Migration927 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
UPDATE s_core_config_elements SET value = 's:360:"frontend/listing 3600
frontend/index 3600
frontend/detail 3600
frontend/campaign 14400
widgets/listing 14400
frontend/custom 14400
frontend/sitemap 14400
frontend/blog 14400
widgets/index 3600
widgets/checkout 3600
widgets/compare 3600
widgets/emotion 14400
widgets/recommendation 14400
widgets/lastArticles 3600
widgets/campaign 3600
frontend/listing/layout 0";'
WHERE name='cacheControllers';
EOD;
        $this->addSql($sql);

        $values = $this->connection->query(
            "SELECT configValues.*
             FROM s_core_config_values configValues
             INNER JOIN s_core_config_elements elements
             ON elements.id = configValues.element_id
             AND elements.name = 'cacheControllers'"
        )->fetchAll(PDO::FETCH_ASSOC);

        foreach ($values as $value) {
            $controllers = unserialize($value['value']);
            $controllers = explode("\n", $controllers);
            $controllers[] = 'frontend/listing/layout 0';
            $controllers = serialize($controllers);
            $this->addSql("UPDATE s_core_config_values SET value = '" . $controllers . "' WHERE id = " . $value['id']);
        }
    }
}
