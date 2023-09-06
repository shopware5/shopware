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

class Migrations_Migration371 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
SET @parentFormId = (SELECT `id` FROM `s_core_config_forms` WHERE name = 'Other' and parent_id IS NULL);

INSERT IGNORE INTO `s_core_config_forms`
    (`parent_id`, `name`, `label`, `description`)
  VALUES
    (@parentFormId, 'SwagMultiEdit', 'Multi edit', '');

SET @formId = (SELECT `id` FROM `s_core_config_forms` WHERE name = 'SwagMultiEdit');

UPDATE `s_core_config_forms` SET `plugin_id` = NULL WHERE `id`=@formId;

UPDATE `s_core_plugins` SET `active`=0 WHERE `name` = 'SwagMultiEdit';

DELETE FROM `s_core_menu` WHERE `controller`= 'SwagMultiEdit';

INSERT IGNORE INTO `s_core_config_elements`
  (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`)
VALUES
(@formId, 'addToQueuePerRequest', 'i:2048;', 'Number of products per queue request', 'The number of products, you want to add to queue per request. The higher the value, the longer a request will take. Too low values will result in overhead', 'number', 1, 0, 0, NULL, NULL, 'a:1:{s:10:"attributes";a:1:{s:8:"minValue";i:100;}}');

INSERT IGNORE INTO `s_core_config_elements`
  (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`)
VALUES
(@formId, 'batchItemsPerRequest', 'i:2048;', 'Products per batch request', 'The number of products, you want to be processed per request. The higher the value, the longer a request will take. Too low values will result in overhead', 'number', 1, 0, 0, NULL, NULL, 'a:1:{s:10:"attributes";a:1:{s:8:"minValue";i:50;}}');

INSERT IGNORE INTO `s_core_config_elements`
  (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`)
VALUES
(@formId, 'enableBackup', 'b:1;', 'Enable restore feature', 'Enable the restore feature.', 'checkbox', 0, 0, 0, NULL, NULL, 'a:0:{}');

INSERT IGNORE INTO `s_core_config_elements`
  (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`)
VALUES
(@formId, 'b:0;', 'b:1;', 'Invalidate products in batch mode', 'Will clear the cache for any product, which was changed in batch mode. When changing many products, this will be quite slow. Its recommended to clear the cache manually afterwards.', 'checkbox', 0, 0, 0, NULL, NULL, 'a:0:{}');
EOD;

        $this->addSql($sql);
    }
}
