<?php

class Migrations_Migration952 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOF'
SET @formId = (SELECT id FROM s_core_config_forms WHERE name = 'Auth' LIMIT 1);

INSERT INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
VALUES (NULL, @formId, 'backendMenuOnHover', 'b:1;', 'Backend Menüeinträge automatisch ausklappen', 'Das Verhalten der Buttons in der oberen Menüleiste im Backend ändert sich mit dieser Option. Falls diese Option auf Nein gesetzt ist, müssen die Menüeinträge manuell durch einen Mausklick geöffnet werden. (Backend Cache leeren und Neuladen des Backends erforderlich)', 'checkbox', '0', '0', '0', null);
EOF;
        $this->addSql($sql);

        $sql = <<<'EOF'
        SET @elementId = (SELECT id FROM s_core_config_elements WHERE name = 'backendMenuOnHover' LIMIT 1);

        INSERT INTO `s_core_config_element_translations` (`id`, `element_id`, `locale_id`, `label`, `description`)
        VALUES (NULL, @elementId, '2', 'Automatically expand backend menu entries', 'The behavior of the buttons in the upper menu in the backend changes with this option. If this option is set to No, the menu entries must be opened manually by a mouse click. (backend cache needs to be cleared and the backend must be reloaded)');
EOF;
        $this->addSql($sql);
    }
}
