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

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration963 extends AbstractMigration
{
    public function up($modus)
    {
        $template = serialize('{$campaign.name}');
        $this->addSql('SET @id = (SELECT `id` FROM `s_core_config_elements` WHERE `name`="routercampaigntemplate");');
        $this->addSql(sprintf("UPDATE `s_core_config_elements` SET `value`='%s' WHERE `id`=@id AND md5(`value`)='bbd170aeaa1f52b8c372b32d8c853e9e'", $template));
        $this->addSql(sprintf("UPDATE `s_core_config_values` SET `value`='%s' WHERE `element_id`=@id AND md5(`value`)='bbd170aeaa1f52b8c372b32d8c853e9e'", $template));
        // 'bbd170aeaa1f52b8c372b32d8c853e9e' = md5('s:64:"{sCategoryPath categoryID=$campaign.categoryId}/{$campaign.name}";')
    }
}
