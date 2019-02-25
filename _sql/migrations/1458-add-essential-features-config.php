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

class Migrations_Migration1458 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // Adds new config
        $sql = <<<'SQL'
            SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Checkout' LIMIT 1);
    
            INSERT IGNORE INTO `s_core_config_elements`
            (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
            VALUES
            (@parent, 'alwaysShowMainFeatures', 'b:0;', 'Wesentliche Merkmale im gesamten Checkout Prozess anzeigen', 'Wenn aktiviert, werden die wesentlichen Merkmale im gesamten Checkout angezeigt. Andernfalls tauchen diese nur auf der Bestell-Bestätigungsseite auf.', 'boolean', 0, 1, 1, NULL);
    
            SET @elementId = (SELECT id FROM s_core_config_elements WHERE name = 'alwaysShowMainFeatures' LIMIT 1);
    
            INSERT IGNORE INTO `s_core_config_element_translations` (`id`, `element_id`, `locale_id`, `label`, `description`)
            VALUES
            (NULL, @elementId, '2', 'Display essential characteristics throughout the checkout process', 'If activated, the essential characteristics are displayed throughout the checkout. Otherwise, they will only appear on the order confirmation page.');
SQL;
        $this->addSql($sql);

        if ($modus === self::MODUS_INSTALL) {
            $sql = <<<'SQL'
            SET @elementId = (SELECT id FROM s_core_config_elements WHERE name = 'mainFeatures');
            
            UPDATE s_core_config_elements SET value = :value WHERE id = @elementId;
SQL;

            $stmt = $this->getConnection()->prepare($sql);
            $value = serialize('{if $sBasketItem.purchaseunit && $sBasketItem.purchaseunit != 0}
                <span class="price--label label--purchase-unit is--bold is--nowrap">
                    Inhalt:
                </span>
            
                <span class="is--nowrap">
                    {$sBasketItem.purchaseunit|floatval} {$sBasketItem.additional_details.sUnit.description}
                </span>
            {/if}
            
            {if $sBasketItem.purchaseunit && $sBasketItem.additional_details.referenceunit && $sBasketItem.purchaseunit != $sBasketItem.additional_details.referenceunit}
                <span class="is--nowrap">
                    ({$sBasketItem.additional_details.referenceprice|currency}
                    * / {$sBasketItem.additional_details.referenceunit} {$sBasketItem.additional_details.sUnit.description})
                </span>
            {/if}');

            $stmt->bindParam(':value', $value);
            $stmt->execute();
        }
    }
}
