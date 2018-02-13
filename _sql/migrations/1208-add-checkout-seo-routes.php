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

class Migrations_Migration1208 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        /*
         * This migration was intended to provide some SEO routes for checkout and account of new installations.
         *
         * It was removed due to problems with language subshops automatically inheriting SEO routes in a
         * different language.
         *
         * If you are interested in defining SEO routes for checkout and account, copy the following blocks to
         * Basic settings -> Frontend -> SEO / router settings, field "Other SEO URls".
         *
         * German:

sViewport=account,mein-konto
sViewport=account&sAction=profile,mein-konto/persoenliche-daten
sViewport=address&sAction=index,mein-konto/adressen
sViewport=account&sAction=payment,mein-konto/zahlarten
sViewport=account&sAction=orders,mein-konto/bestellungen
sViewport=account&sAction=downloads,mein-konto/downloads
sViewport=account&sAction=logout,mein-konto/abmelden
sViewport=note,merkzettel
sViewport=register,anmeldung
sViewport=checkout&sAction=index,bestellen
sViewport=checkout&sAction=cart,warenkorb
sViewport=checkout&sAction=confirm,meine-bestellung
sViewport=checkout&sAction=shippingPayment,zahlung-und-versand
sViewport=checkout,pruefen-und-bestellen
sViewport=checkout&sAction=finish,vielen-dank-fuer-ihre-bestellung

         * English:

sViewport=account,my-account
sViewport=account&sAction=profile,my-account/personal-data
sViewport=address&sAction=index,my-account/addresses
sViewport=account&sAction=payment,my-account/payment-methods
sViewport=account&sAction=orders,my-account/orders
sViewport=account&sAction=downloads,my-account/downloads
sViewport=account&sAction=logout,my-account/logout
sViewport=note,notepad
sViewport=register,signup
sViewport=checkout&sAction=index,order
sViewport=checkout&sAction=cart,basket
sViewport=checkout&sAction=confirm,my-order
sViewport=checkout&sAction=shippingPayment,payment-and-delivery
sViewport=checkout,check-and-order
sViewport=checkout&sAction=finish,thank-you-for-your-order

         */
    }
}
