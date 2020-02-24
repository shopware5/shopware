<?php declare(strict_types=1);

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration1649 extends AbstractMigration
{
    public function up($modus)
    {
        if ($modus === self::MODUS_INSTALL) {
            $this->enableOnInstallation();
        }

        if ($this->connection->query("SELECT id FROM s_cms_static WHERE link = 'javascript:openCookieConsentManager()'")->fetchColumn()) {
            return;
        }

        $sql = <<<SQL
            INSERT INTO `s_cms_static` (`active`, `tpl1variable`, `tpl1path`, `tpl2variable`, `tpl2path`, `tpl3variable`, `tpl3path`, `description`, `html`, `grouping`, `position`, `link`, `target`, `parentID`, `page_title`, `meta_keywords`, `meta_description`, `changed`, `shop_ids`) VALUES
            (%d, '',	'',	'',	'',	'',	'',	'Cookie-Einstellungen',	'', 'bottom2|left',	0, 'javascript:openCookieConsentManager()',	'',	0, '', '', '', '2019-11-01 00:00:00', NULL);
SQL;
        $this->addSql(sprintf($sql, $this->shouldPageActive()));

        $sql = <<<SQL
            INSERT INTO `s_core_translations` (`objecttype`, `objectdata`, `objectkey`, `objectlanguage`, `dirty`) VALUES
            ('page', 'a:1:{s:11:\"description\";s:18:\"Cookie preferences\";}', LAST_INSERT_ID(), '2', 1);
SQL;

        $this->addSql($sql);
    }

    private function shouldPageActive(): int
    {
        $showCookieMode = $this->getConfigValue('show_cookie_note');
        $cookieMode = $this->getConfigValue('cookie_note_mode');

        if (!$showCookieMode) {
            return 0;
        }


        return $cookieMode === 1 ? 1 : 0;
    }

    private function getConfigValue(string $name)
    {
        $config = $this->connection->query(sprintf('SELECT id, value FROM s_core_config_elements WHERE name = "%s" LIMIT 1', $name))->fetch(\PDO::FETCH_ASSOC);
        $configId = (int) $config['id'];
        $default = unserialize($config['value'], ['allowed_classes' => false]);

        $value = $this->connection->query(sprintf('SELECT `value` FROM s_core_config_values WHERE element_id = %d LIMIT 1', $configId))->fetchColumn();

        if (!$value) {
            return $default;
        }

        return unserialize($value, ['allowed_classes' => false]);
    }

    private function enableOnInstallation(): void
    {
        $this->connection->exec(sprintf('UPDATE s_core_config_elements SET value = "%s" WHERE name = "show_cookie_note"', serialize(true)));
        $this->connection->exec(sprintf('UPDATE s_core_config_elements SET value = "%s" WHERE name = "cookie_note_mode"', serialize(1)));
    }
}
