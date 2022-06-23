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

namespace Shopware\Tests\Functional\Bundle\SearchBundleDBAL\FacetHandler;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\LengthFacet;
use Shopware\Bundle\SearchBundle\Facet\PropertyFacet;
use Shopware\Bundle\SearchBundle\FacetResult\FacetResultGroup;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\SearchBundleDBAL\FacetHandler\PropertyFacetHandler;
use Shopware\Bundle\StoreFrontBundle\Struct\Property\Set;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware\Tests\Functional\Traits\ShopContextTrait;

class PropertyFacetHandlerTest extends TestCase
{
    use DatabaseTransactionBehaviour;
    use ContainerTrait;
    use ShopContextTrait;

    private const BOTTLE_SIZE_PROPERTY_NAME = 'Flaschengröße';

    private PropertyFacetHandler $propertyFacetHandler;

    protected function setUp(): void
    {
        $this->propertyFacetHandler = $this->getContainer()->get(PropertyFacetHandler::class);
    }

    public function testSupportsFacetValidInput(): void
    {
        static::assertTrue($this->propertyFacetHandler->supportsFacet(new PropertyFacet()));
    }

    public function testSupportsFacetInvalidInput(): void
    {
        static::assertFalse($this->propertyFacetHandler->supportsFacet(new LengthFacet()));
    }

    public function testGeneratePartialFacetSortedByPosition(): void
    {
        $context = $this->createShopContext();
        $reversedBottleProperties = $this->updateBottleSizePropertyPositions();
        $this->changeSetSortMode();

        $facetResult = $this->propertyFacetHandler->generatePartialFacet(
            new PropertyFacet(),
            new Criteria(),
            new Criteria(),
            $context
        );
        static::assertInstanceOf(FacetResultGroup::class, $facetResult);

        foreach ($facetResult->getFacetResults() as $facetResult) {
            if ($facetResult->getLabel() !== self::BOTTLE_SIZE_PROPERTY_NAME) {
                continue;
            }

            static::assertInstanceOf(ValueListFacetResult::class, $facetResult);

            $values = $facetResult->getValues();
            foreach ($reversedBottleProperties as $position => $bottlePropertyId) {
                static::assertSame((int) $bottlePropertyId, $values[$position]->getId());
            }
        }
    }

    /**
     * @return array<array<string, string>>
     */
    private function updateBottleSizePropertyPositions(): array
    {
        $connection = $this->getContainer()->get(Connection::class);

        $reversedBottleProperties = array_reverse($connection->executeQuery(
            'SELECT position, fs.id
             FROM s_filter_values AS fs
             INNER JOIN s_filter_options as fo
               ON fs.optionID = fo.id
               AND fo.name LIKE :optionName',
            ['optionName' => self::BOTTLE_SIZE_PROPERTY_NAME]
        )->fetchAllKeyValue());

        foreach ($reversedBottleProperties as $position => $bottlePropertyId) {
            $connection->update('s_filter_values', ['position' => $position], ['id' => $bottlePropertyId]);
        }

        return $reversedBottleProperties;
    }

    private function changeSetSortMode(): void
    {
        $connection = $this->getContainer()->get(Connection::class);
        $setId = $connection->executeQuery(
            'SELECT f.id
             FROM s_filter AS f
             INNER JOIN s_filter_relations AS fr
               ON f.id = fr.groupID
               AND fr.optionID = (SELECT id FROM s_filter_options WHERE name LIKE :optionName)',
            ['optionName' => self::BOTTLE_SIZE_PROPERTY_NAME]
        )->fetchOne();

        $connection->update('s_filter', ['sortMode' => Set::SORT_POSITION], ['id' => $setId]);
    }
}
