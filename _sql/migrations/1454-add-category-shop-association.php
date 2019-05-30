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

class Migrations_Migration1454 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addShopsColumnToCategory();
        $this->addShopIdToBlogComment();
        $this->createBlogConfigForm();
        $this->createSubshopBlogCommentElement();
    }

    private function addShopsColumnToCategory()
    {
        $sql = <<<'SQL'
ALTER TABLE `s_categories`
ADD `shops` varchar(255) COLLATE 'utf8_unicode_ci' NULL;
SQL;
        $this->addSql($sql);

        return $sql;
    }

    private function addShopIdToBlogComment()
    {
        $sql = <<<'SQL'
ALTER TABLE `s_blog_comments`
ADD `shop_id` int NULL;
SQL;
        $this->addSql($sql);
    }

    private function createBlogConfigForm()
    {
        $sql = <<<'EOD'
            SET @storeFrontId = (SELECT id FROM s_core_config_forms WHERE `name` LIKE 'Frontend' AND `label` LIKE 'Storefront');
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            INSERT IGNORE s_core_config_forms 
                (`parent_id`, `name`, `label`, `description`, `position`, `plugin_id`) 
            VALUE 
                (@storeFrontId, 'Blog', 'Blog', NULL, 0, NULL);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            SET @blogId = (SELECT id FROM s_core_config_forms WHERE `name` LIKE 'Blog' ORDER BY id LIMIT 1);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            INSERT IGNORE s_core_config_form_translations
                (`form_id`, `locale_id`, `label`, `description`) 
            VALUE
                (@blogId, '2', 'Blog', null)
EOD;
        $this->addSql($sql);
    }

    private function createSubshopBlogCommentElement()
    {
        $sql = <<<'SQL'

INSERT IGNORE INTO `s_core_config_elements`
  (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`)
VALUES
(@blogId, 'displayOnlySubShopBlogComments', 'b:0;', 'Nur subshopspezifische Blog-Kommentare anzeigen', 'Wenn aktiv, werden nur Blog-Kommentare des zugehörigen Shops angezeigt.<br>Falls inaktiv, werden unabhängig vom Sprach- oder Subshop stets alle Blog-Kommentare angezeigt', 'checkbox', 0, 0, 1);
SQL;

        $this->addSql($sql);

        $sql = <<<'SQL'
        SET @elementId = (SELECT id FROM s_core_config_elements WHERE name = 'displayOnlySubShopBlogComments' AND form_id = @blogId LIMIT 1);
        INSERT INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`) VALUES (@elementId, 2, 'Display shop specific blog comments only', 'If active, only blog comments of the corresponding shop are displayed. <br>If inactive, all blog comments are always displayed regardless of the language or subshop.');
SQL;
        $this->addSql($sql);
    }
}
