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

namespace Shopware\Tests\Functional\Plugins\Core\HttpCache;

use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Enlight_Controller_Request_RequestTestCase;
use Exception;
use PHPUnit\Framework\TestCase;
use Shopware\Components\HttpCache\CacheTimeServiceInterface;
use Shopware\Components\HttpCache\DynamicCacheTimeService;
use Shopware\Models\Article\Article;
use Shopware\Models\Blog\Blog;
use Shopware\Models\Category\Category;
use Shopware\Models\Emotion\Emotion;
use Shopware\Models\Plugin\Plugin;

class DynamicCacheTimeServiceTest extends TestCase
{
    /** @var CacheTimeServiceInterface */
    private $cacheTimeService;

    /** @var array */
    private $routes = [];

    /** @var int */
    private $defaultTime;

    /** @var int */
    private $defaultBlogTime;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        $pluginManager = Shopware()->Container()->get('shopware_plugininstaller.plugin_manager');

        /** @var Plugin $plugin */
        $plugin = $pluginManager->getPluginByName('HttpCache');

        $pluginManager->installPlugin($plugin);
        $pluginManager->activatePlugin($plugin);
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass(): void
    {
        // Flush, so the entities created and removed by the test methods actually get deleted.
        Shopware()->Models()->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->cacheTimeService = Shopware()->Container()->get('shopware.http_cache.cache_time_service');

        $this->defaultTime = 3600;
        $this->defaultBlogTime = 14400;

        $this->routes = [
            'frontend/detail' => $this->defaultTime,
            'frontend/listing' => $this->defaultTime,
            'frontend/blog' => $this->defaultBlogTime,
        ];
    }

    public function testServiceIsAvailable()
    {
        static::assertInstanceOf(DynamicCacheTimeService::class, $this->cacheTimeService);
    }

    public function testProductDetailTimeCalculation()
    {
        $product = $this->createTestProduct();
        $request = $this->getProductRequest($product);
        Shopware()->Container()->get('front')->setRequest($request);

        /**
         * The release date of products only contains information about the day, not minutes or seconds,
         * so the defaultTime of 1h should be used most of the time, except we're testing between 23:00 and 00:00.
         * In this case, the calculated cache time should be less than the default time.
         */
        $productCacheTime = $this->cacheTimeService->getCacheTime($request);
        static::assertLessThanOrEqual($this->defaultTime, $productCacheTime);

        Shopware()->Models()->remove($product);
    }

    public function testCategoryTimeCalculation()
    {
        $category = $this->createTestCategory();
        $request = $this->getcategoryRequest($category);
        Shopware()->Container()->get('front')->setRequest($request);

        /*
         * Test if the five minute time difference is correctly recognised by the CacheTimeService.
         * Since data written to the database via "createTestCategory" is used, there might be a slight difference
         * between the creation of the resource (including activation time) and the calculation of the cache time
         * to be used. Therefore we're testing for an expected deviation (< 5s) here and not exactly 300s.
         */
        $emotionCacheTime = $this->cacheTimeService->getCacheTime($request);
        static::assertLessThan(5, abs(300 - $emotionCacheTime));

        Shopware()->Models()->remove($category);
    }

    public function testBlogTimeCalculation()
    {
        $blog = $this->createTestBlog();
        $request = $this->getBlogRequest($blog);
        Shopware()->Container()->get('front')->setRequest($request);

        /*
         * Test if the five minute time difference is correctly recognised by the CacheTimeService.
         * Since data written to the database via "createTestBlog" is used, there might be a slight difference
         * between the creation of the resource (including activation time) and the calculation of the cache time
         * to be used. Therefore we're testing for an expected deviation (< 5s) here and not exactly 300s.
         */
        $blogCacheTime = $this->cacheTimeService->getCacheTime($request);
        static::assertLessThan(5, abs(300 - $blogCacheTime));

        Shopware()->Models()->remove($blog);
    }

    public function testBlogListingTimeCalculation()
    {
        $blog = $this->createTestBlogWithCategory();
        $request = $this->getBlogListingRequest($blog);
        Shopware()->Container()->get('front')->setRequest($request);

        /*
         * Test if the five minute time difference is correctly recognised by the CacheTimeService.
         * Since data written to the database via "createTestBlog" is used, there might be a slight difference
         * between the creation of the resource (including activation time) and the calculation of the cache time
         * to be used. Therefore we're testing for an expected deviation (< 5s) here and not exactly 300s.
         */
        $blogCacheTime = $this->cacheTimeService->getCacheTime($request);
        static::assertLessThan(5, abs(300 - $blogCacheTime));

        Shopware()->Models()->remove($blog);
    }

    /**
     * @return Enlight_Controller_Request_RequestTestCase
     */
    private function getBlogListingRequest(Blog $blog)
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams([
            'module' => 'frontend',
            'controller' => 'blog',
            'action' => 'index',
            'sCategory' => sprintf('%d', $blog->getCategoryId()),
        ]);

