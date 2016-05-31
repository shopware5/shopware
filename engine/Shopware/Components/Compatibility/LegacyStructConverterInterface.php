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

namespace Shopware\Components\Compatibility;

use Shopware\Bundle\StoreFrontBundle;

/**
 * @category  Shopware
 * @package   Shopware\Components\Compatibility
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
interface LegacyStructConverterInterface
{
    /**
     * @param StoreFrontBundle\Struct\Country[] $countries
     * @return array
     */
    public function convertCountryStructList($countries);

    /**
     * @param StoreFrontBundle\Struct\Country $country
     * @return array
     */
    public function convertCountryStruct(StoreFrontBundle\Struct\Country $country);

    /**
     * @param StoreFrontBundle\Struct\Country\State[] $states
     * @return array
     */
    public function convertStateStructList($states);

    /**
     * @param StoreFrontBundle\Struct\Country\State $state
     * @return array
     */
    public function convertStateStruct(StoreFrontBundle\Struct\Country\State $state);

    /**
     * Converts a configurator group struct which used for default or selection configurators.
     *
     * @param StoreFrontBundle\Struct\Configurator\Group $group
     * @return array
     */
    public function convertConfiguratorGroupStruct(StoreFrontBundle\Struct\Configurator\Group $group);

    /**
     * @param StoreFrontBundle\Struct\Category $category
     * @return array
     * @throws \Exception
     */
    public function convertCategoryStruct(StoreFrontBundle\Struct\Category $category);

    /**
     * @param StoreFrontBundle\Struct\ListProduct[] $products
     * @return array
     */
    public function convertListProductStructList(array $products);

    /**
     * Converts the passed ListProduct struct to a shopware 3-4 array structure.
     *
     * @param StoreFrontBundle\Struct\ListProduct $product
     * @return array
     */
    public function convertListProductStruct(StoreFrontBundle\Struct\ListProduct $product);

    /**
     * Converts the passed ProductStream struct to an array structure.
     *
     * @param StoreFrontBundle\Struct\ProductStream $productStream
     * @return array
     */
    public function convertRelatedProductStreamStruct(StoreFrontBundle\Struct\ProductStream $productStream);

    /**
     * @param StoreFrontBundle\Struct\Product $product
     * @return array
     */
    public function convertProductStruct(StoreFrontBundle\Struct\Product $product);

    /**
     * @param StoreFrontBundle\Struct\Product\VoteAverage $average
     * @return array
     */
    public function convertVoteAverageStruct(StoreFrontBundle\Struct\Product\VoteAverage $average);

    /**
     * @param StoreFrontBundle\Struct\Product\Vote $vote
     * @return array
     */
    public function convertVoteStruct(StoreFrontBundle\Struct\Product\Vote $vote);

    /**
     * @param StoreFrontBundle\Struct\Product\Price $price
     * @return array
     */
    public function convertPriceStruct(StoreFrontBundle\Struct\Product\Price $price);

    /**
     * @param StoreFrontBundle\Struct\Media $media
     * @return array
     */
    public function convertMediaStruct(StoreFrontBundle\Struct\Media $media);

    /**
     * @param StoreFrontBundle\Struct\Product\Unit $unit
     * @return array
     */
    public function convertUnitStruct(StoreFrontBundle\Struct\Product\Unit $unit);
    

    /**
     * Example:
     *
     * return [
     *     9 => [
     *         'id' => 9,
     *         'optionID' => 9,
     *         'name' => 'Farbe',
     *         'groupID' => 1,
     *         'groupName' => 'Edelbrände',
     *         'value' => 'goldig',
     *         'values' => [
     *             53 => 'goldig',
     *         ],
     *     ],
     *     2 => [
     *         'id' => 2,
     *         'optionID' => 2,
     *         'name' => 'Flaschengröße',
     *         'groupID' => 1,
     *         'groupName' => 'Edelbrände',
     *         'value' => '0,5 Liter, 0,7 Liter, 1,0 Liter',
     *         'values' => [
     *             23 => '0,5 Liter',
     *             24 => '0,7 Liter',
     *             25 => '1,0 Liter',
     *         ],
     *     ],
     * ];
     *
     * @param StoreFrontBundle\Struct\Property\Set $set
     * @return array
     */
    public function convertPropertySetStruct(StoreFrontBundle\Struct\Property\Set $set);

    /**
     * @param StoreFrontBundle\Struct\Property\Group $group
     * @return array
     */
    public function convertPropertyGroupStruct(StoreFrontBundle\Struct\Property\Group $group);

    /**
     * @param StoreFrontBundle\Struct\Property\Option $option
     * @return array
     */
    public function convertPropertyOptionStruct(StoreFrontBundle\Struct\Property\Option $option);

    /**
     * @param StoreFrontBundle\Struct\Product\Manufacturer $manufacturer
     * @return array
     */
    public function convertManufacturerStruct(StoreFrontBundle\Struct\Product\Manufacturer $manufacturer);

    /**
     * @param StoreFrontBundle\Struct\ListProduct $product
     * @param StoreFrontBundle\Struct\Configurator\Set $set
     * @return array
     */
    public function convertConfiguratorStruct(
        StoreFrontBundle\Struct\ListProduct $product,
        StoreFrontBundle\Struct\Configurator\Set $set
    );

    /**
     * @param StoreFrontBundle\Struct\ListProduct $product
     * @param StoreFrontBundle\Struct\Configurator\Set $set
     * @return array
     */
    public function convertConfiguratorPrice(
        StoreFrontBundle\Struct\ListProduct $product,
        StoreFrontBundle\Struct\Configurator\Set $set
    );

    /**
     * Converts a configurator option struct which used for default or selection configurators.
     *
     * @param StoreFrontBundle\Struct\Configurator\Group $group
     * @param StoreFrontBundle\Struct\Configurator\Option $option
     * @return array
     */
    public function convertConfiguratorOptionStruct(
        StoreFrontBundle\Struct\Configurator\Group $group,
        StoreFrontBundle\Struct\Configurator\Option $option
    );
}
