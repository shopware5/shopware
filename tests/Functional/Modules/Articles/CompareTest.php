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

namespace Shopware\Tests\Functional\Modules\Articles;

use Enlight_Components_Test_TestCase;
use sArticles;

class CompareTest extends Enlight_Components_Test_TestCase
{
    protected sArticles $module;

    /**
     * @var array<string>
     */
    protected array $testProductIds;

    protected function setUp(): void
    {
        parent::setUp();

        $this->module = Shopware()->Modules()->Articles();
        $this->module->sDeleteComparisons();
        Shopware()->Container()->get('session')->offsetSet('sessionId', uniqid((string) rand()));
        $sql = 'SELECT `id` FROM `s_articles` WHERE `active` =1';
        $sql = Shopware()->Db()->limit($sql, 5);
        $this->testProductIds = Shopware()->Db()->fetchCol($sql);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->module->sDeleteComparisons();
    }

    public function Module(): sArticles
    {
        return $this->module;
    }

    public function testDeleteComparison(): void
    {
        $article = $this->getTestProductId();
        static::assertTrue($this->Module()->sAddComparison($article));
        $this->Module()->sDeleteComparison($article);
        static::assertEmpty($this->Module()->sGetComparisons());
    }

    public function testDeleteComparisons(): void
    {
        static::assertTrue($this->Module()->sAddComparison($this->getTestProductId()));
        static::assertTrue($this->Module()->sAddComparison($this->getTestProductId()));
        static::assertTrue($this->Module()->sAddComparison($this->getTestProductId()));

        $this->Module()->sDeleteComparisons();
        static::assertEmpty($this->Module()->sGetComparisons());
    }

    public function testAddComparison(): void
    {
        static::assertTrue($this->Module()->sAddComparison($this->getTestProductId()));
        static::assertNotEmpty($this->Module()->sGetComparisons());
    }

    public function testGetComparisons(): void
    {
        static::assertTrue($this->Module()->sAddComparison($this->getTestProductId()));
        static::assertTrue($this->Module()->sAddComparison($this->getTestProductId()));
        static::assertCount(2, $this->Module()->sGetComparisons());
    }

    public function testGetComparisonList(): void
    {
        static::assertTrue($this->Module()->sAddComparison($this->getTestProductId()));
        static::assertTrue($this->Module()->sAddComparison($this->getTestProductId()));
        static::assertCount(2, $this->Module()->sGetComparisonList());
    }

    protected function getTestProductId(): int
    {
        return (int) array_shift($this->testProductIds);
    }
}
