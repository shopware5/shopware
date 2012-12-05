<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Controllers
 * @subpackage Bundle
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Oliver Denter
 */

/**
 *
 */
class Shopware_Controllers_Backend_SwagTestCases extends Shopware_Controllers_Backend_ExtJs
{
    protected $url = '';

    public function testAttributeExtensionAction()
    {
        $result = array();

        $page = $this->Request()->getParam('page', 1);

//        Shopware()->Cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array('Shopware_Config', 'Shopware_Plugin'));

        switch ($page) {
            case 1:
                $result[] = $this->testAddAttribute('s_articles_attributes', 'Artikel Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_articles_attributes', 'Artikel Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_articles_downloads_attributes', 'Download Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_articles_downloads_attributes', 'Download Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_articles_esd_attributes', 'ESD Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_articles_esd_attributes', 'ESD Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_articles_img_attributes', 'Image Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_articles_img_attributes', 'Image Attribute Erweiterung Entfernen');
                break;
            case 2:
                $result[] = $this->testAddAttribute('s_articles_information_attributes', 'Link Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_articles_information_attributes', 'Link Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_articles_prices_attributes', 'Preis Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_articles_prices_attributes', 'Preis Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_articles_supplier_attributes', 'Hersteller Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_articles_supplier_attributes', 'Hersteller Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_blog_attributes', 'Blog Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_blog_attributes', 'Blog Attribute Erweiterung Entfernen');
                break;
            case 3:
                $result[] = $this->testAddAttribute('s_categories_attributes', 'Kategorie Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_categories_attributes', 'Kategorie Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_cms_static_attributes', 'Shop-Seiten Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_cms_static_attributes', 'Shop-Seiten Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_cms_support_attributes', 'Formular Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_cms_support_attributes', 'Formular Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_core_auth_attributes', 'AUTH Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_core_auth_attributes', 'AUTH Attribute Erweiterung Entfernen');
                break;
            case 4:
                $result[] = $this->testAddAttribute('s_core_config_mails_attributes', 'Mail Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_core_config_mails_attributes', 'Mail Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_core_countries_attributes', 'Länder Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_core_countries_attributes', 'Länder Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_core_countries_states_attributes', 'Länder Staaten Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_core_countries_states_attributes', 'Länder Staaten Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_core_customergroups_attributes', 'Kundengruppen Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_core_customergroups_attributes', 'Kundengruppen Attribute Erweiterung Entfernen');
                break;
            case 5:
                $result[] = $this->testAddAttribute('s_core_paymentmeans_attributes', 'Zahlungsarten Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_core_paymentmeans_attributes', 'Zahlungsarten Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_emarketing_banners_attributes', 'Banner Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_emarketing_banners_attributes', 'Banner Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_emarketing_vouchers_attributes', 'Gutschein Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_emarketing_vouchers_attributes', 'Gutschein Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_emotion_attributes', 'Einkaufswelten Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_emotion_attributes', 'Einkaufswelten Attribute Erweiterung Entfernen');
                break;
            case 6:
                $result[] = $this->testAddAttribute('s_export_attributes', 'Export Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_export_attributes', 'Export Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_filter_attributes', 'Filter Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_filter_attributes', 'Filter Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_media_attributes', 'Media Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_media_attributes', 'Media Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_order_attributes', 'Bestellungs Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_order_attributes', 'Bestellungs Attribute Erweiterung Entfernen');
                break;
            case 7:
                $result[] = $this->testAddAttribute('s_order_basket_attributes', 'Warenkorb Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_order_basket_attributes', 'Warenkorb Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_order_billingaddress_attributes', 'Bestellungs-Rechnungsadresse Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_order_billingaddress_attributes', 'Bestellungs-Rechnungsadresse Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_order_details_attributes', 'Bestellungs-Position Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_order_details_attributes', 'Bestellungs-Position Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_order_documents_attributes', 'Beleg Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_order_documents_attributes', 'Beleg Attribute Erweiterung Entfernen');
                break;
            case 8:
                $result[] = $this->testAddAttribute('s_order_shippingaddress_attributes', 'Bestellungs-Lieferadressen Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_order_shippingaddress_attributes', 'Bestellungs-Lieferadressen Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_premium_dispatch_attributes', 'Versandart Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_premium_dispatch_attributes', 'Versandart Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_user_attributes', 'Kunden Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_user_attributes', 'Kunden Attribute Erweiterung Entfernen');

                $result[] = $this->testAddAttribute('s_user_billingaddress_attributes', 'Kunden-Rechnungsadresse Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_user_billingaddress_attributes', 'Kunden-Rechnungsadresse Attribute Erweiterung Entfernen');
                break;
            case 9:
                $result[] = $this->testAddAttribute('s_user_shippingaddress_attributes', 'Kunden-Lieferadressen Attribute Erweiterung Hinzufügen');
                $result[] = $this->testRemoveAttribute('s_user_shippingaddress_attributes', 'Kunden-Lieferadressen Attribute Erweiterung Entfernen');
                break;
        }

        $this->View()->assign(array(
            'success' => true,
            'data' => $result,
            'total' => 66
        ));
    }


