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

class Migrations_Migration1447 extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up($modus)
    {
        $sql = <<<'SQL'
CREATE TABLE `s_articles_notification_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notificationID` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `notificationID` (`notificationID`),
  CONSTRAINT `s_articles_notification_attributesibfk_1` FOREIGN KEY (`notificationID`) REFERENCES `s_articles_notification` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

SQL;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `context` = 'a:5:{s:11:"sNotifyData";a:5:{s:2:"id";s:1:"1";s:11:"ordernumber";s:7:"SW10239";s:4:"mail";s:12:"test@test.de";s:8:"language";s:1:"1";s:9:"attribute";a:2:{s:2:"id";s:1:"1";s:14:"notificationID";s:1:"1";}}s:12:"sArticleLink";s:91:"http://shopware.dev.localhost/genusswelten/koestlichkeiten/272/spachtelmasse?number=SW10239";s:12:"sOrdernumber";s:7:"SW10239";s:5:"sData";N;s:7:"product";a:94:{s:9:"articleID";i:272;s:16:"articleDetailsID";i:827;s:11:"ordernumber";s:7:"SW10239";s:9:"highlight";b:0;s:11:"description";s:0:"";s:16:"description_long";s:406:"<p>qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox.</p>";s:3:"esd";b:0;s:11:"articleName";s:13:"Spachtelmasse";s:5:"taxID";i:4;s:3:"tax";i:7;s:7:"instock";i:5555;s:11:"isAvailable";b:1;s:19:"hasAvailableVariant";b:1;s:6:"weight";i:0;s:12:"shippingtime";N;s:16:"pricegroupActive";b:0;s:12:"pricegroupID";N;s:6:"length";i:0;s:6:"height";i:0;s:5:"width";i:0;s:9:"laststock";b:0;s:14:"additionaltext";s:0:"";s:5:"datum";s:10:"2012-08-31";s:6:"update";s:10:"2018-11-06";s:5:"sales";i:0;s:13:"filtergroupID";N;s:17:"priceStartingFrom";N;s:18:"pseudopricePercent";N;s:15:"sVariantArticle";N;s:13:"sConfigurator";b:0;s:9:"metaTitle";s:0:"";s:12:"shippingfree";b:0;s:14:"suppliernumber";s:0:"";s:12:"notification";b:1;s:3:"ean";s:0:"";s:8:"keywords";s:0:"";s:12:"sReleasedate";s:0:"";s:8:"template";s:0:"";s:10:"attributes";a:2:{s:4:"core";a:23:{s:2:"id";s:3:"865";s:9:"articleID";s:3:"272";s:16:"articledetailsID";s:3:"827";s:5:"attr1";s:0:"";s:5:"attr2";s:0:"";s:5:"attr3";s:0:"";s:5:"attr4";s:0:"";s:5:"attr5";s:0:"";s:5:"attr6";s:0:"";s:5:"attr7";s:0:"";s:5:"attr8";s:0:"";s:5:"attr9";s:0:"";s:6:"attr10";s:0:"";s:6:"attr11";s:0:"";s:6:"attr12";s:0:"";s:6:"attr13";s:0:"";s:6:"attr14";s:0:"";s:6:"attr15";s:0:"";s:6:"attr16";s:0:"";s:6:"attr17";N;s:6:"attr18";s:0:"";s:6:"attr19";s:0:"";s:6:"attr20";s:0:"";}s:9:"marketing";a:4:{s:5:"isNew";b:0;s:11:"isTopSeller";b:0;s:10:"comingSoon";b:0;s:7:"storage";a:0:{}}}s:17:"allowBuyInListing";b:1;s:5:"attr1";s:0:"";s:5:"attr2";s:0:"";s:5:"attr3";s:0:"";s:5:"attr4";s:0:"";s:5:"attr5";s:0:"";s:5:"attr6";s:0:"";s:5:"attr7";s:0:"";s:5:"attr8";s:0:"";s:5:"attr9";s:0:"";s:6:"attr10";s:0:"";s:6:"attr11";s:0:"";s:6:"attr12";s:0:"";s:6:"attr13";s:0:"";s:6:"attr14";s:0:"";s:6:"attr15";s:0:"";s:6:"attr16";s:0:"";s:6:"attr17";N;s:6:"attr18";s:0:"";s:6:"attr19";s:0:"";s:6:"attr20";s:0:"";s:12:"supplierName";s:15:"The Deli Garage";s:11:"supplierImg";s:61:"http://shopware.localhost/media/image/70/ff/d6/deligarage.png";s:10:"supplierID";i:4;s:19:"supplierDescription";s:0:"";s:19:"supplier_attributes";a:0:{}s:10:"newArticle";b:0;s:9:"sUpcoming";b:0;s:9:"topseller";b:0;s:7:"valFrom";i:1;s:5:"valTo";N;s:4:"from";i:1;s:2:"to";N;s:5:"price";s:5:"17,08";s:11:"pseudoprice";s:1:"0";s:14:"referenceprice";s:1:"0";s:15:"has_pseudoprice";b:0;s:13:"price_numeric";d:17.08;s:19:"pseudoprice_numeric";i:0;s:16:"price_attributes";a:0:{}s:10:"pricegroup";s:2:"EK";s:11:"minpurchase";i:1;s:11:"maxpurchase";s:3:"100";s:13:"purchasesteps";i:1;s:12:"purchaseunit";N;s:13:"referenceunit";N;s:8:"packunit";s:0:"";s:6:"unitID";N;s:5:"sUnit";a:2:{s:4:"unit";N;s:11:"description";N;}s:15:"unit_attributes";a:0:{}s:5:"image";a:12:{s:2:"id";i:769;s:8:"position";N;s:6:"source";s:64:"http://shopware.localhost/media/image/91/ee/35/spachtelmasse.jpg";s:11:"description";s:0:"";s:9:"extension";s:3:"jpg";s:4:"main";b:1;s:8:"parentId";N;s:5:"width";i:380;s:6:"height";i:276;s:10:"thumbnails";a:3:{i:0;a:6:{s:6:"source";s:72:"http://shopware.localhost/media/image/0e/8e/f0/spachtelmasse_200x200.jpg";s:12:"retinaSource";s:75:"http://shopware.localhost/media/image/c5/fe/f6/spachtelmasse_200x200@2x.jpg";s:9:"sourceSet";s:152:"http://shopware.localhost/media/image/0e/8e/f0/spachtelmasse_200x200.jpg, http://shopware.localhost/media/image/c5/fe/f6/spachtelmasse_200x200@2x.jpg 2x";s:8:"maxWidth";s:3:"200";s:9:"maxHeight";s:3:"200";s:10:"attributes";a:0:{}}i:1;a:6:{s:6:"source";s:72:"http://shopware.localhost/media/image/96/0c/99/spachtelmasse_600x600.jpg";s:12:"retinaSource";s:75:"http://shopware.localhost/media/image/05/70/1e/spachtelmasse_600x600@2x.jpg";s:9:"sourceSet";s:152:"http://shopware.localhost/media/image/96/0c/99/spachtelmasse_600x600.jpg, http://shopware.localhost/media/image/05/70/1e/spachtelmasse_600x600@2x.jpg 2x";s:8:"maxWidth";s:3:"600";s:9:"maxHeight";s:3:"600";s:10:"attributes";a:0:{}}i:2;a:6:{s:6:"source";s:74:"http://shopware.localhost/media/image/28/g0/7e/spachtelmasse_1280x1280.jpg";s:12:"retinaSource";s:77:"http://shopware.localhost/media/image/6e/5f/c6/spachtelmasse_1280x1280@2x.jpg";s:9:"sourceSet";s:156:"http://shopware.localhost/media/image/28/g0/7e/spachtelmasse_1280x1280.jpg, http://shopware.localhost/media/image/6e/5f/c6/spachtelmasse_1280x1280@2x.jpg 2x";s:8:"maxWidth";s:4:"1280";s:9:"maxHeight";s:4:"1280";s:10:"attributes";a:0:{}}}s:10:"attributes";a:0:{}s:9:"attribute";a:0:{}}s:6:"prices";a:1:{i:0;a:22:{s:7:"valFrom";i:1;s:5:"valTo";N;s:4:"from";i:1;s:2:"to";N;s:5:"price";s:5:"17,08";s:11:"pseudoprice";s:1:"0";s:14:"referenceprice";s:1:"0";s:18:"pseudopricePercent";N;s:15:"has_pseudoprice";b:0;s:13:"price_numeric";d:17.08;s:19:"pseudoprice_numeric";i:0;s:16:"price_attributes";a:0:{}s:10:"pricegroup";s:2:"EK";s:11:"minpurchase";i:1;s:11:"maxpurchase";s:3:"100";s:13:"purchasesteps";i:1;s:12:"purchaseunit";N;s:13:"referenceunit";N;s:8:"packunit";s:0:"";s:6:"unitID";N;s:5:"sUnit";a:2:{s:4:"unit";N;s:11:"description";N;}s:15:"unit_attributes";a:0:{}}}s:10:"linkBasket";s:42:"shopware.php?sViewport=basket&sAdd=SW10239";s:11:"linkDetails";s:42:"shopware.php?sViewport=detail&sArticle=272";s:11:"linkVariant";s:57:"shopware.php?sViewport=detail&sArticle=272&number=SW10239";}}' 
WHERE `s_core_config_mails`.`name` = 'sARTICLEAVAILABLE';
EOD;
        $this->addSql($sql);
    }
}
