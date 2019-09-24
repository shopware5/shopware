<?php declare(strict_types=1);

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

use Shopware\Bundle\SitemapBundle\Provider\BlogUrlProvider;
use Shopware\Bundle\SitemapBundle\Provider\CategoryUrlProvider;
use Shopware\Bundle\SitemapBundle\Provider\LandingPageUrlProvider;
use Shopware\Bundle\SitemapBundle\Provider\ManufacturerUrlProvider;
use Shopware\Bundle\SitemapBundle\Provider\ProductUrlProvider;
use Shopware\Bundle\SitemapBundle\Provider\StaticUrlProvider;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Blog\Blog;
use Shopware\Models\Category\Category;
use Shopware\Models\Emotion\Emotion;
use Shopware\Models\Site\Site;

class SitemapConfigMigrationHelper
{
    /**
     * @var PDO
     */
    private $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function migrate(): void
    {
        $customConfigurationPath = dirname(__DIR__, 3) . '/config.php';

        if (!file_exists($customConfigurationPath)) {
            return;
        }

        $customConfiguration = require $customConfigurationPath;
        $sitemapConfiguration = $customConfiguration['sitemap'];

        if ($sitemapConfiguration['custom_urls']) {
            $this->migrateCustomUrls($sitemapConfiguration['custom_urls']);
        }

        if ($sitemapConfiguration['excluded_urls']) {
            $this->migrateExcludedUrls($sitemapConfiguration['excluded_urls']);
        }
    }

    private function migrateCustomUrls(array $customUrls): void
    {
        $sqlStatement = <<<SQL
            INSERT INTO `s_sitemap_custom`
            (`url`, `change_freq`, `last_mod`, `priority`, `shop_id`)
            VALUES (:url, :changeFreq, :lastMod, :priority, :shopId)
SQL;

        foreach ($customUrls as $customUrl) {
            $statement = $this->connection->prepare($sqlStatement);
            $shopId = $customUrl['shopId'] ?: null;

            $statement->bindParam(':url', $customUrl['url']);
            $statement->bindParam(':changeFreq', $customUrl['changeFreq']);
            $statement->bindParam(':lastMod', $customUrl['lastMod']);
            $statement->bindParam(':priority', $customUrl['priority']);
            $statement->bindParam(':shopId', $shopId);

            $statement->execute();
        }
    }

    private function migrateExcludedUrls(array $excludedUrls): void
    {
        $sqlStatement = <<<SQL
            INSERT INTO `s_sitemap_exclude`
            (`resource`, `identifier`, `shop_id`)
            VALUES (:resource, :identifier, :shopId)
SQL;

        foreach ($excludedUrls as $excludedUrl) {
            $statement = $this->connection->prepare($sqlStatement);
            $identifier = $excludedUrl['identifier'] ?: null;
            $shopId = $excludedUrl['shopId'] ?: null;
            $statement->bindParam(':resource', $excludedUrl['resource']);
            $statement->bindParam(':identifier', $identifier);
            $statement->bindParam(':shopId', $shopId);

            $statement->execute();
        }
    }
}
