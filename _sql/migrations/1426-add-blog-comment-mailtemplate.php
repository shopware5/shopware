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
class Migrations_Migration1426 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * We need to rerun the Migrations from 5.4.5 <-> 5.4.6, to make the 5.5 beta 1 updatable
     *
     * @param string $modus
     */
    public function up($modus)
    {
        if ($this->connection->query('SELECT 1 FROM s_schema_version WHERE version = 1226')->fetchColumn()) {
            return;
        }

        // Get id to change
        $sql = "SET @optinid = ( SELECT `id` FROM `s_core_config_elements` WHERE `name` = 'optinvote' LIMIT 1 );";
        $this->addSql($sql);

        // Update label in backend settings
        $sql = "UPDATE `s_core_config_elements`
                SET
                  `label` = 'Double-Opt-In für Blog- & Artikel-Bewertungen',
                  `value` = 'b:0;'
                WHERE `id` = @optinid";
        $this->addSql($sql);

        // Don't change old settings
        if ($modus === self::MODUS_UPDATE) {
            // Insert for every subshop whichs setting was 'on' (standard)
            $sql = "INSERT INTO `s_core_config_values` (`element_id`, `shop_id`, `value`)
                    SELECT 
                      @optinid,
                      `id`,
                      'b:1;'
                    FROM s_core_shops
                    WHERE id NOT IN (SELECT `shop_id` FROM `s_core_config_values` WHERE `element_id` = @optinid)";
            $this->addSql($sql);

            // Delete every old setting / apply new default
            $sql = 'DELETE FROM `s_core_config_values`
                    WHERE `element_id` = @optinid
                    AND   `value` = "b:0;"';
            $this->addSql($sql);
        }

        // Update english label too
        $sql = "UPDATE `s_core_config_element_translations`
                SET `label` = 'Double opt in for blog comments & customer reviews'
                WHERE `element_id` = @optinid";
        $this->addSql($sql);

        // Store localePrefix
        $sql = "SELECT MID(`locale`, 1, 2) AS localePrefix
                FROM `s_core_locales`
                WHERE `id` = (
                    SELECT locale_id
                    FROM `s_core_shops`
                    WHERE `default` = '1'
                    LIMIT 1
                )
                LIMIT 1
            ";
        $localePrefix = $this->connection->query($sql)->fetchColumn();

        // Add new Mailtemplate - German
        if (strtolower($localePrefix) === 'de') {
            $sql = <<<'EOD'
INSERT INTO `s_core_config_mails` ( `name`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `attachment`, `mailtype`, `dirty`)
VALUES
(
    'sOPTINBLOGCOMMENT',
    '{config name=mail}',
    '{config name=shopName}',
    'Bitte bestätigen Sie Ihre Blogartikel-Bewertung',
    '{include file="string:{config name=emailheaderplain}"}

Hallo,

vielen Dank für die Bewertung des Blogartikels „{$sArticle.title}“.
Bitte bestätigen Sie die Bewertung über den nachfolgenden Link:

{$sConfirmLink}

{include file="string:{config name=emailfooterplain}"}',
    '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo,<br/>
        <br/>
        vielen Dank für die Bewertung des Blogartikels „{$sArticle.title}“.<br/>
        Bitte bestätigen Sie die Bewertung über den nachfolgenden Link:<br/>
        <br/>
        <a href="{$sConfirmLink}">Blogartikel-Bewertung abschließen</a><br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',
    '1',
    '',
    '2',
    '0'
)
EOD;
            $this->addSql($sql);
        }
        // Add new Mailtemplate - English (Fallback)
        else {
            $sql = <<<'EOD'
INSERT INTO `s_core_config_mails` ( `name`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `attachment`, `mailtype`, `dirty`)
VALUES
(
    'sOPTINBLOGCOMMENT',
    '{config name=mail}',
    '{config name=shopName}',
    'Please confirm your blog article evaluation',
    '{include file="string:{config name=emailheaderplain}"}

Hello,

thank you for evaluating the blog article "{$sArticle.title}".
Please confirm your evaluation using the following link:

{$sConfirmLink}

{include file="string:{config name=emailfooterplain}"}',
    '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello,<br/>
        <br/>
        thank you for evaluating the blog article for "{$sArticle.title}".<br/>
        Please confirm your evaluation using the following link:<br/>
        <br/>
        <a href="{$sConfirmLink}">Confirm</a><br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',
    '1',
    '',
    '2',
    '0'
)
EOD;
            $this->addSql($sql);

            // Fix other, similar template too
            $sql = <<<'EOD'
UPDATE `s_core_config_mails`
SET `content` = '{include file="string:{config name=emailheaderplain}"}

Hello,

thank you for evaluating the article {$sArticle.articleName}.
Please confirm your evaluation using the following link:

{$sConfirmLink}

{include file="string:{config name=emailfooterplain}"}',
`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <p>
        Hello,<br/>
        <br/>
        thank you for evaluating the article {$sArticle.articleName}.<br/>
        Please confirm your evaluation using the following link:<br/>
        <br/>
        <a href="{$sConfirmLink}">Confirm</a>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>'
WHERE `s_core_config_mails`.`name` = 'sOPTINVOTE'
AND   `dirty` = 0;
EOD;
            $this->addSql($sql);
        }
    }
}
