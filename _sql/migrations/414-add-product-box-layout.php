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

class Migrations_Migration414 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addProductBoxLayoutColumn();
        $this->addSearchProductBoxLayoutSwitch();
    }

    private function addProductBoxLayoutColumn()
    {
        $sql = <<<EOT
ALTER TABLE s_categories
ADD product_box_layout varchar(50) NULL DEFAULT NULL
EOT;
        $this->addSql($sql);
    }

    private function addSearchProductBoxLayoutSwitch()
    {
        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(190, 'searchProductBoxLayout', 's:5:"basic"', 'Produkt Layout', 'Mit Hilfe des Produkt Layouts können Sie entscheiden, wie Ihre Produkte auf der Suchergebnis-Seite dargestellt werden sollen. Wählen Sie eines der drei unterschiedlichen Layouts um die Ansicht perfekt auf Ihr Produktsortiment abzustimmen.', 'product-box-layout-select', 0, 0, 1, NULL, NULL, NULL);

SET @elementId = (SELECT `id` FROM `s_core_config_elements` WHERE `form_id`= 190 AND `name`="searchProductBoxLayout" LIMIT 1);

INSERT IGNORE INTO `s_core_config_element_translations` (`label`, `description`, `locale_id`, `element_id`)
VALUES ('Product layout', 'Product layout allows you to control how your products are presented on the search result page. Choose between three different layouts to fine-tune your product display.', 2, @elementId);
EOD;
        $this->addSql($sql);
    }
}
