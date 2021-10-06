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

namespace Shopware\Bundle\SitemapBundle\ConfigHandler;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Shopware\Bundle\SitemapBundle\Service\ConfigHandler;

class Database implements ConfigHandlerInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getSitemapConfig(): array
    {
        return [
            ConfigHandler::EXCLUDED_URLS_KEY => $this->fetchExcludedUrls(),
            ConfigHandler::CUSTOM_URLS_KEY => $this->fetchCustomUrls(),
        ];
    }

    public function saveCustomUrls(array $customUrls): void
    {
        // Easier than to detect changes, e.g. like deleting a row while adding a new one.
        $this->connection->exec('TRUNCATE s_sitemap_custom');

        $customUrls = array_map(static function (array $customUrl) {
            if (\array_key_exists('shopId', $customUrl)) {
                $customUrl['shop_id'] = $customUrl['shopId'];
                unset($customUrl['shopId']);
            }

            if (\array_key_exists('lastMod', $customUrl)) {
                $customUrl['last_mod'] = $customUrl['lastMod'];
                unset($customUrl['lastMod']);
            }

            if (!$customUrl['last_mod']) {
                $customUrl['last_mod'] = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
            }

            if (isset($customUrl['changeFreq'])) {
                $customUrl['change_freq'] = $customUrl['changeFreq'];
                unset($customUrl['changeFreq']);
            }

            unset($customUrl['id']);

            return $customUrl;
        }, $customUrls);

        foreach ($customUrls as $customUrl) {
            $this->connection->insert(
                's_sitemap_custom',
                $customUrl
            );
        }
    }

    public function saveExcludedUrls(array $excludedUrls): void
    {
        // Easier than to detect changes, e.g. like deleting a row while adding a new one.
        $this->connection->exec('TRUNCATE s_sitemap_exclude');

        $excludedUrls = array_map(static function (array $excludedUrl) {
            if (\array_key_exists('shopId', $excludedUrl)) {
                $excludedUrl['shop_id'] = $excludedUrl['shopId'];
                unset($excludedUrl['shopId']);
            }

            if ($excludedUrl['identifier'] === '') {
                $excludedUrl['identifier'] = null;
            }

            unset($excludedUrl['id']);

            return $excludedUrl;
        }, $excludedUrls);

        foreach ($excludedUrls as $excludedUrl) {
            $this->connection->insert(
                's_sitemap_exclude',
                $excludedUrl
            );
        }
    }

    private function fetchExcludedUrls(): array
    {
        $excludedUrls = $this->connection->fetchAll('SELECT * FROM s_sitemap_exclude');

        $excludedUrls = array_map(static function (array $excludedUrl) {
            $excludedUrl['shopId'] = $excludedUrl['shop_id'];
            unset($excludedUrl['shop_id']);

            return $excludedUrl;
        }, $excludedUrls);

        return $excludedUrls;
    }

    private function fetchCustomUrls(): array
    {
        $customUrls = $this->connection->fetchAll('SELECT * FROM s_sitemap_custom');

        $customUrls = array_map(static function (array $customUrl) {
            $customUrl['lastMod'] = $customUrl['last_mod'];
            $customUrl['changeFreq'] = $customUrl['change_freq'];
            $customUrl['shopId'] = $customUrl['shop_id'];

            unset($customUrl['shop_id'], $customUrl['last_mod'], $customUrl['change_freq']);

            return $customUrl;
        }, $customUrls);

        return $customUrls;
    }
}
