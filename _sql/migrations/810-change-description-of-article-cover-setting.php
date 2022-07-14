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

class Migrations_Migration810 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql(<<<SQL
SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'forceArticleMainImageInListing');

UPDATE `s_core_config_elements`
  SET `label` = 'Immer das Artikel-Vorschaubild anzeigen', `description` = 'z.B. im Listing oder beim Auswahl- und Bildkonfigurator ohne ausgewählte Variante'
  WHERE `id` = @elementId;

UPDATE `s_core_config_element_translations`
  SET `label` = 'Always display the article preview image', `description` = 'e.g. in listings or when using selection or picture configurator with no selected variant'
  WHERE `element_id` = @elementId;
SQL
        );
    }
}
