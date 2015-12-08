<?php
class Migrations_Migration701 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // remove old templates
        $this->addSql("DELETE FROM `s_core_templates` WHERE version < 3;");

        // remove unused table fields
        $this->addSql("ALTER TABLE `s_categories` DROP COLUMN `showfiltergroups`, DROP COLUMN `template`;");
        $this->addSql("ALTER TABLE `s_emotion` DROP COLUMN `container_width`;");

        // remove unused config elements
        $optionsToDelete = [
            'category_default_tpl',
            'categorytemplates',
            'maxsupplierscategory',
            'showbundlemainarticle',
            'paymentEditingInCheckoutPage',
            'basketHeaderColor',
            'basketHeaderFontColor',
            'basketTableColor',
            'fuzzysearchdistance',
            'fuzzysearchpricefilter',
            'fuzzysearchresultsperpage',
            'thumb'
        ];

        $optionsToDeleteSql = "'".implode("','", $optionsToDelete)."'";
        $sql = <<<SQL
DELETE elements, elementValues, elementTranslations
FROM `s_core_config_elements` as elements
LEFT JOIN `s_core_config_values` as elementValues
  ON elements.id = elementValues.element_id
LEFT JOIN `s_core_config_element_translations` as elementTranslations
  ON elements.id = elementTranslations.element_id
WHERE `name` IN ($optionsToDeleteSql)
SQL;

        $this->addSql($sql);

        /**
         * Cleanup
         */

        // remove orphan forms
        $this->addSql("DELETE FROM `s_core_config_forms` WHERE (SELECT count(*) FROM `s_core_config_elements` WHERE s_core_config_elements.form_id = s_core_config_forms.id) < 1 AND parent_id IS NOT NULL;");
    }
}
