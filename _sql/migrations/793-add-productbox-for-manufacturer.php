<?php
class Migrations_Migration793 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`) VALUES
(144, \'manufacturerProductBoxLayout\', \'s:5:"basic";\', \'Hersteller-Seite Produkt Layout\', \'Mit Hilfe des Produkt Layouts können Sie entscheiden, wie Ihre Produkte auf der Hersteller-Seite dargestellt werden sollen. Wählen Sie eines der drei unterschiedlichen Layouts um die Ansicht perfekt auf Ihr Produktsortiment abzustimmen.\', \'product-box-layout-select\', 0, 0, 1);');
        $this->addSql('SET @elementId = (SELECT `id` FROM `s_core_config_elements` WHERE `form_id`= 144 AND `name`="manufacturerProductBoxLayout" LIMIT 1);');
        $this->addSql('INSERT IGNORE INTO `s_core_config_element_translations` (`label`, `description`, `locale_id`, `element_id`)
VALUES (\'Manufacturer page product layout\', \'Product layout allows you to control how your products are presented on the manufacturer page. Choose between three different layouts to fine-tune your product display.\', 2, @elementId);');
    }
}
