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

class Migrations_Migration962 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $conn = $this->getConnection();
        $id = (int) $conn->query('SELECT `id` FROM `s_cms_support` WHERE `name` = "Anfrage-Formular"')
            ->fetchColumn(0);

        if (!empty($id)) {
            $statement = $conn->prepare('SELECT max(`position`)+1 AS `position` FROM `s_cms_support_fields` WHERE `supportID`=?');
            $statement->execute([$id]);
            $position = (int) $statement->fetchColumn(0);

            $statement = $conn->prepare(
                'INSERT INTO `s_cms_support_fields` (`name`, `typ`, `required`, `supportID`, `label`, `class`, `error_msg`, `value`, `added`, `position`, `ticket_task`, `note`) VALUES ("sordernumber", "hidden", 0, ?, "Artikelnummer", "normal", "", "", NOW(), ?, "", "");'
            );
            $statement->execute([$id, $position]);

            $sql = <<<'EOD'
UPDATE `s_cms_support` SET `email_template` = "{sShopname} Anfrage-Formular

Anrede: {sVars.anrede}
Vorname: {sVars.vorname}
Nachname: {sVars.nachname}
eMail: {sVars.email}
Telefon: {sVars.telefon}
Artikel: {sVars.sordernumber}

Frage:
{sVars.inquiry}", `email_subject`="{sShopname} Anfrage-Formular" WHERE `id`=? AND md5(`email_template`) = "02c8993563bf42f52a95504e6e8549f5";
EOD;

            $statement = $conn->prepare($sql);
            $statement->execute([$id]);
        }

        $id = (int) $conn->query('SELECT `id` FROM `s_cms_support` WHERE `name` = "Inquiry form"')
            ->fetchColumn(0);

        if (!empty($id)) {
            $statement = $conn->prepare('SELECT max(`position`)+1 AS `position` FROM `s_cms_support_fields` WHERE `supportID`=?');
            $statement->execute([$id]);
            $position = (int) $statement->fetchColumn(0);

            $statement = $conn->prepare(
                'INSERT INTO `s_cms_support_fields` (`name`, `typ`, `required`, `supportID`, `label`, `class`, `error_msg`, `value`, `added`, `position`, `ticket_task`, `note`) VALUES ("sordernumber", "hidden", 0, ?, "Order number", "normal", "", "", NOW(), ?, "", "");'
            );
            $statement->execute([$id, $position]);

            $sql = <<<'EOD'
UPDATE `s_cms_support` SET `email_template` = "{sShopname} Anfrage-Formular

Anrede: {sVars.anrede}
Vorname: {sVars.vorname}
Nachname: {sVars.nachname}
eMail: {sVars.email}
Telefon: {sVars.telefon}
Artikel: {sVars.sordernumber}

Frage:
{sVars.inquiry}", `email_subject`="{sShopname} Anfrage-Formular" WHERE `id`=? AND md5(`email_template`) = "02c8993563bf42f52a95504e6e8549f5";
EOD;

            $statement = $conn->prepare($sql);
            $statement->execute([$id]);
        }
    }
}
