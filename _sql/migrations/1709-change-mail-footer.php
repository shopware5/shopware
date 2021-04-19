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

class Migrations_Migration1709 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        if ($modus === self::MODUS_UPDATE) {
            return;
        }

        $this->connection->exec('SET @formId = ( SELECT id FROM `s_core_config_forms` WHERE name = \'Frontend60\' LIMIT 1 );');

        $html = <<<HTML
<br/>
Mit freundlichen Grüßen<br/><br/>
Ihr Team von {config name=shopName}</div>

{{config name=address}|nl2br}<br/><br/>
Bankverbindung:<br/>
{{config name=bankAccount}|nl2br}
HTML;

        $plain = <<<PLAIN
Mit freundlichen Grüßen

Ihr Team von {config name=shopName}

{config name=address}<br/><br/>
Bankverbindung:<br/>
{config name=bankAccount}
PLAIN;

        $sql = <<<SQL
            UPDATE s_core_config_elements SET `value` = ? WHERE `name` = ? AND form_id = @formId
SQL;
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([serialize($html), 'emailfooterhtml']);
        $stmt->execute([serialize($plain), 'emailfooterplain']);
    }
}
