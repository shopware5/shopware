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

class Migrations_Migration1606 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     */
    public function up($modus): void
    {
        $this->addSql("SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'logMailAddress' LIMIT 1);");

        $this->addSql($this->modifyTypeQuery());
        $this->addSql($this->modifyLabelQuery());
        $this->addSql($this->modifyDescriptionQuery());
    }

    private function modifyTypeQuery(): string
    {
        return <<<'EOD'
UPDATE `s_core_config_elements`
SET `type` = 'textarea'
WHERE `id` = @elementId;
EOD;
    }

    private function modifyLabelQuery(): string
    {
        return <<<'EOD'
UPDATE `s_core_config_elements`
SET `label` = 'Alternative E-Mail-Adressen für Fehlermeldungen'
WHERE `id` = @elementId;

UPDATE `s_core_config_element_translations`
SET `label` = 'Alternative email addresses for errors'
WHERE `element_id` = @elementId;
EOD;
    }

    private function modifyDescriptionQuery(): string
    {
        return <<<'EOD'
UPDATE `s_core_config_elements`
SET `description` = 'Wenn dieses Feld leer ist, wird die Shopbetreiber E-Mail-Adresse verwendet. Pro Zeile kann eine Empfängeradresse angegeben werden.'
WHERE `id` = @elementId;

UPDATE `s_core_config_element_translations`
SET `description` = 'If this field is empty, the shop owners email address will be used. One recipient address may be given per line.'
WHERE `element_id` = @elementId;
EOD;
    }
}
