<?php
class Migrations_Migration432 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
UPDATE `s_library_component_field` SET `x_type`= 'mediatextfield' WHERE `name` IN (
    'webm_video',
    'ogg_video',
    'h264_video',
    'fallback_picture'
)
EOD;
        $this->addSql($sql);
    }
}
