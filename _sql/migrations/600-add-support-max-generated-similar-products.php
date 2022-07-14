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

class Migrations_Migration600 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('SET @element_id = (SELECT `id` FROM `s_core_config_elements` WHERE `name` = "similarlimit");');

        $this->updateDescription();
        $this->updateTranslatedDescription();
    }

    private function updateDescription()
    {
        $sql = <<<EOD
                UPDATE `s_core_config_elements`
                SET
                `description` = "Wenn keine ähnlichen Produkte gefunden wurden, kann Shopware automatisch alternative Vorschläge generieren. Sie können die automatischen Vorschläge deaktivieren indem Sie 0 eintragen. Das deaktivieren kann sich positiv auf die Performance dieser geladenen Artikel auswirken."
                WHERE `name` = "similarlimit";
EOD;
        $this->addSql($sql);
    }

    private function updateTranslatedDescription()
    {
        $sql = <<<EOD
                UPDATE `s_core_config_element_translations`
                SET
                `description` = "If no similar articles are found, Shopware can automatically generates alternative suggestions. You can disable these suggestions if you enter 0. May increase performance when loading these articles."
                WHERE `element_id` = @element_id;
EOD;
        $this->addSql($sql);
    }
}
