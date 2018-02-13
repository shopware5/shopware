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

class Migrations_Migration1211 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql(<<<SQL
SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'forceArticleMainImageInListing');

UPDATE `s_core_config_elements`
  SET `description` = 'z.B. im Listing oder beim Auswahl- und Bildkonfigurator ohne ausgewählte Variante. Wichtig: Bei Variantenfilterung auf aufgefächerten Variantengruppen wird diese Option nicht beachtet.'
  WHERE `id` = @elementId;

UPDATE `s_core_config_element_translations`
  SET `description` = 'e.g. in listings or when using selection or picture configurator with no selected variant. Important: If you filter on expanded variant groups, this configuration will be ignored.'
  WHERE `element_id` = @elementId AND locale_id = 2;
SQL
        );
    }
}
