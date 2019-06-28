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

namespace Shopware\Bundle\StoreFrontBundle\Gateway;

use Shopware\Bundle\StoreFrontBundle\Struct;

interface CountryGatewayInterface
{
    /**
     * The \Shopware\Bundle\StoreFrontBundle\Struct\Country\State requires the following data:
     * - Country area base data
     *
     * @param int $id
     *
     * @return Struct\Country\Area
     */
    public function getArea($id, Struct\ShopContextInterface $context);

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\CountryGatewayInterface::getState()
     *
     * @return Struct\Country\State[]
     */
    public function getStates(array $ids, Struct\ShopContextInterface $context);

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\CountryGatewayInterface::getCountry()
     *
     * @return Struct\Country[]
     */
    public function getCountries(array $ids, Struct\ShopContextInterface $context);

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\CountryGatewayInterface::getArea()
     *
     * @return Struct\Country\Area[]
     */
    public function getAreas(array $ids, Struct\ShopContextInterface $context);

    /**
     * The \Shopware\Bundle\StoreFrontBundle\Struct\Country requires the following data:
     * - Country base data
     * - Core attribute
     *
     * Required translation in the provided context language:
     * - Country base data
     *
     * @param int $id
     *
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Country
     */
    public function getCountry($id, Struct\ShopContextInterface $context);

    /**
     * The \Shopware\Bundle\StoreFrontBundle\Struct\Country\State requires the following data:
     * - Country state base data
     * - Core attribute
     *
     * Required translation in the provided context language:
     * - Country state base data
     *
     * @param int $id
     *
     * @return Struct\Country\State
     */
    public function getState($id, Struct\ShopContextInterface $context);

    /**
     * @param int[] $countryIds
     *
     * @return array indexed by country id contains an array of Struct\Country\State
     */
    public function getCountryStates($countryIds, Struct\ShopContextInterface $context);
}
