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
class Migrations_Migration1228 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
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
