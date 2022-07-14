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

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration909 extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up($modus)
    {
        $this->addSql("SET @formId = (select id from s_core_config_forms WHERE name = 'Frontend30' LIMIT 1);");

        $sql = <<<EOD
    INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`) VALUES
    (NULL, @formId, 'displayListingBuyButton', 'b:0;', 'Kaufenbutton im Listing anzeigen', '', 'checkbox', 1, 0, 1);
EOD;
        $this->addSql($sql);

        $sql = <<<EOD
SET @elementID = (SELECT id FROM s_core_config_elements WHERE form_id=@formId AND `name`='displayListingBuyButton');
INSERT IGNORE INTO s_core_config_element_translations (element_id, locale_id, label, description) VALUES (@elementID, 2, 'Display buy button in listing', '');
EOD;

        $this->addSql($sql);
    }
}
