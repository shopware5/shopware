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

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration907 extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up($modus)
    {
        $sql = <<<'SQL'
CREATE TABLE `s_order_basket_signatures` (
  `signature` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `basket` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` date NOT NULL,
  PRIMARY KEY (`signature`),
  KEY (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
INSERT INTO `s_crontab` (`id`, `name`, `action`, `elementID`, `data`, `next`, `start`, `interval`, `active`, `end`, `inform_template`, `inform_mail`, `pluginID`)
VALUES (NULL, 'Basket Signature cleanup', 'CleanupSignatures', NULL, '', '2016-10-11 08:34:13', NULL, '86400', '1', '2016-10-11 08:34:13', '', '', NULL);
SQL;
        $this->addSql($sql);
    }
}
