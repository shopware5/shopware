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
class Migrations_Migration1212 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up($modus)
    {
        $sql = <<<'EOD'
UPDATE s_core_config_elements SET `label` = 'Captcha in Newsletter verwenden', `description` = 'Die hier ausgewählte Captcha Methode wird bei der Newsletterregistrierung im Frontend verwendet.' WHERE `name` = 'newsletterCaptcha';
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` LIKE 'newsletterCaptcha' LIMIT 1);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_element_translations` 
    (`element_id`, `locale_id`, `label`, `description`)
VALUES
    (@elementId, '2', 'Use captcha for newsletter', 'The selected captcha method is used in the newsletter registration in the frontend.');
EOD;
        $this->addSql($sql);
    }
}
