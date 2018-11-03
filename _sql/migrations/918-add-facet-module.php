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

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration918 extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up($modus)
    {
        $this->addModule();

        $this->addCategoryConfig();

        $this->importDefaultFacets();

        $this->importFacetTranslations();

        $this->createSearchFacets();

        $this->addNewCategoryFilterParam();
    }

    private function addModule()
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `s_search_custom_facet` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `active` int(1) unsigned NOT NULL,
  `unique_key` varchar(100) NULL DEFAULT NULL,
  `display_in_categories` int(1) unsigned NOT NULL,
  `deletable` int(1) unsigned NOT NULL,
  `position` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `facet` LONGTEXT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE `unique_identifier` (`unique_key`),
  KEY `sorting` (`display_in_categories`,`position`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;
        $this->addSql($sql);
    }

    private function addCategoryConfig()
    {
        $this->addSql('ALTER TABLE `s_categories` ADD `facet_ids` TEXT NULL');
    }

    private function importDefaultFacets()
    {
        $sql = <<<SQL
INSERT IGNORE INTO `s_search_custom_facet` (`id`, `unique_key`, `active`, `display_in_categories`, `position`, `name`, `facet`, `deletable`) VALUES
(1, 'CategoryFacet', 1, 0, 1, 'Kategorien', '{"Shopware\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\CategoryFacet":{"label":"Kategorien", "depth": "2"}}', 0),
(2, 'ImmediateDeliveryFacet', 1, 1, 2, 'Sofort lieferbar', '{"Shopware\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\ImmediateDeliveryFacet":{"label":"Sofort lieferbar"}}', 0),
(3, 'ManufacturerFacet', 1, 1, 3, 'Hersteller', '{"Shopware\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\ManufacturerFacet":{"label":"Hersteller"}}', 0),
(4, 'PriceFacet', 1, 1, 4, 'Preis', '{"Shopware\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\PriceFacet":{"label":"Preis"}}', 0),
(5, 'PropertyFacet', 1, 1, 5, 'Eigenschaften', '{"Shopware\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\PropertyFacet":[]}', 0),
(6, 'ShippingFreeFacet', 1, 1, 6, 'Versandkostenfrei', '{"Shopware\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\ShippingFreeFacet":{"label":"Versandkostenfrei"}}', 0),
(7, 'VoteAverageFacet', 1, 1, 7, 'Bewertungen', '{"Shopware\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\VoteAverageFacet":{"label":"Bewertung"}}', 0),
(8, 'WeightFacet', 0, 1, 8, 'Gewicht', '{"Shopware\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\WeightFacet":{"label":"Gewicht","suffix":"kg","digits":2}}', 0),
(9, 'WidthFacet', 0, 1, 9, 'Breite', '{"Shopware\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\WidthFacet":{"label":"Breite","suffix":"cm","digits":2}}', 0),
(10, 'HeightFacet', 0, 1, 10, 'Höhe', '{"Shopware\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\HeightFacet":{"label":"Höhe","suffix":"cm","digits":2}}', 0),
(11, 'LengthFacet', 0, 1, 11, 'Länge', '{"Shopware\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\LengthFacet":{"label":"Länge","suffix":"cm","digits":2}}', 0)
;
SQL;
        $this->addSql($sql);
    }

    private function importFacetTranslations()
    {
        $shops = $this->connection->query('SELECT id, main_id, locale_id FROM s_core_shops')->fetchAll(PDO::FETCH_ASSOC);

        foreach ($shops as $shop) {
            $translationShopId = $shop['main_id'] ?: $shop['id'];
            $localeId = $shop['locale_id'];

            $insert = $this->getExistingSortingTranslations($translationShopId, $localeId);

            if (!empty($insert)) {
                $this->addSql(
                    "INSERT INTO s_core_translations (objecttype, objectdata, objectkey, objectlanguage)
                     VALUES ('custom_facet', '" . serialize($insert) . "', '1', " . $shop['id'] . ')'
                );
            }
        }
    }

    /**
     * @param int $translationShopId
     * @param int $localeId
     *
     * @return array
     */
    private function getExistingSortingTranslations($translationShopId, $localeId)
    {
        $translations = $this->connection->query(
            "SELECT `name`, `value`
             FROM s_core_snippets
             WHERE `name` IN ('category', 'immediate_delivery', 'manufacturer', 'price', 'shipping_free', 'vote_average')
             AND namespace = 'frontend/listing/facet_labels'
             AND shopID = " . $translationShopId . ' AND localeID = ' . $localeId
        )->fetchAll(PDO::FETCH_ASSOC);

        $insert = [];
        foreach ($translations as $translation) {
            switch ($translation['name']) {
                case 'category':
                    $insert[1] = ['label' => $translation['value']];
                    break;
                case 'immediate_delivery':
                    $insert[2] = ['label' => $translation['value']];
                    break;
                case 'manufacturer':
                    $insert[3] = ['label' => $translation['value']];
                    break;
                case 'price':
                    $insert[4] = ['label' => $translation['value']];
                    break;
                case 'shipping_free':
                    $insert[6] = ['label' => $translation['value']];
                    break;
                case 'vote_average':
                    $insert[7] = ['label' => $translation['value']];
                    break;
            }
        }

        return $insert;
    }

    private function createSearchFacets()
    {
        $this->addSql("SET @formId = (SELECT id FROM s_core_config_forms WHERE name = 'Search' LIMIT 1);");

        $this->addSql("
            INSERT INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
            VALUES (@formId, 'searchFacets', 's:15:\"|1|2|3|4|5|6|7|\";', 'Verfügbare filter', '', 'custom-facet-grid', '0', '0', '1', NULL);
        ");

        $this->addSql("SET @elementId = (SELECT id FROM s_core_config_elements WHERE name = 'searchFacets' LIMIT 1);");

        $this->addSql("
            INSERT IGNORE INTO s_core_config_element_translations (element_id, locale_id, label, description)
            VALUES (@elementId, 2, 'Available filter', NULL);
        ");
    }

    private function addNewCategoryFilterParam()
    {
        $statement = $this->connection->prepare("SELECT * FROM s_core_config_elements WHERE name = 'seoqueryalias'");
        $statement->execute();
        $config = $statement->fetch(PDO::FETCH_ASSOC);

        if (empty($config)) {
            return;
        }

        $value = unserialize($config['value']);
        $value .= ',
categoryFilter=cf';

        $statement = $this->connection->prepare('UPDATE s_core_config_elements SET value = ? WHERE id = ?');
        $statement->execute([serialize($value), $config['id']]);

        $statement = $this->connection->prepare('SELECT * FROM s_core_config_values WHERE element_id = ?');
        $statement->execute([$config['id']]);
        $values = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($values as $shopValue) {
            if (empty($shopValue) || empty($shopValue['value'])) {
                continue;
            }

            $value = unserialize($shopValue['value']);
            $value .= ',
categoryFilter=cf';

            $statement = $this->connection->prepare('UPDATE s_core_config_values SET value = ? WHERE id = ?');
            $statement->execute([serialize($value), $shopValue['id']]);
        }
    }
}
