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

class Migrations_Migration788 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     *
     * @return void
     */
    public function up($modus)
    {
        $sql = <<<'EOD'
           UPDATE `s_core_config_elements` SET `label` = 'prev/next-Tag auf paginierten Seiten benutzen',
           `description` = 'Wenn aktiv, wird auf paginierten Seiten anstatt des Canoncial-Tags der prev/next-Tag benutzt.'
           WHERE `s_core_config_elements`.`name` = 'seoIndexPaginationLinks';
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
           SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'seoIndexPaginationLinks' LIMIT 1);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
           UPDATE `s_core_config_element_translations` SET `label` = 'Use prev/next-tag on paginated sites',
           `description` = 'If active, use prev/next-tag instead of the Canoncial-tag on paginated sites'
           WHERE `element_id` = @elementId;
EOD;
        $this->addSql($sql);
    }
}
