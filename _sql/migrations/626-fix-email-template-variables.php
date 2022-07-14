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

class Migrations_Migration626 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $query = $this->getConnection()->query('SELECT id, content, contentHTML FROM `s_core_config_mails` WHERE dirty = 0');
        $untouchedMails = $query->fetchAll();

        foreach ($untouchedMails as $mail) {
            $replacedContent = $this->replaceOldVarSyntax($mail['content']);
            $replacedContentHTML = $this->replaceOldVarSyntax($mail['contentHTML']);

            if ($replacedContent != $mail['content'] || $replacedContentHTML != $mail['contentHTML']) {
                $mailId = $mail['id'];

                $replacedContent = !empty($replacedContent) ? trim($this->getConnection()->quote($replacedContent), "'") : '';
                $replacedContentHTML = !empty($replacedContentHTML) ? trim($this->getConnection()->quote($replacedContentHTML), "'") : '';

                $sql = <<<EOL
                    UPDATE
                        `s_core_config_mails`
                    SET
                        content="$replacedContent",
                        contentHTML="$replacedContentHTML"
                    WHERE
                        id = $mailId;
EOL;

                $this->addSql($sql);
            }
        }
    }

    private function replaceOldVarSyntax($content)
    {
        preg_match_all("/\{([a-z0-9\.\s]+)\}/i", $content, $matches);

        foreach ($matches[1] as $match) {
            if (empty($match) || $match === 'else') {
                continue;
            }

            $content = str_replace('{' . $match . '}', '{$' . $match . '}', $content);
        }

        return trim($content);
    }
}
