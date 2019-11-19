<?php declare(strict_types=1);

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration1649 extends AbstractMigration
{
    public function up($modus)
    {
        if ($this->connection->query("SELECT id FROM s_cms_static WHERE link = 'javascript:openCookieConsentManager()'")->fetchColumn()) {
            return;
        }

        $sql = <<<SQL
            INSERT INTO `s_cms_static` (`active`, `tpl1variable`, `tpl1path`, `tpl2variable`, `tpl2path`, `tpl3variable`, `tpl3path`, `description`, `html`, `grouping`, `position`, `link`, `target`, `parentID`, `page_title`, `meta_keywords`, `meta_description`, `changed`, `shop_ids`) VALUES
            (1, '',	'',	'',	'',	'',	'',	'Cookie Einstellungen',	'', 'bottom2|left',	0, 'javascript:openCookieConsentManager()',	'',	0, '', '', '', '2019-11-01 00:00:00', NULL);
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
            INSERT INTO `s_core_translations` (`objecttype`, `objectdata`, `objectkey`, `objectlanguage`, `dirty`) VALUES
            ('page', 'a:1:{s:11:\"description\";s:18:\"Cookie preferences\";}', LAST_INSERT_ID(), '2', 1);
SQL;

        $this->addSql($sql);
    }
}
