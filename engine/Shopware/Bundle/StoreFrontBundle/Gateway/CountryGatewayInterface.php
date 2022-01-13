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

use Shopware\Bundle\StoreFrontBundle\Struct\Country;
use Shopware\Bundle\StoreFrontBundle\Struct\Country\Area;
use Shopware\Bundle\StoreFrontBundle\Struct\Country\State;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

interface CountryGatewayInterface
{
    /**
     * The \Shopware\Bundle\StoreFrontBundle\Struct\Country\State requires the following data:
     * - Country area base data
     *
     * @param int $id
     *
     * @return Area|null
     */
    public function getArea($id, ShopContextInterface $context);

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\CountryGatewayInterface::getState()
     *
     * @param int[] $ids
     *
     * @return State[]
     */
    public function getStates(array $ids, ShopContextInterface $context);

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\CountryGatewayInterface::getCountry()
     *
     * @param int[] $ids
     *
     * @return Country[]
     */
    public function getCountries(array $ids, ShopContextInterface $context);

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\CountryGatewayInterface::getArea()
     *
     * @param int[] $ids
     *
     * @return Area[]
     */
    public function getAreas(array $ids, ShopContextInterface $context);

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
     * @return Country|null
     */
    public function getCountry($id, ShopContextInterface $context);

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
     * @return State|null
     */
    public function getState($id, ShopContextInterface $context);

    /**
     * @param int[] $countryIds
     *
     * @return array<int, array<int, State>> indexed by country id contains an array of Struct\Country\State
     */
    public function getCountryStates($countryIds, ShopContextInterface $context);
}
