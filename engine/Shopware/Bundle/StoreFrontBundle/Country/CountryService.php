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

namespace Shopware\Bundle\StoreFrontBundle\Country;

use Doctrine\DBAL\Connection;
use Shopware\Context\Struct\ShopContext;
use Shopware\Country\Gateway\CountryReader;
use Shopware\Country\Struct\Country;
use Shopware\CountryState\Struct\CountryState;

class CountryService implements CountryServiceInterface
{
    /**
     * @var CountryReader
     */
    private $gateway;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * CountryService constructor.
     *
     * @param CountryReader $gateway
     * @param Connection     $connection
     */
    public function __construct(CountryReader $gateway, Connection $connection)
    {
        $this->gateway = $gateway;
        $this->connection = $connection;
    }

    /**
     * Returns all available countries for the provided shop context
     *
     * @param \Shopware\Context\Struct\ShopContext $context
     *
     * @return Country[] indexed by id
     */
    public function getAvailable(ShopContext $context)
    {
        $ids = $this->getCountryIds();
        $countries = $this->gateway->read($ids, $context->getTranslationContext());
        $states = $this->gateway->getCountryStates($ids, $context->getTranslationContext());

        $result = [];
        foreach ($countries as $country) {
            if (!$country->isActive()) {
                continue;
            }

            if (isset($states[$country->getId()])) {
                $country->setStates(
                    $this->sortStates($states[$country->getId()])
                );
            }

            $result[$country->getId()] = $country;
        }

        return $this->sortCountries($result);
    }

    /**
     * @return int[]
     */
    private function getCountryIds()
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('id');
        $query->from('s_core_countries', 'country');

        return $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @param CountryState[] $countryStates
     *
     * @return CountryState[]
     */
    private function sortStates($countryStates)
    {
        usort($countryStates, function (
            CountryState $a, CountryState $b) {
            if ($a->getPosition() == $b->getPosition()) {
                return strnatcasecmp($a->getName(), $b->getName());
            }

            return ($a->getPosition() < $b->getPosition()) ? -1 : 1;
        });

        return $countryStates;
    }

    /**
     * @param Country[] $countries
     *
     * @return Country[]
     */
    private function sortCountries($countries)
    {
        usort($countries, function (Country $a, Country $b) {
            if ($a->getPosition() == $b->getPosition()) {
                return strnatcasecmp($a->getCountryName(), $b->getCountryName());
            }

            return ($a->getPosition() < $b->getPosition()) ? -1 : 1;
        });

        return $countries;
    }
}
