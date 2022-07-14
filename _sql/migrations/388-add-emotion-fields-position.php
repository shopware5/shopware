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

class Migrations_Migration388 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('ALTER TABLE s_library_component_field ADD position INT NULL;');
        $this->addSql('UPDATE s_library_component_field SET position = id;');
        $this->addSql("SET @parent = (SELECT id FROM `s_library_component` WHERE `x_type`='emotion-components-article-slider' AND template = 'component_article_slider' AND pluginID IS NULL LIMIT 1);");
        $this->addSql("SET @maxNumberPosition = (SELECT id FROM `s_library_component_field` WHERE `name`='article_slider_max_number' AND componentID = @parent LIMIT 1);");
        $this->addSql('UPDATE s_library_component_field SET position = position+1 WHERE componentID = @parent AND id >= @maxNumberPosition;');
    }
}
