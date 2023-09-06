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

class Migrations_Migration1428 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * We need to rerun the Migrations from 5.4.5 <-> 5.4.6, to make the 5.5 beta 1 updatable
     *
     * @param string $modus
     */
    public function up($modus)
    {
        if ($this->connection->query('SELECT 1 FROM s_schema_version WHERE version = 1228')->fetchColumn()) {
            return;
        }

        if ($modus === self::MODUS_UPDATE) {
            // Store localePrefix
            $sql =
                "SET @localePrefix = (
                    SELECT MID(`locale`, 1, 2) AS localePrefix
                    FROM `s_core_locales`
                    WHERE `id` = (
                        SELECT locale_id
                        FROM `s_core_shops`
                        WHERE `default` = '1'
                        LIMIT 1
                    )
                    LIMIT 1
                );";
            $this->addSql($sql);

            // Update mail template, if english update and not dirty
            $sql = <<<'EOD'
UPDATE `s_core_config_mails`
SET
  `subject` = 'Please confirm your registration at {config name=shopName}',
  `content` = '{include file="string:{config name=emailheaderplain}"}

Hello,

thank you for signing up at {$sShop}.
Please confirm your registration by clicking the following link:

{$sConfirmLink}

With this confirmation you also agree that we may send you further e-mails within the scope of the fulfilment of the contract.

{include file="string:{config name=emailfooterplain}"}',
  `contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello,<br/>
        <br/>
        thank you for signing up at {$sShop}.<br/>
        Please confirm your registration by clicking the following link:<br/>
        <br/>
        <a href="{$sConfirmLink}">Confirm registration</a><br/>
        <br/>
        With this confirmation you also agree that we may send you further e-mails within the scope of the fulfilment of the contract.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>'
WHERE `name`  = 'sOPTINREGISTER'
AND   `dirty` = '0'
AND   @localePrefix = 'en'
EOD;
            $this->addSql($sql);
        }
    }
}
