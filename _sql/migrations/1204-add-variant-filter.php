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
class Migrations_Migration1204 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<SQL
INSERT IGNORE INTO `s_search_custom_facet` (`id`, `unique_key`, `active`, `display_in_categories`, `position`, `name`, `facet`, `deletable`) VALUES
(NULL, 'VariantFacet', 0, 1, 11, 'Varianten', '{"Shopware\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\VariantFacet":{"groupIds":"", "expandGroupIds":""}}', 0)
;
SQL;
        $this->addSql($sql);

        $id = (int) $this->connection->query('SELECT id FROM `s_search_custom_facet` WHERE `unique_key` = "VariantFacet"')->fetchColumn(0);

        $this->importFacetTranslations($id);
    }

    /**
     * @param int $facetId
     */
    private function importFacetTranslations($facetId)
    {
        $shops = $this->connection->query('SELECT shops.id, shops.main_id, shops.locale_id, locales.locale FROM s_core_shops shops INNER JOIN s_core_locales locales ON locales.id=shops.locale_id')
            ->fetchAll(PDO::FETCH_ASSOC);

        foreach ($shops as $shop) {
            $translationShopId = $shop['id'];
            $locale = $shop['locale'];
            $localeId = $shop['locale_id'];

            $insert = $this->getExistingSortingTranslation($translationShopId, $localeId, $locale);

            if (!empty($insert)) {
                $customFacetTranslations = $this->connection->query('SELECT id, objectdata FROM s_core_translations WHERE objecttype="custom_facet" AND objectlanguage=' . $translationShopId)
                    ->fetch(PDO::FETCH_ASSOC);

                if (empty($customFacetTranslations)) {
                    $insert = [$facetId => $insert];

                    $this->addSql(
                        "INSERT INTO s_core_translations (objecttype, objectdata, objectkey, objectlanguage, dirty)
                     VALUES ('custom_facet', '" . serialize($insert) . "', '1', " . $translationShopId . ', 0)'
                    );
                } else {
                    $unserialized = unserialize($customFacetTranslations['objectdata']);

                    if (array_key_exists($facetId, $unserialized)) {
                        continue;
                    }

                    $unserialized[$facetId] = $insert;
                    $this->addSql("UPDATE s_core_translations SET objectdata='" . serialize($unserialized) . "' WHERE id=" . $customFacetTranslations['id']);
                }
            }
        }
    }

    /**
     * @param int    $translationShopId
     * @param int    $localeId
     * @param string $locale
     *
     * @return array
     */
    private function getExistingSortingTranslation($translationShopId, $localeId, $locale)
    {
        $translation = $this->connection->query(
            "SELECT `name`, `value`
             FROM s_core_snippets
             WHERE `name` = 'variant'
             AND namespace = 'frontend/listing/facet_labels'
             AND shopID = " . $translationShopId . ' AND localeID = ' . $localeId
        )->fetch(PDO::FETCH_ASSOC);

        if (empty($translation)) {
            switch (strtolower(substr($locale, 0, 2))) {
                case 'de':
                    return [
                        'label' => 'Varianten',
                    ];
                case 'en':
                    return [
                        'label' => 'Variants',
                    ];
            }
        }

        return ['label' => $translation['value']];
    }
}
