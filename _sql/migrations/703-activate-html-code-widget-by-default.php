<?php
class Migrations_Migration703 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        if ($modus !== self::MODUS_INSTALL) {
            return;
        }
        $this->insertPlugin();
        $this->fetchPluginId();
        $this->insertSubscribers();
        $this->addComponentToLibrary();
        $this->fetchComponentId();
        $this->addComponentFields();
    }

    private function insertPlugin()
    {
        $sql = <<<SQL
INSERT INTO `s_core_plugins` (`namespace`, `name`, `label`, `source`, `active`, `added`, `installation_date`, `update_date`, `refresh_date`, `author`, `copyright`, `version`, `capability_update`, `capability_install`, `capability_enable`)
VALUES('Backend', 'SwagHtmlCodeWidget', 'HTML Code Widget', 'Default', 1, NOW(), NOW(), NOW(), NOW(), 'shopware AG', 'Copyright © 2015, shopware AG', '1.0.1', 1, 1, 1);
SQL;
        $this->addSql($sql);
    }

    private function fetchPluginId()
    {
        $sql = <<<SQL
SET @pluginId = (
  SELECT id
  FROM s_core_plugins
  WHERE name LIKE "SwagHtmlCodeWidget"
  AND author LIKE "shopware AG"
  LIMIT 1
);
SQL;
        $this->addSql($sql);
    }

    private function insertSubscribers()
    {
        $sql = <<<SQL
INSERT INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`)
VALUES ('Enlight_Controller_Action_PostDispatchSecure_Widgets_Emotion', 0, 'Shopware_Plugins_Backend_SwagHtmlCodeWidget_Bootstrap::extendsEmotionTemplates', @pluginId, 0),
('Enlight_Controller_Action_PostDispatchSecure_Backend_Emotion', 0, 'Shopware_Plugins_Backend_SwagHtmlCodeWidget_Bootstrap::extendsEmotionTemplates', @pluginId, 0);
SQL;
        $this->addSql($sql);
    }

    private function addComponentToLibrary()
    {
        $sql = <<<SQL
INSERT INTO `s_library_component` (`name`, `x_type`, `convert_function`, `description`, `template`, `cls`, `pluginID`)
VALUES ('HTML Code Widget', 'emotion-html-code', NULL, '', 'component_html_code', 'emotion-html-code-widget', @pluginId);
SQL;

        $this->addSql($sql);
    }

    private function fetchComponentId()
    {
        $sql = <<<SQL
SET @componentId = (
  SELECT id
  FROM s_library_component
  WHERE `x_type` LIKE "emotion-html-code"
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
