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
class Migrations_Migration1633 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'SQL'
UPDATE `s_core_config_elements` 
SET `description` = 'Diese Option aktiviert die Versandkostenberechnung für den Mini- bzw. OffCanvas-Warenkorb. Dies ist nur für Kunden verfügbar, die nicht angemeldet sind.' 
WHERE `name` = 'showShippingCostsOffCanvas';

SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'showShippingCostsOffCanvas' LIMIT 1);

UPDATE `s_core_config_element_translations`
SET `description` = 'Diese Option aktiviert die Versandkostenberechnung für den Mini- bzw. OffCanvas-Warenkorb. Dies ist nur für Kunden verfügbar, die nicht angemeldet sind.'
WHERE `element_id` = @elementId
AND `locale_id` = 1;

UPDATE `s_core_config_element_translations`
SET `description` = 'If enabled, a shipping cost calculation will be displayed in the mini/offcanvas cart page. This is only available for customers who aren\'t logged in.'
WHERE `element_id` = @elementId
AND `locale_id` = 2;
SQL;

        $this->addSql($sql);
    }
}
