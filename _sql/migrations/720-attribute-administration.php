<?php


class Migrations_Migration720 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'SQL'
ALTER TABLE `s_articles_attributes` CHANGE `attr1` `attr1` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
ALTER TABLE `s_articles_attributes` CHANGE `attr2` `attr2` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
ALTER TABLE `s_articles_attributes` CHANGE `attr3` `attr3` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
ALTER TABLE `s_articles_attributes` CHANGE `attr8` `attr8` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
SQL;
        $this->addSql($sql);


        $sql = <<<'SQL'
ALTER TABLE `s_articles_attributes` CHANGE `attr13` `attr13` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
            SET @parentId = (SELECT id FROM s_core_menu WHERE name = 'Einstellungen');
            INSERT INTO `s_core_menu` (`id`, `parent`, `hyperlink`, `name`, `onclick`, `style`, `class`, `position`, `active`, `pluginID`, `resourceID`, `controller`, `shortcut`, `action`) VALUES
            (null, @parentId, '', 'Freitextfeld-Verwaltung', '', NULL, 'sprite-attributes', -1, 1, NULL, 0, 'Attributes', NULL, 'Index');
SQL;

        $this->addSql($sql);

        $sql = <<<'SQL'

CREATE TABLE IF NOT EXISTS `s_attribute_configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `column_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `column_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `translatable` int(1) NOT NULL,
  `display_in_backend` int(1) NOT NULL,
  `custom` int(1) NOT NULL,
  `help_text` TEXT COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `support_text` varchar(500) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `entity` varchar(500) COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `plugin_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
CREATE TABLE `s_filter_values_attributes` (
  `id` int(11) NOT NULL,
  `valueID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `s_filter_values_attributes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `valueID` (`valueID`);

ALTER TABLE `s_filter_values_attributes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `s_filter_values_attributes`
  ADD CONSTRAINT `s_filter_values_attributes_ibfk_1` FOREIGN KEY (`valueID`) REFERENCES `s_filter_values` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
SQL;

        $this->addSql($sql);


        $sql = <<<'SQL'
CREATE TABLE `s_filter_options_attributes` (
  `id` int(11) NOT NULL,
  `optionID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `s_filter_options_attributes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `optionID` (`optionID`);


ALTER TABLE `s_filter_options_attributes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `s_filter_options_attributes`
  ADD CONSTRAINT `s_filter_options_attributes_ibfk_1` FOREIGN KEY (`optionID`) REFERENCES `s_filter_options` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
SQL;
        $this->addSql($sql);


        $sql = <<<'SQL'
CREATE TABLE `s_product_streams_attributes` (
  `id` int(11) NOT NULL,
  `streamID` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `s_product_streams_attributes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `streamID` (`streamID`);

ALTER TABLE `s_product_streams_attributes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `s_product_streams_attributes`
  ADD CONSTRAINT `s_product_streams_attributes_ibfk_1` FOREIGN KEY (`streamID`) REFERENCES `s_product_streams` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

SQL;

        $this->addSql($sql);

        $sql = <<<'SQL'
INSERT INTO s_core_acl_resources (name) VALUES ('attributes');
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
SET @resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'attributes' LIMIT 1);
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES (@resourceID, 'read');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES (@resourceID, 'update');
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
UPDATE s_core_snippets SET `value` = 'Als Vorschau verwenden' WHERE `name` = 'image/list/preview_button' AND localeID = 1 AND dirty = 0;
UPDATE s_core_snippets SET `value` = 'Bild löschen' WHERE `name` = 'image/list/remove_button' AND localeID = 1 AND dirty = 0;
UPDATE s_core_snippets SET `value` = 'Mark as preview image' WHERE `name` = 'image/list/preview_button' AND localeID = 2 AND dirty = 0;
UPDATE s_core_snippets SET `value` = 'Remove image' WHERE `name` = 'image/list/remove_button' AND localeID = 2 AND dirty = 0;
SQL;

        $this->addSql($sql);

        $sql = <<<'SQL'
SET @formId = (SELECT id FROM s_core_config_forms WHERE name = 'Attribute' LIMIT 1);
DELETE FROM s_core_config_form_translations WHERE form_id = @formId;
DELETE FROM s_core_config_elements WHERE form_id = @formId;
DELETE FROM s_core_config_forms WHERE id = @formId;
SQL;

        $this->addSql($sql);

        $statement = $this->connection->query("SELECT * FROM s_core_engine_elements");
        $attributes = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($attributes as $attribute) {
            $attribute = $this->convertAttribute($attribute);

            $sql = "
              INSERT INTO s_attribute_configuration (`table_name`, `column_name`, `column_type`, `position`, `translatable`, `display_in_backend`, `custom`, `help_text`, `support_text`, `label`, `entity`, `plugin_id`)
              VALUES (
                's_articles_attributes',
                 '{$attribute['name']}',
                 '{$attribute['type']}',
                 {$attribute['position']},
                 {$attribute['translatable']},
                 1,
                 0,
                 '{$attribute['help']}',
                 '',
                 '{$attribute['label']}',
                 {$attribute['entity']},
                 NULL
              );
            ";
            $this->addSql($sql);
        }
    }

    /**
     * @param array $attribute
     * @return array
     */
    private function convertAttribute(array $attribute)
    {
        $attribute['entity'] = 'NULL';
        switch (strtolower($attribute['type'])) {
            case 'date':
            case 'html':
            case 'text':
            case 'boolean':
                break;
            case 'number':
                $attribute['type'] = 'float';
                break;
            case 'textarea':
                $attribute['type'] = 'text';
                break;
            case 'time':
                $attribute['type'] = 'datetime';
                break;
            case 'article':
                $attribute['type'] = 'single_selection';
                $attribute['entity'] = "'Shopware\\\Models\\\Article\\\Article'";
                break;
            case 'select':
            default:
                $attribute['type'] = 'string';
                break;
        }
        return $attribute;
    }
}
