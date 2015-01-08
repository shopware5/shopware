<?php
class Migrations_Migration432 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {

        // Phone link in the footer - german snippet
        $sql = <<<'EOD'
            SET @parent = (SELECT id FROM `s_core_snippets` WHERE `value` LIKE 'Telefonische Unterst&uuml;tzung und Beratung unter:<br /><br /><strong style="font-size:19px;">0180 - 000000</strong><br/>Mo-Fr, 09:00 - 17:00 Uhr');
            UPDATE `s_core_snippets` SET `value` = 'Telefonische Unterst&uuml;tzung und Beratung unter:<br /><br /><a href="tel:+49180000000" class="footer--phone-link">0180 - 000000</a><br/>Mo-Fr, 09:00 - 17:00 Uhr', `updated` = NOW() WHERE `id` = @parent;
EOD;
        $this->addSql($sql);

        // ...english snippet
        $sql = <<<'EOD'
            SET @parent = (SELECT id FROM `s_core_snippets` WHERE `value` LIKE 'Telephone support and counselling under:<br /><br /><strong>0180 - 000000</strong><br/>Mon-Fri, 9 am - 5 pm');
            UPDATE `s_core_snippets` SET `value` = 'Telephone support and counselling under:<br /><br /><a href="tel:+49180000000" class="footer--phone-link">0180 - 000000</a><br/>Mon-Fri, 9 am - 5 pm', `updated` = NOW() WHERE `id` = @parent;
EOD;
        $this->addSql($sql);
    }
}
