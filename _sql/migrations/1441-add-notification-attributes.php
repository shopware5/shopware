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

class Migrations_Migration1441 extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up($modus)
    {
        $sql = <<<'SQL'
CREATE TABLE `s_articles_notification_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notificationID` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `notificationID` (`notificationID`),
  CONSTRAINT `s_articles_notification_attributesibfk_1` FOREIGN KEY (`notificationID`) REFERENCES `s_articles_notification` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

SQL;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `context` = 'a:5:{s:12:"sArticleLink";s:70:"http://shopware.example/genusswelten/koestlichkeiten/272/spachtelmasse";s:12:"sOrdernumber";s:7:"SW10239";s:5:"sData";N;s:11:"sNotifyData";N;s:21:"sNotifyData.attribute";N;}' 
WHERE `s_core_config_mails`.`name` = 'sARTICLEAVAILABLE';
EOD;
        $this->addSql($sql);
    }
}
