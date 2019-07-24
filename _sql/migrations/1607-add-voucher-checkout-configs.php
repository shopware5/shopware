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

class Migrations_Migration1607 extends Shopware\Components\Migrations\AbstractMigration
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
                        'snippet' => 'voucher_mode_not_show',
                        'en_GB' => 'No',
                        'de_DE' => 'Nein'
                    ],
                ],
                [
                    1,
                    [
                        'snippet' => 'voucher_mode_show_folded',
                        'en_GB' => 'Collapsed',
                        'de_DE' => 'Eingeklappt'
                    ]
                ],
                [
                    2,
                    [
                        'snippet' => 'voucher_mode_show_expanded',
                        'en_GB' => 'Expanded',
                        'de_DE' => 'Ausgeklappt'
                    ]
                ]
            ]
        ];

        $sql = <<<'SQL'
        SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Checkout' LIMIT 1);

        INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`) VALUES
        (NULL, @parent, 'showVoucherModeForCheckout', 'i:2;', 'Gutscheinfeld im Bestellabschluss anzeigen', NULL, 'select', 0, 0, 0, '%s');

        SET @voucherModeElementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'showVoucherModeForCheckout' LIMIT 1);
        INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`)
        VALUES (@voucherModeElementId, '2', 'Display voucher field on checkout page');

        SET @commentArticleElementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'commentVoucherArticle'); 
        UPDATE `s_core_config_elements` SET `description` = 'Artikel hinzuf&uuml;gen, Kommentarfunktion', name = 'commentArticle' WHERE id = @commentArticleElementId;
        UPDATE `s_core_config_element_translations` SET description = 'Add product, comment function' WHERE element_id = @commentArticleElementId;
SQL;
        $this->addSql(sprintf($sql, serialize($options)));

        if ($modus === self::MODUS_UPDATE) {
            $sql = <<<'SQL'
            INSERT INTO `s_core_config_values` (`element_id`, `shop_id`, `value`)
            SELECT @voucherModeElementId, `id`, 'i:0;' FROM s_core_shops
            WHERE id NOT IN (SELECT `shop_id` FROM `s_core_config_values` WHERE `element_id` = @voucherModeElementId);
            DELETE FROM `s_core_config_values` WHERE `element_id` = @voucherModeElementId && 
            (SELECT value FROM `s_core_config_values` WHERE `element_id` = @commentArticleElementId) = 'b:1;';
SQL;
            $this->addSql($sql);
        }
    }
}
