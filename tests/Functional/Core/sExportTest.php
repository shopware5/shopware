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

use Shopware\Models\ProductFeed\ProductFeed;
use Shopware\Models\ProductFeed\Repository;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Symfony\Component\DependencyInjection\ContainerInterface;

class sExportTest extends PHPUnit\Framework\TestCase
{
    use DatabaseTransactionBehaviour;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var sExport
     */
    private $export;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var Enlight_Template_Manager
     */
    private $template;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $testDir;

    public function setUp(): void
    {
        $this->container = Shopware()->Container();
        $this->export = $this->container->get('modules')->Export();
        $this->repository = $this->container->get('models')->getRepository(ProductFeed::class);
        $this->template = $this->container->get('template');
        $this->cacheDir = $this->container->getParameter('kernel.cache_dir') . '/productexport/';
        $this->testDir = __DIR__ . '/fixtures/productexport/';

        if (!is_dir($this->cacheDir)) {
            if (@mkdir($this->cacheDir, 0777, true) === false) {
                throw new \RuntimeException(sprintf("Unable to create directory '%s'\n", $this->cacheDir));
            }
        } elseif (!is_writable($this->cacheDir)) {
            throw new \RuntimeException(sprintf("Unable to write in directory '%s'\n", $this->cacheDir));
        }

        /* @var \Doctrine\DBAL\Connection $connextion */
        $this->connection = $this->container->get('dbal_connection');
    }

    public function testNewFields()
    {
        $this->connection->exec(file_get_contents($this->testDir . 'products.sql'));

        $sql = 'REPLACE INTO `s_export` (`id`, `name`, `last_export`, `active`, `hash`, `show`, `count_articles`, `expiry`, `interval`, `formatID`, `last_change`, `filename`, `encodingID`, `categoryID`, `currencyID`, `customergroupID`, `partnerID`, `languageID`, `active_filter`, `image_filter`, `stockmin_filter`, `instock_filter`, `price_filter`, `own_filter`, `header`, `body`, `footer`, `count_filter`, `multishopID`, `variant_export`, `cache_refreshed`, `dirty`) VALUES
(99, \'Test\', \'2019-11-18 19:26:59\', 1, \'be825a3aec75a7793e11ccf74caffbb9\', 0, 52, \'2019-11-18 22:46:10\', 0, 1, \'2019-11-18 22:46:10\', \'test.csv\', 2, 3, 1, 1, \'\', 1, 1, 0, 0, 0, 0, \'\', \'{strip}\narticleID{#S#}\npseudosales{#S#}\nmetaTitle{#S#}\nnotification{#S#}\navailable_from{#S#}\navailable_to{#S#}\npricegroupActive{#S#}\npricegroupID\n{/strip}{#L#}\', \'{strip}\n{$sArticle.articleID|escape}{#S#}\n{$sArticle.pseudosales|escape}{#S#}\n{$sArticle.metaTitle|escape}{#S#}\n{$sArticle.notification|escape}{#S#}\n{$sArticle.available_from|escape}{#S#}\n{$sArticle.available_to|escape}{#S#}\n{$sArticle.pricegroupActive|escape}{#S#}\n{$sArticle.pricegroupID|escape}\n{/strip}{#L#}\', \'\', 0, NULL, 1, \'2000-01-01 00:00:00\', 1);';
        $this->connection->exec($sql);

        $fileName = $this->generateFeed(99);

        static::assertFileEquals(
            $this->testDir . $fileName,
            $this->cacheDir . $fileName
        );
    }

    public function testMetaTitleTranslation()
    {
        $this->connection->exec(file_get_contents($this->testDir . 'products.sql'));

        $sql = 'REPLACE INTO `s_export` (`id`, `name`, `last_export`, `active`, `hash`, `show`, `count_articles`, `expiry`, `interval`, `formatID`, `last_change`, `filename`, `encodingID`, `categoryID`, `currencyID`, `customergroupID`, `partnerID`, `languageID`, `active_filter`, `image_filter`, `stockmin_filter`, `instock_filter`, `price_filter`, `own_filter`, `header`, `body`, `footer`, `count_filter`, `multishopID`, `variant_export`, `cache_refreshed`, `dirty`) VALUES
(99, \'Test\', \'2019-11-19 18:07:35\', 1, \'be825a3aec75a7793e11ccf74caffbb9\', 0, 29, \'2019-11-19 18:16:09\', 0, 1, \'2019-11-19 18:16:09\', \'test_translation.csv\', 2, 0, 1, 1, \'\', 2, 1, 0, 0, 0, 0, \'\', \'{strip}\narticleID{#S#}\npseudosales{#S#}\nmetaTitle{#S#}\nnotification{#S#}\navailable_from{#S#}\navailable_to{#S#}\npricegroupActive{#S#}\npricegroupID\n{/strip}{#L#}\', \'{strip}\n{$sArticle.articleID|escape}{#S#}\n{$sArticle.pseudosales|escape}{#S#}\n{$sArticle.metaTitle|escape}{#S#}\n{$sArticle.notification|escape}{#S#}\n{$sArticle.available_from|escape}{#S#}\n{$sArticle.available_to|escape}{#S#}\n{$sArticle.pricegroupActive|escape}{#S#}\n{$sArticle.pricegroupID|escape}\n{/strip}{#L#}\', \'\', 0, NULL, 1, \'2000-01-01 00:00:00\', 1);';
        $this->connection->exec($sql);

        $sql = 'REPLACE INTO `s_core_translations` (`id`, `objecttype`, `objectdata`, `objectkey`, `objectlanguage`, `dirty`) VALUES
(NULL, \'article\', \'a:1:{s:9:\"metaTitle\";s:9:\"Meta test\";}\', 1, \'2\', 1);';
        $this->connection->exec($sql);

        $fileName = $this->generateFeed(99);

        static::assertFileEquals(
            $this->testDir . $fileName,
            $this->cacheDir . $fileName
        );
    }

    private function generateFeed(int $feedId): string
    {
        /** @var ProductFeed $productFeed */
        $productFeed = $this->repository->find((int) $feedId);

        if (!$productFeed) {
            throw new Exception('Product feed not found');
        }

        $this->export->sFeedID = $productFeed->getId();
        $this->export->sHash = $productFeed->getHash();
        $this->export->sInitSettings();
        $this->export->sSettings['categoryID'] = 0;
        $this->export->sSmarty = clone $this->template;
        $this->export->sInitSmarty();

        $fileName = $productFeed->getHash() . '_' . $productFeed->getFileName();

        $handle = fopen($this->cacheDir . $fileName, 'w');

        $this->export->executeExport($handle);

        return $fileName;
    }
}
