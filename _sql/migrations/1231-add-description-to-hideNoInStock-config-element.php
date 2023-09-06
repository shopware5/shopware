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

class Migrations_Migration1231 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<SQL
        SET @formId = (SELECT id FROM s_core_config_forms WHERE name = 'Frontend79' LIMIT 1);
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
        SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'hideNoInStock' LIMIT 1);
SQL;
        $this->addSql($sql);

        $sql = <<<'EOD'
UPDATE s_core_config_elements SET
`description` = 'Falls inaktiv, kann es zu längeren Ladezeiten im Listing kommen, wenn die aufgefächerte Variantenfilterung genutzt wird. Bei Nutzung von ElasticSearch tritt dieser Effekt nicht auf.'
WHERE `id` = @elementId;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
UPDATE s_core_config_element_translations SET
`description` = 'If inactive, the listing may take longer to load if the split variant filtering is used. This effect does not occur when using ElasticSearch.'
WHERE `element_id` = @elementId and locale_id = 2;
EOD;
        $this->addSql($sql);
    }
}
