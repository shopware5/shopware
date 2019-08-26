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

namespace Shopware\Bundle\AccountBundle\Service;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Country\Country;
use Shopware\Models\Country\State;

class CountryService implements CountryServiceInterface
{
    /**
     * @var ModelManager
     */
    private $em;

    public function __construct(ModelManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchCountryData(array $data): array
    {
        $country = null;
        $state = null;

        if (isset($data['country']) && is_numeric($data['country'])) {
            $country = $this->getCountry($data['country']);
        }

        if (!$country && isset($data['countryIso']) && ctype_print($data['countryIso'])) {
            $country = $this->getCountryByIso($data['countryIso']);
        }

        if (isset($data['state']) && is_numeric($data['state'])) {
            $state = $this->getState($data['state'], $country ? $country->getId() : null);
        }

        if ($state && !$country) {
            $country = $state->getCountry();
        } elseif (!$state && $country && isset($data['stateIso']) && ctype_print($data['stateIso'])) {
            $state = $this->getStateByIso($data['stateIso'], $country->getId());
        }
        $data['country'] = $country;
        $data['state'] = $state;

        return $data;
    }

    private function getCountry(int $countryId): ?Country
    {
        /** @var Country|null $country */
        $country = $this->em->getRepository(Country::class)->find($countryId);

        return $country;
    }

    /**
     * @throws NonUniqueResultException
     */
    private function getCountryByIso(string $countryIso): ?Country
    {
        $builder = $this->em->createQueryBuilder();
        $builder
            ->select('country')
            ->from(Country::class, 'country')
            ->where('country.iso = :iso OR country.iso3 = :iso')
            ->setParameter('iso', $countryIso)
            ->setMaxResults(1);

        return $builder->getQuery()->getOneOrNullResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    private function getState(int $stateId, ?int $countryId = null): ?State
    {
        $builder = $this->getStateQueryBuilder($countryId);
        $builder
            ->andWhere('s.id = :stateId')
            ->setParameter('stateId', $stateId);

        return $builder->getQuery()->getOneOrNullResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    private function getStateByIso(string $stateIso, int $countryId): ?State
    {
        $builder = $this->getStateQueryBuilder($countryId);
        $builder
            ->andWhere('s.shortCode = :stateIso')
            ->setParameter('stateIso', $stateIso);

        return $builder->getQuery()->getOneOrNullResult();
    }

    private function getStateQueryBuilder(?int $countryId = null): QueryBuilder
    {
        $builder = $this->em->createQueryBuilder();
        $builder
            ->select('s, c')
            ->from(State::class, 's')
            ->join('s.country', 'c')
            ->setMaxResults(1);

        if (is_int($countryId)) {
            $builder
                ->andWhere('c.id = :countryId')
                ->setParameter('countryId', $countryId);
        }

        return $builder;
    }
}
