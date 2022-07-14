<?php

declare(strict_types=1);
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

class Migrations_Migration1654 extends AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('SET @formId = (SELECT id FROM s_core_config_forms WHERE name = "Frontend100")');

        $sql = "INSERT INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
                    VALUES (@formId, 'hrefLangJustSeoUrl', 'b:1;', 'Nur SEO-Urls in href-lang ausgeben', 'Wenn aktiv, werden in den Meta Tags \"href-lang\" nur SEO-Urls ausgegeben', 'boolean', '0', '200', '0', NULL);";
        $this->addSql($sql);

        $this->addSql('SET @elementId = LAST_INSERT_ID();');

        $sql = "INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
                   VALUES (@elementId, '2', 'Just output href-lang with SEO URLs', 'If active, just SEO URLs are displayed in the meta tags \"href-lang\"');";
        $this->addSql($sql);

        $this->addSql('UPDATE s_core_config_elements SET position = 50 WHERE name IN ("hrefLangEnabled", "hrefLangCountry", "hrefLangDefaultShop")');
    }
}
