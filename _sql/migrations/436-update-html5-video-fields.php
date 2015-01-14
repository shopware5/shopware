<?php
class Migrations_Migration436 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
UPDATE `s_library_component_field` SET `x_type`= 'mediatextfield', `help_text`= 'Sie können eine Datei auswählen oder eine externe URL angeben.' WHERE `name` IN (
    'webm_video',
    'ogg_video',
    'h264_video',
    'fallback_picture'
)
EOD;
        $this->addSql($sql);
    }
}
