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

class Migrations_Migration703 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->renameHtmlElement();
        $this->addComponentToLibrary();
        $this->fetchComponentId();
        $this->addComponentFields();
    }

    private function renameHtmlElement()
    {
        $sql = <<<SQL
UPDATE `s_library_component` SET `name` = 'Text Element'
WHERE `x_type` = 'emotion-components-html-element' AND pluginID IS NULL
SQL;

        $this->addSql($sql);
    }

    private function addComponentToLibrary()
    {
        $sql = <<<SQL
INSERT INTO `s_library_component` (`name`, `x_type`, `convert_function`, `description`, `template`, `cls`, `pluginID`)
VALUES ('Code Element', 'emotion-components-html-code', NULL, '', 'component_html_code', 'html-code-element', null);
SQL;

        $this->addSql($sql);
    }

    private function fetchComponentId()
    {
        $sql = <<<SQL
SET @componentId = (
  SELECT id
  FROM s_library_component
  WHERE `x_type` LIKE "emotion-components-html-code"
  AND `template` LIKE "component_html_code"
  LIMIT 1
);
SQL;
        $this->addSql($sql);
    }

    private function addComponentFields()
    {
        $sql = <<<SQL
INSERT INTO `s_library_component_field` (`componentID`, `name`, `x_type`, `field_label`, `allow_blank`, `position`)
VALUES (@componentId, 'javascript', 'codemirrorfield', 'JavaScript Code', 1, 0),
(@componentId, 'smarty', 'codemirrorfield', 'HTML Code', 1, 1);
SQL;
        $this->addSql($sql);
    }
}
