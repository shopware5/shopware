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

class Migrations_Migration431 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
UPDATE s_core_config_elements SET value = 's:334:"frontend/listing 3600
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
widgets/campaign 3600";'

WHERE name='cacheControllers';
EOD;
        $this->addSql($sql);
    }
}