        return $request;
    }

    /**
     * @return Enlight_Controller_Request_RequestTestCase
     */
    private function getBlogRequest(Blog $blog)
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams([
            'module' => 'frontend',
            'controller' => 'blog',
            'action' => 'detail',
            'blogArticle' => sprintf('%d', $blog->getId()),
        ]);

        return $request;
    }

    /**
     * @return Blog|null
     */
    private function createTestBlog()
    {
        try {
            $blog = new Blog();
            $blog->fromArray($this->getBlogTestData());

            Shopware()->Models()->persist($blog);
            Shopware()->Models()->flush();

            return $blog;
        } catch (Exception $e) {
            static::fail($e->getMessage());

            return null;
        }
    }

    /**
     * @return Blog|null
     */
    private function createTestBlogWithCategory()
    {
        try {
            $blog = $this->createTestBlog();
            $blogCategory = $this->createTestCategory();

            $blog->setCategoryId($blogCategory->getId());

            Shopware()->Models()->persist($blog);
            Shopware()->Models()->flush();

            return $blog;
        } catch (Exception $e) {
            static::fail($e->getMessage());

            return null;
        }
    }

    /**
     * @return Enlight_Controller_Request_RequestTestCase
     */
    private function getcategoryRequest(Category $category)
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams([
            'module' => 'frontend',
            'controller' => 'listing',
            'action' => 'index',
            'sCategory' => sprintf('%d', $category->getId()),
        ]);

        return $request;
    }

    /**
     * @return Emotion|null
     */
    private function createTestEmotion()
    {
        try {
            $emotion = new Emotion();
            $emotion->fromArray($this->getEmotionTestData());

            Shopware()->Models()->persist($emotion);
            Shopware()->Models()->flush();

            return $emotion;
        } catch (Exception $e) {
            static::fail($e->getMessage());

            return null;
        }
    }

    /**
     * @return Category|null
     */
    private function createTestCategory()
    {
        try {
            $category = new Category();
            $category->setActive(1);
            $category->setName('MyTestCategory');
            $category->setParent(Shopware()->Models()->getRepository(Category::class)->find(1));

            $emotion = $this->createTestEmotion();

            $emotion->setCategories(
                new ArrayCollection([$category])
            );

            $category->setEmotions(
                new ArrayCollection([$emotion])
            );

            Shopware()->Models()->persist($category);
            Shopware()->Models()->flush();

            return $category;
        } catch (Exception $e) {
            static::fail($e->getMessage());

            return null;
        }
    }

    /**
     * @return Enlight_Controller_Request_RequestTestCase
     */
    private function getProductRequest(Article $product)
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams([
            'module' => 'frontend',
            'controller' => 'detail',
            'action' => 'index',
            'sArticle' => sprintf('%d', $product->getId()),
            'number' => $product->getMainDetail()->getNumber(),
        ]);

        return $request;
    }

    /**
     * @return Article|null
     */
    private function createTestProduct()
    {
        try {
            $product = new Article();

            $product->fromArray($this->getProductTestData());
            /*
             * The release date of products is only saved with day-precision,
             * so the timestamp "five minutes from now" will evaluate to the previous midnight.
             * Therefore we need to add another day, so it'll evaluate to the next midnight.
             */
            $product->getMainDetail()->setReleaseDate((new DateTime())->add(
                new DateInterval('P1D')
            ));

            Shopware()->Models()->persist($product);
            Shopware()->Models()->flush();

            return $product;
        } catch (Exception $e) {
            static::fail($e->getMessage());

            return null;
        }
    }

    /**
     * @return array
     */
    private function getProductTestData()
    {
        return [
            'name' => 'Testarticle',
            'description' => 'testdescription',
            'descriptionLong' => 'Test descriptionLong',
            'active' => true,
            'pseudoSales' => 999,
            'highlight' => true,
            'keywords' => 'test, testarticle',
            'taxId' => 1,
            'mainDetail' => [
                'active' => 1,
                'laststock' => 1,
                'number' => 'swTEST' . uniqid(mt_rand(), true),
            ],
        ];
    }

    /**
     * @return array
     */
    private function getEmotionTestData()
    {
        return [
            'active' => 1,
            'name' => 'MyTestEmotion',
            'cellSpacing' => 4,
            'cellHeight' => 4,
            'articleHeight' => 4,
            'rows' => 6,
            'validFrom' => $this->getTimestampFiveMinutesFromNow(),
            'showListing' => 0,
            'isLandingpage' => 0,
            'seoTitle' => 'abd',
            'seoKeywords' => 'this, emotion, doesnt, need, keywords',
            'seoDescription' => 'def',
            'fullscreen' => 0,
            'mode' => 'fluid',
        ];
    }

    /**
     * @return array
     */
    private function getBlogTestData()
    {
        return [
            'title' => 'MyTestBlog',
            'active' => 1,
            'shortDescription' => 'A short description.',
            'description' => 'Description',
            'displayDate' => $this->getTimestampFiveMinutesFromNow(),
            'template' => '',
        ];
    }

    /**
     * @return DateTime
     */
    private function getTimestampFiveMinutesFromNow()
    {
        return (new DateTime())->add(new \DateInterval('PT5M'));
    }
}
