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

namespace Shopware\Tests\Modules\Articles;

use Enlight_Components_Test_TestCase;
use sArticles;

class CompareTest extends Enlight_Components_Test_TestCase
{
    /**
     * Module instance
     *
     * @var sArticles
     */
    protected $module;

    /**
     * Test article ids
     *
     * @var array
     */
    protected $testArticleIds;

    /**
     * Test set up method
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->module = Shopware()->Modules()->Articles();
        $this->module->sDeleteComparisons();
        Shopware()->Container()->get('session')->offsetSet('sessionId', uniqid(rand()));
        $sql = 'SELECT `id` FROM `s_articles` WHERE `active` =1';
        $sql = Shopware()->Db()->limit($sql, 5);
        $this->testArticleIds = Shopware()->Db()->fetchCol($sql);
    }

    /**
     * Cleaning up testData
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->module->sDeleteComparisons();
    }

    /**
     * Retrieve module instance
     *
     * @return sArticles
     */
    public function Module()
    {
        return $this->module;
    }

    /**
     * Test case method
     */
    public function testDeleteComparison()
    {
        $article = $this->getTestArticleId();
        static::assertTrue($this->Module()->sAddComparison($article));
        $this->Module()->sDeleteComparison($article);
        static::assertEmpty($this->Module()->sGetComparisons());
    }

    /**
     * Test case method
     */
    public function testDeleteComparisons()
    {
        static::assertTrue($this->Module()->sAddComparison($this->getTestArticleId()));
        static::assertTrue($this->Module()->sAddComparison($this->getTestArticleId()));
        static::assertTrue($this->Module()->sAddComparison($this->getTestArticleId()));

        $this->Module()->sDeleteComparisons();
        static::assertEmpty($this->Module()->sGetComparisons());
    }

    /**
     * Test case method
     */
    public function testAddComparison()
    {
        static::assertTrue($this->Module()->sAddComparison($this->getTestArticleId()));
        static::assertNotEmpty($this->Module()->sGetComparisons());
    }

    /**
     * Test case method
     */
    public function testGetComparisons()
    {
        static::assertTrue($this->Module()->sAddComparison($this->getTestArticleId()));
        static::assertTrue($this->Module()->sAddComparison($this->getTestArticleId()));
        static::assertEquals(count($this->Module()->sGetComparisons()), 2);
    }

    /**
     * Test case method
     */
    public function testGetComparisonList()
    {
        static::assertTrue($this->Module()->sAddComparison($this->getTestArticleId()));
        static::assertTrue($this->Module()->sAddComparison($this->getTestArticleId()));
        static::assertEquals(count($this->Module()->sGetComparisonList()), 2);
    }

    /**
     * Returns a test article id
     *
     * @return int
     */
    protected function getTestArticleId()
    {
        return array_shift($this->testArticleIds);
    }
}