    private function testAddAttribute($table, $headline)
    {
        $message = 'Es werden verschiedene Spalten der Tabelle : ' . $table . ' hinzugefügt. <br>
                    Danach wird die Model Generierung für diese Attribute Tabelle gestartet.';

        try {
            $this->addColumns($table);
            Shopware()->Models()->generateAttributeModels(array($table));
            $success = true;
        }
        catch (Exception $e) {
            $success = false;
            $message .= '<br><br>' . $e->getMessage();
        }

        return array(
            'success' => $success,
            'headline' => $headline,
            'description' => $message
        );
    }

    private function testRemoveAttribute($table, $headline)
    {
        $message = 'Die zuvor hinzugefügten Spalten in der Tabelle : ' . $table . ' werden wieder entfernt. <br>
                    Danach wird die Model Generierung für diese Attribute Tabelle gestartet.';

        try {
            $this->removeColumns($table);
            Shopware()->Models()->generateAttributeModels(array($table));
            $success = true;
        }
        catch (Exception $e) {
            $success = false;
            $message .= '<br><br>' . $e->getMessage();
        }

        return array(
            'success' => $success,
            'headline' => $headline,
            'description' => $message
        );
    }

    public function addColumns($tableName) {
        $sql = "
            ALTER TABLE  `$tableName`
            ADD  `int_column` INT NOT NULL ,
            ADD  `varchar_column` VARCHAR( 200 ) NOT NULL ,
            ADD  `text_column` TEXT NOT NULL ,
            ADD  `date_column` DATE NOT NULL ,
            ADD  `tinyint_column` TINYINT NOT NULL ,
            ADD  `smallint_column` SMALLINT NOT NULL ,
            ADD  `bigint_column` BIGINT NOT NULL ,
            ADD  `decimal_column` DECIMAL( 14, 2 ) NOT NULL ,
            ADD  `float_column` FLOAT NOT NULL ,
            ADD  `double_column` DOUBLE NOT NULL ,
            ADD  `datetime_column` DATETIME NOT NULL ,
            ADD  `timestamp_column` TIMESTAMP NOT NULL ,
            ADD  `char_colum` CHAR NOT NULL ,
            ADD  `longtext_column` LONGTEXT NOT NULL;
        ";
        $sql = str_replace('`$tableName`', $tableName, $sql);

        Shopware()->Db()->query($sql);
    }

    public function removeColumns($tableName)
    {
        $sql = "
            ALTER TABLE `$tableName`
              DROP `int_column`,
              DROP `varchar_column`,
              DROP `text_column`,
              DROP `date_column`,
              DROP `tinyint_column`,
              DROP `smallint_column`,
              DROP `bigint_column`,
              DROP `decimal_column`,
              DROP `float_column`,
              DROP `double_column`,
              DROP `datetime_column`,
              DROP `timestamp_column`,
              DROP `char_colum`,
              DROP `longtext_column`;
        ";

        $sql = str_replace('$tableName', $tableName, $sql);
        Shopware()->Db()->query($sql);
    }







    public function testSelfHealingAction()
    {
        $this->setUrl();

        $this->switchSelfHealing(1);

        try {
            $result = array();
            $result[] = $this->healAttributeModelsTestFailure();
            $result[] = $this->healAttributeModelsTestSuccess();
            $result[] = $this->healModelProxiesSuccess();
            $result[] = $this->healProxiesAndModelsSuccess();
        }
        catch (Exception $e) {
            $this->switchSelfHealing(1);
        }

        $this->switchSelfHealing(1);

        $this->View()->assign(array(
            'success' => true,
            'data' => $result
        ));
    }


