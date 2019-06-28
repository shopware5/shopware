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

namespace Shopware\Recovery\Install\Service;

use Shopware\Recovery\Common\Service\UniqueIdGenerator;
use Shopware\Recovery\Install\Struct\Shop;

class ShopService
{
    /**
     * @var \PDO
     */
    private $connection;

    /**
     * @var UniqueIdGenerator
     */
    private $generator;

    public function __construct(\PDO $connection, UniqueIdGenerator $generator)
    {
        $this->connection = $connection;
        $this->generator = $generator;
    }

    /**
     * @throws \RuntimeException
     */
    public function updateShop(Shop $shop)
    {
        if (empty($shop->locale)
            || empty($shop->host)
        ) {
            throw new \RuntimeException('Please fill in all required fields. (shop configuration)');
        }

        try {
            $fetchLanguageId = $this->getLocaleIdByLocale($shop->locale);

            // Update s_core_shops
            $sql = <<<'EOT'
UPDATE
    s_core_shops
SET
    `name` = ?,
    locale_id =  ?,
    host = ?,
    base_path = ?,
    hosts = ?
WHERE
    `default` = 1
EOT;

            $prepareStatement = $this->connection->prepare($sql);
            $prepareStatement->execute([
                $shop->name,
                $fetchLanguageId,
                $shop->host,
                $shop->basePath,
                $shop->host,
            ]);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws \RuntimeException
     */
    public function updateConfig(Shop $shop)
    {
        // Do update on shop-configuration
        if (empty($shop->name) || empty($shop->email)) {
            throw new \RuntimeException('Please fill in all required fields. (shop configuration#2)');
        }

        $this->updateMailAddresses($shop);
        $this->updateShopName($shop);
        $this->generateEsdKey();
    }

    /**
     * @param string $locale
     *
     * @return int
     */
    protected function getLocaleIdByLocale($locale)
    {
        $fetchLanguageId = $this->connection->prepare(
            'SELECT id FROM s_core_locales WHERE locale = ?'
        );
        $fetchLanguageId->execute([$locale]);
        $fetchLanguageId = $fetchLanguageId->fetchColumn();
        if (!$fetchLanguageId) {
            throw new \RuntimeException('Language with id ' . $locale . ' not found');
        }

        return (int) $fetchLanguageId;
    }

    private function updateMailAddresses(Shop $shop)
    {
        $this->updateConfigValue('mail', $shop->email);

        $sql = 'UPDATE `s_cms_support` SET email = :email';
        $prepareStatement = $this->connection->prepare($sql);
        $prepareStatement->execute(['email' => $shop->email]);

        $sql = 'UPDATE `s_campaigns_sender` SET email = :email';
        $prepareStatement = $this->connection->prepare($sql);
        $prepareStatement->execute(['email' => $shop->email]);
    }

    private function updateShopName(Shop $shop)
    {
        $this->updateConfigValue('shopName', $shop->name);
    }

    private function generateEsdKey()
    {
        $this->updateConfigValue('esdKey', strtolower($this->generator->generateUniqueId(33)));
    }

    /**
     * @param string $elementName
     */
    private function updateConfigValue($elementName, $value)
    {
        $sql = <<<'EOT'
DELETE
FROM s_core_config_values
WHERE element_id =
    (SELECT id FROM s_core_config_elements WHERE name=:elementName)
AND shop_id = 1
EOT;
        $this->connection->prepare($sql)->execute([
            'elementName' => $elementName,
        ]);

        $sql = <<<'EOT'
INSERT INTO `s_core_config_values`
(`id`, `element_id`, `shop_id`, `value`) VALUES
(NULL, (SELECT id FROM s_core_config_elements WHERE name=:elementName), 1, :value);
EOT;

        $prepared = $this->connection->prepare($sql);
        $prepared->execute([
            'elementName' => $elementName,
            'value' => serialize($value),
        ]);
    }
}
