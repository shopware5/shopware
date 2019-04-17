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

class Migrations_Migration1460 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $options = [
            'editable' => false,
            'forceSelection' => true,
            'translateUsingSnippets' => true,
            'namespace' => 'backend/application/main',
            'store' => [
                [
                    0,
                    [
                        'snippet' => 'shipping_calculations_not_show',
                        'en_GB' => 'No',
                        'de_DE' => 'Nein'
                    ],
                ],
                [
                    1,
                    [
                        'snippet' => 'shipping_calculations_show_folded',
                        'en_GB' => 'Collapsed',
                        'de_DE' => 'Eingeklappt'
                    ]
                ],
                [
                    2,
                    [
                        'snippet' => 'shipping_calculations_show_expanded',
                        'en_GB' => 'Expanded',
                        'de_DE' => 'Ausgeklappt'
                    ]
                ]
            ]
        ];

        $sql = <<<'SQL'
        SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Frontend79' LIMIT 1);
        
        INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`) VALUES
        (NULL, @parent, 'showShippingCostsOffCanvas', 'i:1;', 'Versandkostenberechnung im Mini-/OffCanvas-Warenkorb anzeigen', 'Diese Option aktiviert die Versandkostenberechnung für den Mini- bzw. OffCanvas-Warenkorb.', 'select', 0, 6, 1, '%s');
        
        SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'showShippingCostsOffCanvas' LIMIT 1);
		INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
        VALUES (@elementId, '2', 'Show shipping costs calculation in mini/offcanvas shopping cart', 'If enabled, a shipping cost calculator will be displayed in the mini/offcanvas cart page. This is only available for customers who haven\'t logged in');

SQL;
        $this->addSql(sprintf($sql, serialize($options)));

        if ($modus === self::MODUS_UPDATE) {
            $sql = "INSERT INTO `s_core_config_values` (`element_id`, `shop_id`, `value`)
                    SELECT 
                      @elementId,
                      `id`,
                      'i:0;'
                    FROM s_core_shops
                    WHERE id NOT IN (SELECT `shop_id` FROM `s_core_config_values` WHERE `element_id` = @elementId)";
            $this->addSql($sql);
        }
    }
}
