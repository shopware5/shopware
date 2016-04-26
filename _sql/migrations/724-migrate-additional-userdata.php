<?php

class Migrations_Migration724 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // create fields in s_user
        $sql = <<<SQL
        ALTER TABLE `s_user`
            ADD `title` varchar(64) NULL,
            ADD `salutation` varchar(30) NULL AFTER `title`,
            ADD `firstname` varchar(255) NULL AFTER `salutation`,
            ADD `lastname` varchar(255) NULL AFTER `firstname`,
            ADD `birthday` date NULL AFTER `lastname`;
SQL;
        $this->addSql($sql);

        // migrate existing data to s_user table
        $sql = <<<SQL
        UPDATE s_user AS u
        JOIN s_user_billingaddress as a ON a.userID = u.id
        SET
          u.salutation = a.salutation,
          u.firstname = a.firstname,
          u.lastname = a.lastname,
          u.birthday = a.birthday;
SQL;
        $this->addSql($sql);

        // finally remove the old field
        $this->addSql("ALTER TABLE `s_user_billingaddress` DROP `birthday`");
    }
}
