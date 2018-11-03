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

class Migrations_Migration1209 extends AbstractMigration
{
    public function up($modus)
    {
        $options = [
            'store' => [
                ['DEBUG', 'Debug'],
                ['INFO', 'Info'],
                ['NOTICE', 'Notice'],
                ['WARNING', 'Warning'],
                ['ERROR', 'Error'],
                ['CRITICAL', 'Critical'],
                ['ALERT', 'Alert'],
                ['EMERGENCY', 'Emergency'],
            ],
        ];

        $options = serialize($options);

        $helptext = 'Hier wird festgelegt, ab welchem Log-Level E-Mails versendet werden. Im Standard werden E-Mails ab dem Log-Level "Warning" verschickt. Um nur E-Mails bei Fehlern zu bekommen, kannst du das Log-Level erhöhen, zum Beispiel auf "Error" oder höher.';
        $helptext_en = 'Here you can choose the minimum log level for sending an e-mail. The default is "Warning". To focus on actual errors, you can increase the log level for example to "Error" or higher.';

        $sql = <<<EOD
INSERT INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`) VALUES
(268,'logMailLevel','s:7:"Warning";','Log-Level','$helptext','select',1,0,0, '$options' );
EOD;
        $this->addSql($sql);

        $sql = <<<EOD
SET @elementId = (SELECT id FROM s_core_config_elements WHERE name = 'logMailLevel' LIMIT 1);
INSERT IGNORE INTO s_core_config_element_translations (element_id, locale_id, label, description) VALUES(@elementId, 2, 'Log level', '$helptext_en');
EOD;
        $this->addSql($sql);
    }
}
