<?php

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration804 extends AbstractMigration
{
    /**
     * @inheritdoc
     */
    public function up($modus)
    {
        $sql = <<<'SQL'
CREATE TABLE `s_order_basket_signatures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `signature` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `basket` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;

        $this->addSql($sql);
    }
}
