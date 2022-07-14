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

class Migrations_Migration443 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->fetchFormId();
        $this->insertFormElement();
        $this->fetchElementId();
        $this->insertFormTranslation();
        $this->fixCaptchaColor();

        if ($modus === self::MODUS_UPDATE) {
            $this->disableFirstRunWizard();
        }
    }

    private function disableFirstRunWizard()
    {
        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_values` (`element_id`, `shop_id`, `value`) VALUES
(@elementId, 1, 'i:0;');
EOD;
        $this->addSql($sql);
    }

    private function fetchFormId()
    {
        $sql = <<<'EOD'
SET @formId = (SELECT id FROM s_core_config_forms WHERE `name`='Auth');
EOD;
        $this->addSql($sql);
    }

    private function fetchElementId()
    {
        $sql = <<<'EOD'
SET @elementId = (SELECT id FROM s_core_config_elements WHERE name LIKE "firstRunWizardEnabled" LIMIT 1);
EOD;
        $this->addSql($sql);
    }

    private function insertFormElement()
    {
        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`) VALUES
(NULL, @formId, 'firstRunWizardEnabled', 'b:1;', '\'First Fun Wizard\' beim Aufruf des Backends starten', NULL, 'checkbox', 0, 0, 0);
EOD;
        $this->addSql($sql);
    }

    private function insertFormTranslation()
    {
        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
VALUES (@elementId, '2', 'Run \'First run wizard\' on next backend execution', '' );
EOD;
        $this->addSql($sql);
    }

    private function fixCaptchaColor()
    {
        $sql = <<<'EOD'
UPDATE s_core_config_element_translations
SET label = 'Captcha font color (R,G,B)'
WHERE label = 'Font color code (R,G,B)'
AND locale_id = (SELECT id FROM s_core_locales WHERE locale = 'en_GB')
AND element_id = (SELECT id FROM s_core_config_elements WHERE name = 'captchaColor');
EOD;
        $this->addSql($sql);
    }
}
