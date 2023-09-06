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

class Migrations_Migration901 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('ALTER TABLE `s_articles_vote` ADD `shop_id` INT NULL DEFAULT NULL;');
        $this->addSql("SET @formId = (SELECT id FROM s_core_config_forms WHERE name = 'Rating' LIMIT 1)");
        $this->addSql('ALTER TABLE `s_articles_vote` CHANGE `answer_date` `answer_date` DATETIME NULL DEFAULT NULL;');

        $sql = <<<'EOD'

INSERT IGNORE INTO `s_core_config_elements`
  (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`)
VALUES
(@formId, 'displayOnlySubShopVotes', 'b:0;', 'Nur Subshopspezifische Bewertungen anzeigen', 'description', 'checkbox', 0, 0, 1);
EOD;

        $this->addSql($sql);

        $this->addSql("UPDATE s_core_config_elements SET scope = 1 WHERE name = 'votedisable'");
    }
}