    private function healAttributeModelsTestSuccess()
    {
        $this->switchSelfHealing(1);

        $this->removeAttributeModels();

        $response = $this->sendHttpClientRequest($this->url);

        return array(
            'success' => (bool) (strlen($response->getBody()) > 0 && $response->getStatus() === 200),
            'headline' => 'Automatische Modelgenerierung aktiviert',
            'description' => 'Das SelfHealing plugin ist aktiviert und die Attribute Models sind auf dem System gelöscht worden.<br>
                              Die Startseite des Shops wird aufgerufen => Dies führte zu keinem Fehler da das SelfHealing die Attribute Models neu generiert.'
        );

    }

    private function healAttributeModelsTestFailure()
    {
        $this->switchSelfHealing(0);

        $this->removeAttributeModels();

        $response = $this->sendHttpClientRequest($this->url);

        return array(
            'success' => (bool) (strlen($response->getBody()) === 0),
            'headline' => 'Automatische Modelgenerierung deaktiviert',
            'description' => 'Das SelfHealing plugin ist deaktiviert und die Attribute Models sind auf dem System gelöscht worden.<br>
                              Die Startseite des Shops wird aufgerufen => Dies führte zu einem Fehler da das SelfHealing deaktiviert ist.'
        );
    }

    private function healModelProxiesSuccess() 
    {
        $this->switchSelfHealing(1);

        $this->removeModelProxies();

        $response = $this->sendHttpClientRequest($this->url);

        return array(
            'success' => (bool) (strlen($response->getBody()) > 0 && $response->getStatus() === 200),
            'headline' => 'Automatische Proxygenerierung aktiviert',
            'description' => 'Das SelfHealing plugin ist aktiviert und die Proxies der Attribute Models sind auf dem System gelöscht worden.<br>
                              Die Startseite des Shops wurde aufgerufen => Dies hat zu keinem Fehler geführt da das SelfHealing die Proxies neu generiert.'
        );
    }

    private function healProxiesAndModelsSuccess()
    {
        $this->switchSelfHealing(1);

        $this->removeModelProxies();
        $this->removeAttributeModels();

        $response = $this->sendHttpClientRequest($this->url);

        return array(
            'success' => (bool) (strlen($response->getBody()) > 0  && $response->getStatus() === 200),
            'headline' => 'Automatische Proxygenerierung und Modelgenerierung aktiviert',
            'description' => 'Das SelfHealing plugin ist aktiviert und die Attribute Models sowie die Proxies der Attribute Models sind auf dem System gelöscht worden.<br>
                              Die Startseite des Shops wurde aufgerufen => Dies hat zu keinem Fehler geführt da das SelfHealing die Models und Proxies neu generiert.'
        );
    }
    
    
    
    
    
    
    
    
    
    
    ////////////////HELPER FUNCTIONS/////////////////////////

    private function setUrl() {
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $shops = $repository->findAll();
        $shop = $shops[0];

        /**@var $shop \Shopware\Models\Shop\Shop*/
        $shop->registerResources(Shopware()->Bootstrap());

        $this->url = 'http://' . $shop->getHost() . $this->Request()->getBaseUrl() ;
    }

    private function switchSelfHealing($active = 0) 
    {
        $sql= "UPDATE s_core_plugins SET active = ? WHERE name = 'SelfHealing'";
        Shopware()->Db()->query($sql, array($active));
    }

    private function sendHttpClientRequest($url)
    {
        $client = new Zend_Http_Client($url);
        return $client->request();
    }

    private function removeModelProxies()
    {
        $iterator = $this->getDirectoryIterator(
            Shopware()->AppPath('Proxies')
        );

        /**@var $file SplFileInfo*/
        foreach($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }
            if (strpos($file->getPathname(), '__CG__ShopwareModel') !== false) {
                unlink($file);
            }
        }
    }

    private function removeAttributeModels()
    {
        $iterator = $this->getDirectoryIterator(
            Shopware()->AppPath('Models_Attribute')
        );

        /**@var $file SplFileInfo*/
        foreach($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }
            unlink($file);
        }
    }

    private function getDirectoryIterator($path) {
        return new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::SELF_FIRST
        );
    }


}
