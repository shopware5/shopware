<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\Components;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Components\ReflectionHelper;
use Shopware_Components_CsvIterator;

class ReflectionHelperTest extends TestCase
{
    /**
     * @var ReflectionHelper
     */
    private $helper;

    public function setUp(): void
    {
        parent::setUp();
        $this->helper = new ReflectionHelper();
    }

    public function testCriteriaCreation()
    {
        $criteria = $this->helper->createInstanceFromNamedArguments(Criteria::class, []);
        static::assertInstanceOf(Criteria::class, $criteria);
    }

    public function testOutOfFolderCreation()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/out of scope/m');
        $this->helper->createInstanceFromNamedArguments(NullLogger::class, []);
    }

    public function testCreationOfInvalidClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class Shopware_Components_CsvIterator has to implement the interface Shopware\Components\ReflectionAwareInterface');
        $this->helper->createInstanceFromNamedArguments(Shopware_Components_CsvIterator::class, []);
    }
}
