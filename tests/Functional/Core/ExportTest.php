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

namespace Shopware\Tests\Functional\Core;

use Doctrine\DBAL\Connection;
use Enlight_Template_Manager;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use sExport;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\ProductFeed\ProductFeed;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class ExportTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    private sExport $export;

    private string $cacheDir;

    /**
     * @var ModelRepository<ProductFeed>
     */
    private ModelRepository $repository;

    private Enlight_Template_Manager $template;

    private Connection $connection;

    private string $testDir;

    public function setUp(): void
    {
        $container = Shopware()->Container();

        $this->export = Shopware()->Modules()->Export();
        $this->repository = $container->get('models')->getRepository(ProductFeed::class);
        $this->template = $container->get('template');

        $this->cacheDir = $container->getParameter('shopware.product_export.cache_dir');

        $this->testDir = __DIR__ . '/fixtures/productexport/';

        if (!is_dir($this->cacheDir)) {
            if (@mkdir($this->cacheDir, 0777, true) === false) {
                throw new RuntimeException(sprintf("Unable to create directory '%s'\n", $this->cacheDir));
            }
        } elseif (!is_writable($this->cacheDir)) {
            throw new RuntimeException(sprintf("Unable to write in directory '%s'\n", $this->cacheDir));
        }

        $this->connection = $container->get('dbal_connection');
    }

    public function testNewFields(): void
    {
        $this->connection->executeQuery((string) file_get_contents($this->testDir . 'products.sql'));

        $sql = 'REPLACE INTO `s_export` (`id`, `name`, `last_export`, `active`, `hash`, `show`, `count_articles`, `expiry`, `interval`, `formatID`, `last_change`, `filename`, `encodingID`, `categoryID`, `currencyID`, `customergroupID`, `partnerID`, `languageID`, `active_filter`, `image_filter`, `stockmin_filter`, `instock_filter`, `price_filter`, `own_filter`, `header`, `body`, `footer`, `count_filter`, `multishopID`, `variant_export`, `cache_refreshed`, `dirty`) VALUES
(99, \'Test\', \'2019-11-18 19:26:59\', 1, \'be825a3aec75a7793e11ccf74caffbb9\', 0, 52, \'2019-11-18 22:46:10\', 0, 1, \'2019-11-18 22:46:10\', \'test.csv\', 2, 3, 1, 1, \'\', 1, 1, 0, 0, 0, 0, \'\', \'{strip}\narticleID{#S#}\npseudosales{#S#}\nmetaTitle{#S#}\nnotification{#S#}\navailable_from{#S#}\navailable_to{#S#}\npricegroupActive{#S#}\npricegroupID\n{/strip}{#L#}\', \'{strip}\n{$sArticle.articleID|escape}{#S#}\n{$sArticle.pseudosales|escape}{#S#}\n{$sArticle.metaTitle|escape}{#S#}\n{$sArticle.notification|escape}{#S#}\n{$sArticle.available_from|escape}{#S#}\n{$sArticle.available_to|escape}{#S#}\n{$sArticle.pricegroupActive|escape}{#S#}\n{$sArticle.pricegroupID|escape}\n{/strip}{#L#}\', \'\', 0, NULL, 1, \'2000-01-01 00:00:00\', 1);';
        $this->connection->executeQuery($sql);

        $fileName = $this->generateFeed(99);

        static::assertFileEquals(
            $this->testDir . $fileName,
            $this->cacheDir . $fileName
        );
    }

    public function testMetaTitleTranslation(): void
    {
        $this->connection->executeQuery((string) file_get_contents($this->testDir . 'products.sql'));

        $sql = 'REPLACE INTO `s_export` (`id`, `name`, `last_export`, `active`, `hash`, `show`, `count_articles`, `expiry`, `interval`, `formatID`, `last_change`, `filename`, `encodingID`, `categoryID`, `currencyID`, `customergroupID`, `partnerID`, `languageID`, `active_filter`, `image_filter`, `stockmin_filter`, `instock_filter`, `price_filter`, `own_filter`, `header`, `body`, `footer`, `count_filter`, `multishopID`, `variant_export`, `cache_refreshed`, `dirty`) VALUES
(99, \'Test\', \'2019-11-19 18:07:35\', 1, \'be825a3aec75a7793e11ccf74caffbb9\', 0, 29, \'2019-11-19 18:16:09\', 0, 1, \'2019-11-19 18:16:09\', \'test_translation.csv\', 2, 0, 1, 1, \'\', 2, 1, 0, 0, 0, 0, \'\', \'{strip}\narticleID{#S#}\npseudosales{#S#}\nmetaTitle{#S#}\nnotification{#S#}\navailable_from{#S#}\navailable_to{#S#}\npricegroupActive{#S#}\npricegroupID\n{/strip}{#L#}\', \'{strip}\n{$sArticle.articleID|escape}{#S#}\n{$sArticle.pseudosales|escape}{#S#}\n{$sArticle.metaTitle|escape}{#S#}\n{$sArticle.notification|escape}{#S#}\n{$sArticle.available_from|escape}{#S#}\n{$sArticle.available_to|escape}{#S#}\n{$sArticle.pricegroupActive|escape}{#S#}\n{$sArticle.pricegroupID|escape}\n{/strip}{#L#}\', \'\', 0, NULL, 1, \'2000-01-01 00:00:00\', 1);';
        $this->connection->executeQuery($sql);

        $sql = 'REPLACE INTO `s_core_translations` (`id`, `objecttype`, `objectdata`, `objectkey`, `objectlanguage`, `dirty`) VALUES
(NULL, \'article\', \'a:1:{s:9:\"metaTitle\";s:9:\"Meta test\";}\', 1, \'2\', 1);';
        $this->connection->executeQuery($sql);

        $fileName = $this->generateFeed(99);

        static::assertFileEquals(
            $this->testDir . $fileName,
            $this->cacheDir . $fileName
        );
    }

    public function testCustomerGroupExclusionWorks(): void
    {
        $this->connection->executeQuery((string) file_get_contents($this->testDir . 'products.sql'));

        $sql = 'REPLACE INTO `s_export` (`id`, `name`, `last_export`, `active`, `hash`, `show`, `count_articles`, `expiry`, `interval`, `formatID`, `last_change`, `filename`, `encodingID`, `categoryID`, `currencyID`, `customergroupID`, `partnerID`, `languageID`, `active_filter`, `image_filter`, `stockmin_filter`, `instock_filter`, `price_filter`, `own_filter`, `header`, `body`, `footer`, `count_filter`, `multishopID`, `variant_export`, `cache_refreshed`, `dirty`) VALUES
(99, \'Test\', \'2019-11-19 18:07:35\', 1, \'be825a3aec75a7793e11ccf74caffbb9\', 0, 29, \'2019-11-19 18:16:09\', 0, 1, \'2019-11-19 18:16:09\', \'customergroup_exlusion.csv\', 2, 0, 1, 1, \'\', 2, 1, 0, 0, 0, 0, \'\', \'{strip}\narticleID{#S#}\npseudosales{#S#}\nmetaTitle{#S#}\nnotification{#S#}\navailable_from{#S#}\navailable_to{#S#}\npricegroupActive{#S#}\npricegroupID\n{/strip}{#L#}\', \'{strip}\n{$sArticle.articleID|escape}{#S#}\n{$sArticle.pseudosales|escape}{#S#}\n{$sArticle.metaTitle|escape}{#S#}\n{$sArticle.notification|escape}{#S#}\n{$sArticle.available_from|escape}{#S#}\n{$sArticle.available_to|escape}{#S#}\n{$sArticle.pricegroupActive|escape}{#S#}\n{$sArticle.pricegroupID|escape}\n{/strip}{#L#}\', \'\', 0, NULL, 1, \'2000-01-01 00:00:00\', 1);';
        $this->connection->executeQuery($sql);

        $sql = 'INSERT INTO s_articles_avoid_customergroups (articleID, customergroupID) VALUES(1, 1)';
        $this->connection->executeQuery($sql);

        $fileName = $this->generateFeed(99);

        static::assertFileEquals(
            $this->testDir . $fileName,
            $this->cacheDir . $fileName
        );
    }

    private function generateFeed(int $feedId): string
    {
        $productFeed = $this->repository->find($feedId);
        static::assertInstanceOf(ProductFeed::class, $productFeed);

        $this->export->sFeedID = $productFeed->getId();
        $this->export->sHash = $productFeed->getHash();
        $this->export->sInitSettings();
        $this->export->sSettings['categoryID'] = 0;
        $this->export->sSmarty = clone $this->template;
        $this->export->sInitSmarty();

        $fileName = $productFeed->getHash() . '_' . $productFeed->getFileName();

        $handle = fopen($this->cacheDir . $fileName, 'w');

        if (!\is_resource($handle)) {
            throw new RuntimeException('Cannot open file');
        }

        $this->export->executeExport($handle);

        return $fileName;
    }
}
