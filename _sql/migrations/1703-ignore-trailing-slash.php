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

class Migrations_Migration1703 extends AbstractMigration
{
    public function up($modus)
    {
        $elementExists = $this->connection
            ->query('SELECT id FROM `s_core_config_elements` WHERE `name` = "ignore_trailing_slash" AND form_id = (SELECT id FROM s_core_config_forms WHERE name = "Frontend100") LIMIT 1')
            ->fetch(PDO::FETCH_COLUMN);

        if (is_string($elementExists)) {
            return;
        }

        $sql = <<<'EOD'
SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Frontend100');
INSERT INTO `s_core_config_elements`
(`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
VALUES
(@parent, 'ignore_trailing_slash', 'b:1;', 'URLs ohne abschließenden Slash weiterleiten', 'Wenn aktiv, werden URLs, die normalerweise auf einen Slash (“/”) enden und ohne diesen aufgerufen werden, mittels http-code 301 auf die korrekte Seite mit Slash weitergeleitet. Der Canonical zeigt dabei immer auf die korrekte Seite mit Slash', 'boolean', 1, 0, 0, NULL);
SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'ignore_trailing_slash' AND form_id = @parent LIMIT 1);
INSERT INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
VALUES (@elementId, '2', 'Redirect urls without trailing slash', 'If active, URLs that normally end in a slash ("/") and are called without it are forwarded to the correct page with slash via http-code 301. The Canonical always points to the correct page with slash.' );
EOD;
        $this->addSql($sql);
        if ($modus === self::MODUS_UPDATE) {
            $sql = <<<'EOD'
SET @elementId = (SELECT id FROM s_core_config_elements WHERE name = 'ignore_trailing_slash' AND form_id = @parent);
INSERT INTO s_core_config_values (element_id, shop_id,value) SELECT @elementId, s.id, 'b:0;' from s_core_shops s;
EOD;
            $this->addSql($sql);
        }
    }
}
