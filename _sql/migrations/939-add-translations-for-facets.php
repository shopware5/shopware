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

class Migrations_Migration939 extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up($modus)
    {
        $this->updateTranslation();
        $this->updateCustomerFacetAndSorting();
    }

    private function updateTranslation()
    {
        $shops = $this->connection->query('SELECT id FROM s_core_shops')->fetchAll(PDO::FETCH_ASSOC);

        foreach ($shops as $shop) {
            $translation = $this->getExistingSortingTranslations($shop['id']);

            if (!empty($translation)) {
                $id = $translation['id'];
                $data = unserialize($translation['objectdata']);

                $default = [
                    1 => ['label' => 'Categories'],
                    2 => ['label' => 'Immediately available'],
                    3 => ['label' => 'Manufacturer'],
                    4 => ['label' => 'Price'],
                    6 => ['label' => 'Shipping free'],
                    7 => ['label' => 'Rating'],
                    8 => ['label' => 'Weight'],
                    9 => ['label' => 'Width'],
                    10 => ['label' => 'Height'],
                    11 => ['label' => 'Length'],
                ];

                $data = serialize(array_replace_recursive($data, $default));

                $sql = <<<SQL
UPDATE `s_core_translations` SET `objectdata`= '$data' WHERE `id` = $id
SQL;

                $this->addSql($sql);
            }
        }
    }

    private function updateCustomerFacetAndSorting()
    {
        $sql = <<<SQL
UPDATE `s_search_custom_sorting` SET label = 'Lowest price' WHERE id = 3 and label = 'Cheapest price';
UPDATE `s_search_custom_sorting` SET label = 'Best results' WHERE id = 7 and label = 'Beste hits';
UPDATE `s_search_custom_facet` SET facet = '{"Shopware\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\CategoryFacet":{"label":"Categories", "depth": "2"}}' WHERE id = 1 and name = 'Categories';
UPDATE `s_search_custom_facet` SET facet = '{"Shopware\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\ImmediateDeliveryFacet":{"label":"Immediately available"}}' WHERE id = 2 and name = 'Immediate delivery';
UPDATE `s_search_custom_facet` SET facet = '{"Shopware\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\ManufacturerFacet":{"label":"Manufacturer"}}' WHERE id = 3 and name = 'Manufacturer';
UPDATE `s_search_custom_facet` SET facet = '{"Shopware\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\PriceFacet":{"label":"Price"}}' WHERE id = 4 and name = 'Price';
UPDATE `s_search_custom_facet` SET facet = '{"Shopware\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\ShippingFreeFacet":{"label":"Shipping free"}}' WHERE id = 6 and name = 'Shipping free';
UPDATE `s_search_custom_facet` SET facet = '{"Shopware\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\VoteAverageFacet":{"label":"Rating"}}' WHERE id = 7 and name = 'Rating';
UPDATE `s_search_custom_facet` SET facet = '{"Shopware\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\WeightFacet":{"label":"Weight","suffix":"kg","digits":2}}' WHERE id = 8 and name = 'Weight';
UPDATE `s_search_custom_facet` SET facet = '{"Shopware\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\WidthFacet":{"label":"Width","suffix":"cm","digits":2}}' WHERE id = 9 and name = 'Width';
UPDATE `s_search_custom_facet` SET facet = '{"Shopware\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\HeightFacet":{"label":"Height","suffix":"cm","digits":2}}' WHERE id = 10 and name = 'Height';
UPDATE `s_search_custom_facet` SET facet = '{"Shopware\\\\\\\Bundle\\\\\\\SearchBundle\\\\\\\Facet\\\\\\\LengthFacet":{"label":"Length","suffix":"cm","digits":2}}' WHERE id = 11 and name = 'Length';

SET @formId = (SELECT id FROM s_core_config_forms WHERE name = 'CustomSearch');

UPDATE s_core_config_form_translations SET label = 'Filter / Sorting' WHERE form_id = @formId AND label = 'Filter / Sortings';

SQL;

        $this->addSql($sql);
    }

    private function getExistingSortingTranslations($shopId)
    {
        $sql = <<<SQL
SELECT s_core_translations.id, s_core_translations.objectdata
FROM
    s_core_translations
WHERE
    s_core_translations.objecttype = 'custom_facet' AND s_core_translations.objectlanguage =  $shopId;
SQL;

        $translation = $this->connection->query($sql)->fetch();
        if (!$translation) {
            return null;
        }

        return $translation;
    }
}
