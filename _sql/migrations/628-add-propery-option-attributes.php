<?php

class Migrations_Migration628 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<EOD
CREATE TABLE IF NOT EXISTS `s_filter_options_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filterOptionID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `filterOptionID` (`filterOptionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
EOD;
        $this->addSql($sql);

        $sql = <<<EOD
ALTER TABLE `s_filter_options_attributes` ADD CONSTRAINT `s_filter_otpions_attributes_ibfk_1` 
FOREIGN KEY (`filterOptionID`) REFERENCES `s_filter_options` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
EOD;
        $this->addSql($sql);
    }
}