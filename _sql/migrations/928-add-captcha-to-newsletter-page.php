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

class Migrations_Migration928 extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up($modus)
    {
        $sql = <<<'EOD'
            SET @formParent = (SELECT id FROM s_core_config_forms WHERE `name` LIKE 'Newsletter' AND `label` LIKE 'Newsletter' LIMIT 1);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            INSERT IGNORE s_core_config_elements 
                (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`) 
            VALUE 
                (@formParent, 'newsletterCaptcha', '%s', 'Captcha in Newslettter  verwenden', 'Wenn diese Option aktiv ist, wird ein Captcha zur Newsletter verwendent. Empfohlen für die Newsletter: Honeypot',  'combo', 1, 0, 1, 'a:5:{s:8:"editable";b:0;s:10:"valueField";s:2:"id";s:12:"displayField";s:11:"displayname";s:13:"triggerAction";s:3:"all";s:5:"store";s:12:"base.Captcha";}');
EOD;
        $this->addSql(sprintf($sql, serialize('nocaptcha')));

        $sql = <<<'EOD'
            SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` LIKE 'newsletterShowCaptcha' LIMIT 1);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            INSERT IGNORE INTO `s_core_config_element_translations` 
                (`element_id`, `locale_id`, `label`, `description`)
            VALUES
                (@elementId, '2', 'Use captcha for newsletter', 'If this option is active, a captcha is used for newsletter. Recommended for newsletter: Honeypot');
EOD;
        $this->addSql($sql);
    }
}
