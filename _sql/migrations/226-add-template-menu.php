<?php
class Migrations_Migration226 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up()
    {
        $sql = <<<'EOD'
            SET @parent = (SELECT id FROM `s_core_menu` WHERE `name`= 'Einstellungen');

            INSERT INTO `s_core_menu` (`id`, `parent`, `hyperlink`, `name`,`onclick`, `style`,`class`,`position` ,`active` ,`pluginID` ,`resourceID` ,`controller` ,`shortcut` ,`action`)
            VALUES (NULL ,  @parent,  '',  'Template Manager', NULL , NULL ,  'sprite-edit-shade',  '0',  '1', NULL , NULL ,  'Theme', NULL ,  'Index');
EOD;
        $this->addSql($sql);
    }
}



