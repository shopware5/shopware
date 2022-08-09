<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Components\Api;

use Shopware\Components\Api\Resource\Country as CountryResource;
use Shopware\Models\Country\Area;
use Shopware\Models\Country\Country;
use Shopware\Models\Country\State;

class CountryTest extends TestCase
{
    /**
     * @var CountryResource
     */
    protected $resource;

    /**
     * @var array<int>
     */
    private static $existingCountryIds = [];

    /**
     * @var array<int>
     */
    private static $existingStatesIds = [];

    /**
     * Saves the IDs of currently existing countries and states.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$existingCountryIds = Shopware()->Db()->fetchCol(
            'SELECT id
             FROM s_core_countries'
        );
        self::$existingStatesIds = Shopware()->Db()->fetchCol(
            'SELECT id
             FROM s_core_countries_states'
        );
    }

    /**
     * Restores the state of the 's_core_countries' and 's_core_countries_states' tables
     * by deleting all entries added by this class.
     */
    public static function tearDownAfterClass(): void
    {
        parent::setUpBeforeClass();

        Shopware()->Db()->query(
            'DELETE FROM s_core_countries
             WHERE id NOT IN (' . implode(',', self::$existingCountryIds) . ')'
        );
        Shopware()->Db()->query(
            'DELETE FROM s_core_countries_states
             WHERE id NOT IN (' . implode(',', self::$existingStatesIds) . ')'
        );
    }

    /**
     * @return CountryResource
     */
    public function createResource()
    {
        return new CountryResource();
    }

    public function testCreate(): Country
    {
        $area = $this->getArea();
        $data = [
            'name' => 'Test Country',
            'iso' => 'TC',
            'iso3' => 'TCY',
            'isoName' => 'TEST COUNTRY',
            'area' => $area->getId(),
        ];

        $country = $this->resource->create($data);

        static::assertEquals($country->getName(), $data['name']);
        static::assertEquals($country->getIso(), $data['iso']);
        static::assertEquals($country->getIso3(), $data['iso3']);
        static::assertEquals($country->getIsoName(), $data['isoName']);

        static::assertNotNull($country->getArea());
        static::assertEquals($country->getArea()->getId(), $area->getId());

        return $country;
    }

    public function testCreateWithState(): Country
    {
        $state = new State();
        $state->fromArray([
            'name' => 'Test State',
            'shortCode' => 'TS',
        ]);
        Shopware()->Models()->persist($state);
        Shopware()->Models()->flush($state);

        $area = $this->getArea();
        $data = [
            'name' => 'Test Country 2',
            'iso' => 'T2',
            'iso3' => 'TC2',
            'isoName' => 'TEST COUNTRY 2',
            'area' => $area->getId(),
            'states' => [
                [
                    'id' => $state->getId(),
                    'name' => 'New State Name',
                    'shortCode' => 'NSC',
                ],
            ],
        ];

        $country = $this->resource->create($data);

        static::assertEquals($country->getName(), $data['name']);
        static::assertEquals($country->getIso(), $data['iso']);
        static::assertEquals($country->getIso3(), $data['iso3']);
        static::assertEquals($country->getIsoName(), $data['isoName']);

        static::assertNotNull($country->getArea());
        static::assertEquals($country->getArea()->getId(), $area->getId());

        static::assertEquals(1, $country->getStates()->count());
        $assignedState = $country->getStates()->first();
        static::assertEquals($assignedState->getId(), $data['states'][0]['id']);
        static::assertEquals($assignedState->getName(), $data['states'][0]['name']);
        static::assertEquals($assignedState->getShortCode(), $data['states'][0]['shortCode']);

        return $country;
    }

    /**
     * @depends testCreateWithState
     */
    public function testGetOne(Country $country): Country
    {
        $countryData = $this->resource->getOne($country->getId());
        static::assertIsArray($countryData);

        static::assertEquals($countryData['id'], $country->getId());
        static::assertEquals($countryData['name'], $country->getName());
        static::assertEquals($countryData['iso'], $country->getIso());
        static::assertEquals($countryData['iso3'], $country->getIso3());
        static::assertEquals($countryData['isoName'], $country->getIsoName());
        static::assertInstanceOf(Area::class, $country->getArea());
        static::assertEquals($countryData['areaId'], $country->getArea()->getId());

        static::assertArrayHasKey('states', $countryData);
        static::assertCount(1, $countryData['states']);
        $firstState = $country->getStates()->first();
        static::assertEquals($countryData['states'][0]['id'], $firstState->getId());
        static::assertEquals($countryData['states'][0]['name'], $firstState->getName());
        static::assertEquals($countryData['states'][0]['shortCode'], $firstState->getShortCode());
        static::assertEquals($countryData['states'][0]['countryId'], $country->getId());

        return $country;
    }

    /**
     * @depends testGetOne
     */
    public function testUpdate(Country $country): Country
    {
        $oldState = $country->getStates()->first();
        $state = new State();
        $state->fromArray([
            'name' => 'Test State 2',
            'shortCode' => 'TS2',
        ]);
        Shopware()->Models()->persist($state);
        Shopware()->Models()->flush($state);

        $area = $this->getArea(1);
        $data = [
            'name' => 'New Country Name',
            'iso' => 'NC',
            'iso3' => 'NCN',
            'isoName' => 'NEW COUNTRY',
            'area' => $area->getId(),
            'states' => [
                [
                    'id' => $oldState->getId(),
                ],
                [
                    'id' => $state->getId(),
                    'name' => 'New State 2 Name',
                    'shortCode' => 'NSC2',
                ],
            ],
        ];

        $country = $this->resource->update($country->getId(), $data);

        static::assertEquals($country->getName(), $data['name']);
        static::assertEquals($country->getIso(), $data['iso']);
        static::assertEquals($country->getIso3(), $data['iso3']);
        static::assertEquals($country->getIsoName(), $data['isoName']);

        static::assertNotNull($country->getArea());
        static::assertEquals($country->getArea()->getId(), $area->getId());

        static::assertEquals(2, $country->getStates()->count());
        $oldAssignedState = $country->getStates()->first();
        static::assertEquals($oldAssignedState->getId(), $data['states'][0]['id']);
        static::assertEquals($oldAssignedState->getName(), $oldState->getName());
        static::assertEquals($oldAssignedState->getShortCode(), $oldState->getShortCode());
        $newAssignedState = $country->getStates()->last();
        static::assertEquals($newAssignedState->getId(), $data['states'][1]['id']);
        static::assertEquals($newAssignedState->getName(), $data['states'][1]['name']);
        static::assertEquals($newAssignedState->getShortCode(), $data['states'][1]['shortCode']);

        return $country;
    }

    /**
     * @depends testUpdate
     */
    public function testGetList(Country $country): Country
    {
        $countryData = $this->resource->getList(0, 1000);

        static::assertArrayHasKey('data', $countryData);
        static::assertArrayHasKey('total', $countryData);
        static::assertCount(2 + \count(self::$existingCountryIds), $countryData['data']);
        static::assertEquals($countryData['total'], 2 + \count(self::$existingCountryIds));

        return $country;
    }

    /**
     * @depends testGetList
     */
    public function testDelete(Country $country): void
    {
        $deletedCountry = $this->resource->delete($country->getId());

        static::assertInstanceOf(Country::class, $deletedCountry);
        static::assertSame(0, (int) $deletedCountry->getId());
    }

    private function getArea(int $index = 0): Area
    {
        $areas = Shopware()->Models()->getRepository(Area::class)->findAll();

        return $areas[$index];
    }
}
