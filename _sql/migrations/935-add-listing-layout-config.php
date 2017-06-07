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

class Migrations_Migration935 extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up($modus)
    {
        $tags = implode("\n", [
            'frontend/listing 3600',
            'frontend/index 3600',
            'frontend/detail 3600',
            'frontend/campaign 14400',
            'widgets/listing 14400',
            'frontend/custom 14400',
            'frontend/sitemap 14400',
            'frontend/blog 14400',
            'widgets/index 3600',
            'widgets/checkout 3600',
            'widgets/compare 3600',
            'widgets/emotion 14400',
            'widgets/recommendation 14400',
            'widgets/lastArticles 3600',
            'widgets/campaign 3600',
            'frontend/listing/layout 0',
        ]);

        $this->addSql(sprintf(
            "UPDATE `s_core_config_elements` SET `value` = '%s' WHERE `name` = 'cacheControllers'",
            serialize($tags)
        ));

        if ($modus == self::MODUS_INSTALL) {
            return;
        }

        $values = $this->connection->query("SELECT v.id, v.value FROM s_core_config_values v INNER JOIN s_core_config_elements e ON e.id = v.element_id WHERE e.name = 'cacheControllers'")
            ->fetchAll(PDO::FETCH_ASSOC);

        foreach ($values as $row) {
            $value = unserialize($row['value']);
            $value = explode("\n", $value);
            $value[] = 'frontend/listing/layout 0';
            $value = implode("\n", $value);
            $value = serialize($value);

            $sql = sprintf("UPDATE `s_core_config_values` SET `value` = '%s' WHERE id = " . (int) $row['id'], $value);
            $this->addSql($sql);
        }
    }
}
