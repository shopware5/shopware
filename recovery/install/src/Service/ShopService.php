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

use Shopware\Recovery\Install\Struct\Shop;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ShopService
{
    /**
     * @var \PDO
     */
    private $connection;

    /**
     * @param \PDO $connection
     */
    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param Shop $shop
     *
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
            $sql = <<<EOT
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
     * @param Shop $shop
     *
     * @throws \RuntimeException
     */
    public function updateConfig(Shop $shop)
    {
        // Do update on shop-configuration
        if (empty($shop->name) || empty($shop->email)) {
            throw new \RuntimeException('Please fill in all required fields. (shop configuration#2)');
        }

        $this->updateMailAddress($shop);
        $this->updateFormMailAddress($shop);
        $this->updateShopName($shop);
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

    /**
     * @param Shop $shop
     */
    private function updateMailAddress(Shop $shop)
    {
        $this->updateConfigValue('mail', $shop->email);
    }

    /**
     * @param Shop $shop
     */
    private function updateShopName(Shop $shop)
    {
        $this->updateConfigValue('shopName', $shop->name);
    }

    /**
     * @param Shop $shop
     */
    private function updateFormMailAddress(Shop $shop)
    {
        $this->connection
            ->prepare('UPDATE s_cms_support SET email = ? WHERE email = ?')
            ->execute([$shop->email, 'info@example.com']);
    }

    /**
     * @param string $elementName
     * @param mixed  $value
     */
    private function updateConfigValue($elementName, $value)
    {
        $sql = <<<EOT
DELETE
FROM s_core_config_values
WHERE element_id =
    (SELECT id FROM s_core_config_elements WHERE name=:elementName)
AND shop_id = 1
EOT;
        $this->connection->prepare($sql)->execute([
            'elementName' => $elementName,
        ]);

        $sql = <<<EOT
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
