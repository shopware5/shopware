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

class Migrations_Migration1616 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus): void
    {
        $sql = <<<'SQL'
CREATE TABLE s_mail_log (
  id INT AUTO_INCREMENT NOT NULL,
  type_id INT DEFAULT NULL,
  order_id INT DEFAULT NULL,
  shop_id INT UNSIGNED DEFAULT NULL,
  subject LONGTEXT DEFAULT NULL,
  sender VARCHAR(255) NOT NULL,
  sent_at DATETIME NOT NULL,
  content_html LONGTEXT DEFAULT NULL,
  content_text LONGTEXT DEFAULT NULL,
  INDEX s_mail_log_idx_type_id (type_id),
  INDEX s_mail_log_idx_order_id (order_id),
  INDEX s_mail_log_idx_shop_id (shop_id),
  PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
CREATE TABLE s_mail_log_recipient (
  log_id INT NOT NULL,
  contact_id INT NOT NULL,
  INDEX s_mail_log_recipient_idx_log_id (log_id),
  INDEX s_mail_log_recipient_idx_contact_id (contact_id),
  PRIMARY KEY(log_id, contact_id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
CREATE TABLE s_mail_log_document (
  log_id INT NOT NULL,
  document_id INT NOT NULL,
  INDEX s_mail_log_document_idx_log_id (log_id),
  INDEX s_mail_log_document_idx_document_id (document_id),
  PRIMARY KEY(log_id, document_id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
CREATE TABLE s_mail_log_contact (
  id INT AUTO_INCREMENT NOT NULL,
  mail_address VARCHAR(255) NOT NULL,
  UNIQUE INDEX s_mail_log_contact_uniq_mail_address (mail_address),
  PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
ALTER TABLE s_mail_log ADD CONSTRAINT s_mail_log_fk_type_id
  FOREIGN KEY (type_id)
  REFERENCES s_core_config_mails (id);
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
ALTER TABLE s_mail_log ADD CONSTRAINT s_mail_log_fk_order_id
  FOREIGN KEY (order_id)
  REFERENCES s_order (id);
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
ALTER TABLE s_mail_log ADD CONSTRAINT s_mail_log_fk_shop_id
  FOREIGN KEY (shop_id)
  REFERENCES s_core_shops (id);
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
ALTER TABLE s_mail_log_recipient ADD CONSTRAINT s_mail_log_recipient_fk_log_id
  FOREIGN KEY (log_id)
  REFERENCES s_mail_log (id)
  ON DELETE CASCADE;
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
ALTER TABLE s_mail_log_recipient ADD CONSTRAINT s_mail_log_recipient_fk_contact_id
  FOREIGN KEY (contact_id)
  REFERENCES s_mail_log_contact (id)
  ON DELETE CASCADE;
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
ALTER TABLE s_mail_log_document ADD CONSTRAINT s_mail_log_document_fk_log_id
  FOREIGN KEY (log_id)
  REFERENCES s_mail_log (id)
  ON DELETE CASCADE;
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
ALTER TABLE s_mail_log_document ADD CONSTRAINT s_mail_log_document_fk_document_id
  FOREIGN KEY (document_id)
  REFERENCES s_order_documents (id)
  ON DELETE CASCADE;
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
INSERT INTO `s_crontab` (`name`, `action`, `elementID`, `data`, `next`, `start`, `interval`, `active`, `disable_on_error`, `end`, `inform_template`, `inform_mail`, `pluginID`)
VALUES ('Remove old mail log entries', 'MailLogCleanup', NULL, '', CURDATE(), NULL, 86400, 0, 1, '2016-01-01 01:00:00', '', '', NULL);
SQL;
        $this->addSql($sql);
    }
}
