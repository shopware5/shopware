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

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration1208 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // This checkout routes are supposed to only be used on new installations
        if ($modus === AbstractMigration::MODUS_UPDATE) {
            return;
        }

        $conn = $this->getConnection();

        $shops = $conn->query('SELECT shops.id, locales.locale FROM s_core_shops shops INNER JOIN s_core_locales locales ON locales.id=shops.locale_id')
            ->fetchAll(\Pdo::FETCH_ASSOC);

        foreach ($shops as $shop) {
            switch (strtolower(substr($shop['locale'], 0, 2))) {
                case 'de':
                    $this->addGermanRoutes($shop['id']);
                    break;

                case 'en':
                    $this->addEnglishRoutes($shop['id']);
                    break;
            }
        }
    }

    /**
     * @param int $shopId
     */
    private function addGermanRoutes($shopId)
    {
        $this->addStaticRoutes([
                'sViewport=account,mein-konto',
                'sViewport=account&sAction=profile,mein-konto/persoenliche-daten',
                'sViewport=address&sAction=index,mein-konto/adressen',
                'sViewport=account&sAction=payment,mein-konto/zahlarten',
                'sViewport=account&sAction=orders,mein-konto/bestellungen',
                'sViewport=account&sAction=downloads,mein-konto/downloads',
                'sViewport=account&sAction=logout,mein-konto/abmelden',
                'sViewport=note,merkzettel',

                'sViewport=register,anmeldung',
                'sViewport=checkout&sAction=index,bestellen',
                'sViewport=checkout&sAction=cart,warenkorb',
                'sViewport=checkout&sAction=confirm,meine-bestellung',
                'sViewport=checkout&sAction=shippingPayment,zahlung-und-versand',
                'sViewport=checkout,pruefen-und-bestellen',
                'sViewport=checkout&sAction=finish,vielen-dank-fuer-ihre-bestellung',
        ], $shopId);
    }

    /**
     * @param int $shopId
     */
    private function addEnglishRoutes($shopId)
    {
        $this->addStaticRoutes([
                'sViewport=account,my-account',
                'sViewport=account&sAction=profile,my-account/personal-data',
                'sViewport=address&sAction=index,my-account/addresses',
                'sViewport=account&sAction=payment,my-account/payment-methods',
                'sViewport=account&sAction=orders,my-account/orders',
                'sViewport=account&sAction=downloads,my-account/downloads',
                'sViewport=account&sAction=logout,my-account/logout',
                'sViewport=note,notepad',

                'sViewport=register,signup',
                'sViewport=checkout&sAction=index,order',
                'sViewport=checkout&sAction=cart,basket',
                'sViewport=checkout&sAction=confirm,my-order',
                'sViewport=checkout&sAction=shippingPayment,payment-and-delivery',
                'sViewport=checkout,check-and-order',
                'sViewport=checkout&sAction=finish,thank-you-for-your-order',
        ], $shopId);
    }

    /**
     * @param array $staticRoutes
     * @param int   $shopId
     */
    private function addStaticRoutes(array $staticRoutes, $shopId)
    {
        $conn = $this->getConnection();

        $elementId = $conn->query('SELECT id FROM s_core_config_elements WHERE `name`="seostaticurls"')
            ->fetchColumn(0);

        $elementValueStatement = $conn->prepare('SELECT id, value FROM s_core_config_values WHERE `element_id`=:elementId AND `shop_id`=:shopId');
        $elementValueStatement->execute([
            'elementId' => $elementId,
            'shopId' => $shopId,
        ]);

        if (!empty($elementValueStatement->fetch(\PDO::FETCH_ASSOC))) {
            return;
        }

        $insert = $conn->prepare('INSERT INTO s_core_config_values (element_id, shop_id, value) VALUES (:elementId, :shopId, :value)');
        $insert->execute([
            'elementId' => $elementId,
            'shopId' => $shopId,
            'value' => serialize(implode("\n", $staticRoutes)),
        ]);
    }
}
