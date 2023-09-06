<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Plugins\Core\HttpCache;

use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Enlight_Controller_Request_RequestTestCase;
use Exception;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\PluginInstallerBundle\Service\InstallerService;
use Shopware\Bundle\PluginInstallerBundle\Service\LegacyPluginInstaller;
use Shopware\Components\HttpCache\CacheTimeServiceInterface;
use Shopware\Components\HttpCache\DynamicCacheTimeService;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article as Product;
use Shopware\Models\Article\Detail;
use Shopware\Models\Blog\Blog;
use Shopware\Models\Category\Category;
use Shopware\Models\Emotion\Emotion;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class DynamicCacheTimeServiceTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    private CacheTimeServiceInterface $cacheTimeService;

    private int $defaultTime;

    private ModelManager $modelManager;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        Shopware()->Container()->reset(InstallerService::class);
        Shopware()->Container()->reset('shopware_plugininstaller.plugin_manager');
        Shopware()->Container()->reset(LegacyPluginInstaller::class);
        Shopware()->Container()->reset('shopware_plugininstaller.legacy_plugin_installer');

        $pluginManager = Shopware()->Container()->get(InstallerService::class);

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
        $this->cacheTimeService = $this->getContainer()->get(CacheTimeServiceInterface::class);
        $this->defaultTime = 3600;
        $this->modelManager = $this->getContainer()->get(ModelManager::class);

        Shopware()->Models()->clear();
    }

    public function testServiceIsAvailable(): void
    {
        static::assertInstanceOf(DynamicCacheTimeService::class, $this->cacheTimeService);
    }

    public function testProductDetailTimeCalculation(): void
    {
        $product = $this->createTestProduct();
        $request = $this->getProductRequest($product);
        $this->getContainer()->get('front')->setRequest($request);

        /**
         * The release date of products only contains information about the day, not minutes or seconds,
         * so the defaultTime of 1h should be used most of the time, except we're testing between 23:00 and 00:00.
         * In this case, the calculated cache time should be less than the default time.
         */
        $productCacheTime = $this->cacheTimeService->getCacheTime($request);
        static::assertLessThanOrEqual($this->defaultTime, $productCacheTime);

        $this->modelManager->remove($product);
    }

    public function testCategoryTimeCalculation(): void
    {
        $category = $this->createTestCategory();
        $request = $this->getCategoryRequest($category);
        $this->getContainer()->get('front')->setRequest($request);

        /*
         * Test if the five minute time difference is correctly recognised by the CacheTimeService.
         * Since data written to the database via "createTestCategory" is used, there might be a slight difference
         * between the creation of the resource (including activation time) and the calculation of the cache time
         * to be used. Therefore we're testing for an expected deviation (< 5s) here and not exactly 300s.
         */
        $emotionCacheTime = $this->cacheTimeService->getCacheTime($request);
        static::assertLessThan(5, abs(300 - $emotionCacheTime));

        $this->modelManager->remove($category);
    }

    public function testBlogTimeCalculation(): void
    {
        $blog = $this->createTestBlog();
        $request = $this->getBlogRequest($blog);
        $this->getContainer()->get('front')->setRequest($request);

        /*
         * Test if the five minute time difference is correctly recognised by the CacheTimeService.
         * Since data written to the database via "createTestBlog" is used, there might be a slight difference
         * between the creation of the resource (including activation time) and the calculation of the cache time
         * to be used. Therefore we're testing for an expected deviation (< 5s) here and not exactly 300s.
         */
        $blogCacheTime = $this->cacheTimeService->getCacheTime($request);
        static::assertLessThan(5, abs(300 - $blogCacheTime));

        $this->modelManager->remove($blog);
    }

    public function testBlogListingTimeCalculation(): void
    {
        $blog = $this->createTestBlogWithCategory();
        $request = $this->getBlogListingRequest($blog);
        $this->getContainer()->get('front')->setRequest($request);

        /*
         * Test if the five minute time difference is correctly recognised by the CacheTimeService.
         * Since data written to the database via "createTestBlog" is used, there might be a slight difference
         * between the creation of the resource (including activation time) and the calculation of the cache time
         * to be used. Therefore we're testing for an expected deviation (< 5s) here and not exactly 300s.
         */
        $blogCacheTime = $this->cacheTimeService->getCacheTime($request);
        static::assertLessThan(5, abs(300 - $blogCacheTime));

        $this->modelManager->remove($blog);
    }

    private function getBlogListingRequest(Blog $blog): Enlight_Controller_Request_RequestTestCase
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

    private function getBlogRequest(Blog $blog): Enlight_Controller_Request_RequestTestCase
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

    private function createTestBlog(): Blog
    {
        try {
            $blog = new Blog();
            $blog->fromArray($this->getBlogTestData());

            $this->modelManager->persist($blog);
            $this->modelManager->flush();

            return $blog;
        } catch (Exception $e) {
            static::fail($e->getMessage());
        }
    }

    private function createTestBlogWithCategory(): Blog
    {
        try {
            $blog = $this->createTestBlog();
            $blogCategory = $this->createTestCategory();

            $blog->setCategoryId($blogCategory->getId());

            $this->modelManager->persist($blog);
            $this->modelManager->flush();

            return $blog;
        } catch (Exception $e) {
            static::fail($e->getMessage());
        }
    }

    private function getCategoryRequest(Category $category): Enlight_Controller_Request_RequestTestCase
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

    private function createTestEmotion(): Emotion
    {
        try {
            $emotion = new Emotion();
            $emotion->fromArray($this->getEmotionTestData());

            $this->modelManager->persist($emotion);
            $this->modelManager->flush();

            return $emotion;
        } catch (Exception $e) {
            static::fail($e->getMessage());
        }
    }

    private function createTestCategory(): Category
    {
        try {
            $category = new Category();
            $category->setActive(true);
            $category->setName('MyTestCategory');
            $category->setParent($this->modelManager->getRepository(Category::class)->find(1));

            $emotion = $this->createTestEmotion();

            $emotion->setCategories(
                new ArrayCollection([$category])
            );

            $category->setEmotions(
                new ArrayCollection([$emotion])
            );

            $this->modelManager->persist($category);
            $this->modelManager->flush();

            return $category;
        } catch (Exception $e) {
            static::fail($e->getMessage());
        }
    }

    private function getProductRequest(Product $product): Enlight_Controller_Request_RequestTestCase
    {
        static::assertInstanceOf(Detail::class, $product->getMainDetail());
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

    private function createTestProduct(): Product
    {
        try {
            $product = new Product();

            $product->fromArray($this->getProductTestData());
            /*
             * The release date of products is only saved with day-precision,
             * so the timestamp "five minutes from now" will evaluate to the previous midnight.
             * Therefore we need to add another day, so it'll evaluate to the next midnight.
             */
            static::assertInstanceOf(Detail::class, $product->getMainDetail());
            $product->getMainDetail()->setReleaseDate((new DateTime())->add(
                new DateInterval('P1D')
            ));

            $this->modelManager->persist($product);
            $this->modelManager->flush();

            return $product;
        } catch (Exception $e) {
            static::fail($e->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function getProductTestData(): array
    {
        return [
            'name' => 'Test product',
            'description' => 'test description',
            'descriptionLong' => 'Test descriptionLong',
            'active' => true,
            'pseudoSales' => 999,
            'highlight' => true,
            'keywords' => 'test, testproduct',
            'taxId' => 1,
            'mainDetail' => [
                'active' => 1,
                'laststock' => 1,
                'number' => 'swTEST' . uniqid((string) mt_rand(), true),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getEmotionTestData(): array
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
     * @return array<string, mixed>
     */
    private function getBlogTestData(): array
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

    private function getTimestampFiveMinutesFromNow(): DateTime
    {
        return (new DateTime())->add(new DateInterval('PT5M'));
    }
}
