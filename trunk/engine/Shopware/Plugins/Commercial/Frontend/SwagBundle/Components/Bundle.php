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
 */

use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;

/**
 * Global Shopware Bundle component.
 *
 * The Shopware_Components_Bundle component contains all global
 * shopware logic to calculate bundle prices, add bundles to
 * the basket or validate bundles for the current Shopware_Session.
 * The basket component are registered as Shopware resource, which allows
 * you to call the component function via Shopware()->Bundle().
 * The protected class properties are used for php unit test.
 *
 * @category Shopware
 * @package Shopware\Plugins\SwagBundle\Components
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Components_Bundle extends Enlight_Class
{
    /**
     * Constant for the bundle type "normal"
     *
     * The bundle contains a defined price discount and the customer can only buy the complete bundle or not.
     */
    const NORMAL_BUNDLE = 1;

    /**
     * Constant for the bundle type "selectable"
     *
     * The bundle contains no defined price discount but the customer can select the bundle article position.
     */
    const SELECTABLE_BUNDLE = 2;

    /**
     * Constant for the bundle discount type "percentage"
     *
     * If the bundle discount defined as percentage the bundle prices will be calculate by percentage for each customer group.
     */
    const PERCENTAGE_DISCOUNT = 'pro';

    /**
     * Constant for the bundle discount type "absolute"
     *
     * If the bundle discount defined as absolute,
     * the bundle prices are defined absolute per customer group.
     */
    const ABSOLUTE_DISCOUNT = 'abs';

    /**
     * Repository of the bundle model.
     *
     * Internal helper property to make the backend controller unit test possible without mocking the whole shopware project.
     *
     * @var \Shopware\CustomModels\Bundle\Repository
     */
    protected $bundleRepository = null;

    /**
     * Repository of the basket model.
     *
     * Internal helper property to make the backend controller unit test possible without mocking the whole shopware project.
     *
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $basketRepository = null;

    /**
     * Repository of the article model.
     *
     * Internal helper property to make the backend controller unit test possible without mocking the whole shopware project.
     *
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $articleRepository = null;

    /**
     * Repository of the customergroup model.
     * Internal helper property to make the backend controller unit test possible without mocking the whole shopware project.
     *
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $customerGroupRepository = null;

    /**
     * Repository of the media model.
     *
     * Internal helper property to make the backend controller unit test possible without mocking the whole shopware project.
     *
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $mediaRepository = null;

    /**
     * Media article album.
     *
     * Contains the Shopware\Models\Media\Album for the article images.
     *
     * @var \Shopware\Models\Media\Album
     */
    protected $articleAlbum = null;

    /**
     * Shopware entity manager / Shopware()->Models()
     *
     * Contains the entity manager of shopware.
     * Used for the model access.
     * If the class property contains null, the getter function loads
     * the entity manager over "Shopware()->Models()".
     *
     * @var \Shopware\Components\Model\ModelManager
     */
    protected $entityManager = null;

    /**
     * Old article core class sArticle.
     * Contains the sArticles core class.
     * @var sArticles
     */
    protected $articleModule = null;

    /**
     * Shopware database connection.
     *
     * Connection to the shopware database.
     * If this property is set to null, the getter function
     * of this property loads the default class over "Shopware()->Db()"
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    protected $database = null;

    /**
     * Shopware frontend session id.
     *
     * Session id of the user.
     * @var string Unique identifier for the basket item.
     */
    protected $sessionId = null;

    /**
     * Shopware basket component (Shopware_Components_BundleBasket).
     *
     * Contains the new Shopware_Components_Basket class.
     * Used to add a bundle to the basket.
     * @var null
     */
    protected $basketComponent = null;

    /**
     * Snippet manager of enlight.
     *
     * Contains the basket snippet namespace object which is used to get the translation
     * for the different basket notices and errors.
     * @var Enlight_Components_Snippet_Namespace
     */
    protected $snippetNamespace = null;

    /**
     * Current shop object of the frontend.
     *
     * Contains the current sub shop of shopware.
     * If the class property contains null, the getter function of
     * this property loads the current shop over "Shopware()->Shop()"
     * @var \Shopware\Models\Shop\Shop
     */
    protected $shop = null;

    /**
     * Current shopware frontend session.
     *
     * Current customer session.
     * Contains the customer data and other helper objects/data.
     * @var \Enlight_Components_Session_Namespace
     */
    protected $session = null;

    /**
     * Old shopware sConfigurator core class.
     *
     * Contains the sConfigurator core class.
     * If this property is set to null,
     * the getter function loads as default Shopware()->Modules()->Configurator()
     * @var sConfigurator
     */
    protected $configuratorModule = null;



    /**
     * Getter function for the configurator module property of this class.
     *
     * Used for the price calculation. If this property is set to null,
     * the function loads the default class "Shopware()->Modules()->Configurator()"
     *
     * @return \sConfigurator
     */
    public function getConfiguratorModule()
    {
        if ($this->configuratorModule === null) {
            $this->configuratorModule = Shopware()->Modules()->Configurator();
        }
        return $this->configuratorModule;
    }

    /**
     * Getter function for the configurator module property of this class.
     *
     * Used for the price calculation. If this property is set to null,
     * the getter function loads the default class "Shopware()->Modules()->Configurator()"
     *
     * @param \sConfigurator $configuratorModule
     */
    public function setConfiguratorModule($configuratorModule)
    {
        $this->configuratorModule = $configuratorModule;
    }

    /**
     * Getter function for the session property of this class.
     *
     * Contains the current customer session.
     * The session contains for example the unique session id
     * which is used as basket identification for the current customer.
     * If the session property contains null, this function loads the
     * standard shopware session over "Shopware()->Session()".
     * @return \Enlight_Components_Session_Namespace
     */
    public function getSession()
    {
        if ($this->session === null) {
            $this->session = Shopware()->Session();
        }
        return $this->session;
    }

    /**
     * Setter function for the session property of this class.
     *
     * Contains the current customer session.
     * The session contains for example the unique session id
     * which is used as basket identification for the current customer.
     *
     * @param  $session
     */
    public function setSession($session)
    {
        $this->session = $session;
    }

    /**
     * Getter function for the shop property of this class.
     *
     * Contains the current sub shop model of shopware.
     * If the class property contains null, the getter function loads
     * the active sub shop over "Shopware()->Shop()".
     * @return \Shopware\Models\Shop\Shop
     */
    public function getShop()
    {
        if ($this->shop === null) {
            $this->shop = Shopware()->Shop();
        }
        return $this->shop;
    }

    /**
     * Setter function for the shop property of this class.
     *
     * Contains the current sub shop model of shopware.
     * If the class property contains null, the getter function loads
     * the active sub shop over "Shopware()->Shop()".
     *
     * @param \Shopware\Models\Shop\Shop $shop
     */
    public function setShop($shop)
    {
        $this->shop = $shop;
    }

    /**
     * Getter function for the snippet namespace property.
     *
     * The snippet namespace is used for to get the translation
     * for the different basket notices and errors.
     * If the class property contains null, this function loads automatically the default snippet namespace
     * "Shopware()->Snippets()->getNamespace('frontend/checkout/cart_item')".
     * @return Enlight_Components_Snippet_Namespace
     */
    public function getSnippetNamespace()
    {
        if ($this->snippetNamespace === null) {
            $this->snippetNamespace = Shopware()->Snippets()->getNamespace('frontend/checkout/cart_item');
        }
        return $this->snippetNamespace;
    }

    /**
     * Setter function for the snippet namespace property.
     *
     * The snippet namespace is used for to get the translation
     * for the different basket notices and errors.
     * If the class property contains null, the getter function loads automatically the default snippet namespace
     * "Shopware()->Snippets()->getNamespace('frontend/checkout')".
     *
     * @param $snippetNamespace Enlight_Components_Snippet_Namespace
     */
    public function setSnippetNamespace($snippetNamespace)
    {
        $this->snippetNamespace = $snippetNamespace;
    }


    /**
     * Getter function for the basketComponent property of this class.
     *
     * If the class property contains null, the getter function loads
     * the basket component over "Shopware()->BundleBasket()"
     * @return Shopware_Components_BundleBasket
     */
    public function getBasketComponent()
    {
        if ($this->basketComponent === null) {
            $this->basketComponent = Shopware()->BundleBasket();
        }
        return $this->basketComponent;
    }

    /**
     * Setter function for the basketComponent property of this class.
     *
     * If the class property contains null, the getter function loads
     * the basket component over "Shopware()->BundleBasket()"
     * @param $basketComponent Shopware_Components_BundleBasket
     */
    public function setBasketComponent($basketComponent)
    {
        $this->basketComponent = $basketComponent;
    }

    /**
     * Getter function for the session id property of this class.
     *
     * Used for the customer identification.
     * If the class property contains null, the function loads the
     * session id over "Shopware()->SessionID()".
     * @return string
     */
    public function getSessionId()
    {
        if ($this->sessionId === null) {
            $this->sessionId = Shopware()->SessionID();
        }
        return $this->sessionId;
    }

    /**
     * Setter function for the session id property of this class.
     *
     * Used for customer identification.
     * @param $sessionId
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * Getter function of the database property of this class.
     *
     * If the database property is set to null, this getter
     * function loads the default connection over "Shopware()->Db()".
     *
     * @return \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    public function getDatabase()
    {
        if ($this->database === null) {
            $this->database = Shopware()->Db();
        }
        return $this->database;
    }

    /**
     * Setter function of the database property in this class.
     *
     * Used for all directly database action in this class.
     * If the database property is set to null, the getter
     * function loads the default connection over "Shopware()->Db()".
     *
     * @param \Enlight_Components_Db_Adapter_Pdo_Mysql $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * Getter function for the article module property of this class.
     *
     * Used for the price calculation. If this property is set to null,
     * the function loads the default class "Shopware()->Modules()->Articles()"
     *
     * @return \sArticles
     */
    public function getArticleModule()
    {
        if ($this->articleModule === null) {
            $this->articleModule = Shopware()->Modules()->Articles();
        }
        return $this->articleModule;
    }

    /**
     * Setter function for the article module property of this class.
     *
     * Used for the price calculation.
     *
     * @param \sArticles $articleModule
     */
    public function setArticleModule($articleModule)
    {
        $this->articleModule = $articleModule;
    }

    /**
     * Getter function for the entityManager property.
     *
     * Contains the entity manager of shopware.
     * Used for the model access.
     * If the class property contains null, the function loads
     * the entity manager over "Shopware()->Models()".
     *
     * @return \Shopware\Components\Model\ModelManager
     */
    public function getEntityManager()
    {
        if ($this->entityManager === null) {
            $this->entityManager = Shopware()->Models();
        }
        return $this->entityManager;
    }

    /**
     * Setter function for the entity manager property of this class.
     *
     * @param \Shopware\Components\Model\ModelManager $entityManager
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Getter function of the articleAlbum property.
     *
     * Internal helper function to get the article media album.
     * If this property is set to null the getter function selects
     * the default article album over the media model by using the
     * getAlbumWithSettingsQuery(-1) function.
     *
     * @return mixed
     */
    public function getArticleAlbum()
    {
        if ($this->articleAlbum === null) {
            /**@var $model \Shopware\Models\Media\Album*/
            $this->articleAlbum = $this->getMediaRepository()
                                       ->getAlbumWithSettingsQuery(-1)
                                       ->getOneOrNullResult();
        }

        return $this->articleAlbum;
    }

    /**
     * Getter function of the mediaRepository property.
     *
     * The getMediaRepository function is an internal helper function to make the backend controller unit test
     * possible without mocking the whole shopware project.
     * @return \Shopware\Models\Media\Repository
     */
    public function getMediaRepository()
    {
    	if ($this->mediaRepository === null) {
    		$this->mediaRepository = $this->getEntityManager()->getRepository(
                'Shopware\Models\Media\Media'
            );
    	}
    	return $this->mediaRepository;
    }

    /**
     * Getter function of the customerGroupRepository property.
     *
     * The getCustomerGroupRepository function is an internal helper function to make the backend controller unit test
     * possible without mocking the whole shopware project.
     * @return null|\Shopware\Components\Model\ModelRepository
     */
    public function getCustomerGroupRepository()
    {
    	if ($this->customerGroupRepository === null) {
    		$this->customerGroupRepository = $this->getEntityManager()->getRepository(
                'Shopware\Models\Customer\Group'
            );
    	}
    	return $this->customerGroupRepository;
    }

    /**
     * Getter function of the articleRepository property.
     *
     * The getArticleRepository function is an internal helper function to make the backend controller unit test
     * possible without mocking the whole shopware project.
     * @return \Shopware\Models\Article\Repository
     */
    public function getArticleRepository()
    {
    	if ($this->articleRepository === null) {
    		$this->articleRepository = $this->getEntityManager()->getRepository(
                'Shopware\Models\Article\Article'
            );
    	}
    	return $this->articleRepository;
    }

    /**
     * Getter function of the basketRepository property.
     * The getBasketRepository function is an internal helper function to make the backend controller unit test
     * possible without mocking the whole shopware project.
     * @return null|\Shopware\Components\Model\ModelRepository
     */
    public function getBasketRepository()
    {
    	if ($this->basketRepository === null) {
    		$this->basketRepository = $this->getEntityManager()->getRepository(
                'Shopware\Models\Order\Basket'
            );
    	}
    	return $this->basketRepository;
    }

    /**
     * Getter function of the bundleRepository property.
     *
     * The getBundleRepository function is an internal helper function to make the backend controller unit test
     * possible without mocking the whole shopware project.
     * @return \Shopware\CustomModels\Bundle\Repository
     */
    public function getBundleRepository()
    {
    	if ($this->bundleRepository === null) {
    		$this->bundleRepository = $this->getEntityManager()->getRepository(
                'Shopware\CustomModels\Bundle\Bundle'
            );
    	}
    	return $this->bundleRepository;
    }

    /**
     * Global interface to get the article bundles.
     *
     * Used to get all defined bundles for the passed article id. This function
     * is used from the Shopware_Plugins_Frontend_SwagBundle_Bootstrap class
     * to get all article bundles on the article detail page in the store front.
     *
     * @param int $articleId
     *
     * @return Array
     * (
     *   0 => Array
     *     (
     *       'id' => 1
     *       [name] => Sommer
     *       [number] => SW-Bundle-1
     *       [discountType] => abs
     *       [type] => 1
     *       [allConfigured] =>
     *       [limited] =>
     *       [quantity] => 0
     *       [price] => Array
     *         (
     *           [display] => 10,00
     *           [numeric] => 10
     *         )
     *       [totalPrice] => 98,88
     *       [discount] => Array
     *         (
     *           [percentage] => 10,11
     *           [gross] => 88.88
     *           [net] => 74.676638655462
     *           [display] => 88,88
     *         )
     *       [articles] => Array
     *         (
     *           [0] => Array
     *             (
     *               [bundleArticleId] => 0
     *               [articleId] => 150
     *               [name] => Bikini Ocean Blue
     *               [quantity] => 1
     *               [number] => SW10150.2
     *               [supplier] => Beachdreams Clothes
     *               [description] => Commodo cum mel volu...
     *               [isConfigurable] =>
     *               [isConfigured] =>
     *               [cover] => Array
     *                 (
     *                   [src] => Array
     *                     (
     *                       [original] => http://.../media/image/Bikini-blau-gelb.jpg
     *                       [0] => http://.../media/image/thumbnail/Bikini-blau-gelb_30x30.jpg
     *                       ...
     *                     )
     *                   [res] => Array
     *                     (
     *                       [original] => Array
     *                         (
     *                           [width] => 0
     *                           [height] => 0
     *                         )
     *
     *                       [description] =>
     *                     )
     *                   [position] => 1
     *                   [extension] => jpg
     *                   [main] => 1
     *                   [id] => 408
     *                   [parentId] =>
     *                   [attribute] => Array
     *                     (
     *                     )
     *                 )
     *               [price] => Array
     *                 (
     *                   [display] => 9,99
     *                   [numeric] => 9.99
     *                   [total] => 9.99
     *                 )
     *               [basePrice] => Array
     *                 (
     *                 )
     *               [configuration] => Array
     *                 (
     *                   [0] => Array
     *                     (
     *                       [id] => 6
     *                       [name] => Farbe
     *                       [description] =>
     *                       [position] => 8
     *                       [options] => Array
     *                         (
     *                           [0] => Array
     *                             (
     *                               [id] => 13
     *                               [groupId] => 6
     *                               [name] => weiss
     *                               [position] => 4
     *                             )
     *                           [1] => Array
     *                             (
     *                               ...
     *                         )
     *                       [selected] =>
     *                     )
     *                 )
     *               [descriptionLong] => Es occido Consido oro noster lauvabrum sed I...
     *             )
     *             ...
     *         )
     *     )
     * )
     */
    public function getBundlesForDetailPage($articleId)
    {
        //get the current customer group for the customer or the default customer group of the current shop.
        $customerGroup = $this->getCurrentCustomerGroup();

        $bundles = $this->getBundleRepository()
                        ->getArticleBundlesWithDetailQuery($articleId, $customerGroup->getId())
                        ->getResult();

        $data = array();

        /**@var $bundle \Shopware\CustomModels\Bundle\Bundle*/
        foreach($bundles as $bundle) {
            $calculatedBundle = $this->getCalculatedBundle($bundle);
            
            if (is_array($calculatedBundle) && $calculatedBundle['success'] === false) {
                continue;
            }
            
            $bundleData = $this->getArrayDataOfBundle(
                $calculatedBundle
            );

            if (isset($bundleData['success']) && $bundleData['success'] === false) {
                continue;
            }

            $data[] = $bundleData;
        }


        return $data;
    }

    /**
     * Global interface to get the full data for a single bundle.
     * The function returns the standard values for a bundle, with additional informations
     * like the discount value, article images, article configuration, etc.
     *
     * @param \Shopware\CustomModels\Bundle\Bundle $bundle
     *
     * @return array
     */
    public function getCalculatedBundle($bundle)
    {
        if (!$bundle instanceof \Shopware\CustomModels\Bundle\Bundle) {
            return array('success' => false, 'bundle' => 'n/A', 'notFound' => true);
        }

        //get the current customer group for the customer or the default customer group of the current shop.
        $customerGroup = $this->getCurrentCustomerGroup();

        //initial the bundle specify internal properties.
        $articles = array();
        $totalPrice = array('net' => 0, 'gross' => 0);
        $allConfigured = true;

        //to calculate the whole bundle price and check if the whole bundle is configured,
        //we have to fake an additional bundle article position with the bundle main article.
        //The bundle main article will not be returned as bundle position.
        $bundleArticles = array();
        $bundleArticles[] = $this->getBundleMainArticle($bundle);
        $bundleArticles = array_merge($bundleArticles, $bundle->getArticles()->getValues());

        $validation = $this->validateBundle($bundle);
        if (is_array($validation) && $validation['success'] === false) {
            return $validation;
        }

        //iterate all bundle articles to get the article data.
        /**@var $bundleArticle \Shopware\CustomModels\Bundle\Article*/
        foreach($bundleArticles as $bundleArticle) {
            if (!$bundleArticle instanceof \Shopware\CustomModels\Bundle\Article) {
                continue;
            }

            //the get article selection returns the article configuration groups and
            //options and checks if the article was already configured.
            //Additionally the function returns the article variant for the current configurator selection.
            $articleSelection = $this->getArticleSelection($bundleArticle);

            //get the configurator groups and options
            $configuration = $articleSelection['configuration'];

            /**@var $selectedDetail \Shopware\Models\Article\Detail*/
            //get the selected variant.
            $selectedDetail = $articleSelection['selectedDetail'];

            //check if the article is configured
            if (!$articleSelection['isConfigured']) {
                $allConfigured = false;
            }

            //special handling for the main article
            if ($bundleArticle->getId() === 0 && $bundle->getLimitedDetails()->count() > 0 && !$bundle->getLimitedDetails()->contains($selectedDetail)) {
                if ($bundle->getLimitedDetails()->count() > 0 && !$bundle->getLimitedDetails()->contains($selectedDetail)) {
                    return array('success' => false, 'bundle' => $bundle->getName(), 'notForSelectedVariant' => true);
                }
            }

            //get net and gross price for the selected variant and the current customer group.
            $prices = $this->getArticlePrices($selectedDetail, $customerGroup, $bundleArticle->getQuantity());

            //check if a price was founded.
            if ($prices === false) {
                return array('success' => false,'bundle' => $bundle->getName(), 'article' => $selectedDetail->getNumber(), 'noPrice' => true);
            }

            $basketQuantity = $this->getBasketComponent()->getSummarizedQuantityOfVariant($selectedDetail, null, array());

            //check if the article last stock flag is set to true an no more stck stock exist.
            if ($selectedDetail->getArticle()->getLastStock() && ($selectedDetail->getInStock() - $basketQuantity < 1 || ($bundleArticle->getQuantity() + $basketQuantity) > $selectedDetail->getInStock())) {
                return array('success' => false,'bundle' => $bundle->getName(), 'article' => $selectedDetail->getNumber(), 'noArticleStock' => true);
            }

            //check if the article and the selected variant is set to active.
            if (!$selectedDetail->getActive() || !$selectedDetail->getArticle()->getActive()) {
                return array('success' => false,'bundle' => $bundle->getName(), 'article' => $selectedDetail->getNumber(), 'notActive' => true);
            }

            //check if the customer group can buy the article
            if ($selectedDetail->getArticle()->getCustomerGroups()->contains($customerGroup)) {
                return array('success' => false,'bundle' => $bundle->getName(), 'article' => $selectedDetail->getNumber(), 'articleNotForCustomerGroup' => true);
            }

            //get the display price for the selected variant.
            $articlePrice = array(
                'display' => $this->getArticleModule()->sFormatPrice($prices['net']),
                'numeric' => $prices['net'],
                'total' => $prices['net'] * $bundleArticle->getQuantity()
            );

            if (!$this->displayNetPrices()) {
                $articlePrice = array(
                    'display' => $this->getArticleModule()->sFormatPrice($prices['gross']),
                    'numeric' => $prices['gross'],
                    'total' => $prices['gross'] * $bundleArticle->getQuantity()
                );
            }

            $articleData = array(
                'bundleArticleId' => $bundleArticle->getId(), //we have to set the original detail id as identification
                'articleId' => $selectedDetail->getArticle()->getId(),
                'name' => $selectedDetail->getArticle()->getName(),
                'quantity' => $bundleArticle->getQuantity(),
                'number' => $selectedDetail->getNumber(),
                'supplier' => $selectedDetail->getArticle()->getSupplier()->getName(),
                'description' => $selectedDetail->getArticle()->getDescription(),
                'description_long' => $selectedDetail->getArticle()->getDescriptionLong(),
                'isConfigurable' => $bundleArticle->getConfigurable() && $bundleArticle->getId() > 0,
                'isConfigured' => $articleSelection['isConfigured'],
                'cover' => $this->getArticleCover($selectedDetail),
                'price' => $articlePrice,
                'basePrice' => $this->getBasePriceOfDetail($selectedDetail),
                'configuration' => $configuration['groups']
            );

            $articleData = $this->getArticleModule()->sGetTranslation(
                $articleData, $selectedDetail->getArticle()->getId(), 'article'
            );
            $articleData['descriptionLong'] = $articleData['description_long'];
            unset($articleData['description_long']);

            $articles[] = $articleData;

            $totalPrice['net'] += ($prices['net'] * $bundleArticle->getQuantity());
            $totalPrice['gross'] += ($prices['gross'] * $bundleArticle->getQuantity());
        }

        $totalPrice['display'] = $this->getArticleModule()->sFormatPrice($totalPrice['net']);
        if (!$this->displayNetPrices()) {
            $totalPrice['display'] = $this->getArticleModule()->sFormatPrice($totalPrice['gross']);
        }

        //set the total prices into the bundle object.
        $bundle->setTotalPrice($totalPrice);

        //get the bundle prices
        $bundlePrices = $this->getBundlePrices($bundle);
        if ($bundlePrices === false) {
            return array('success' => false, 'bundle' => $bundle->getName(), 'noPrices' => true);
        }

        //get the bundle price for the selected customer group.
        $currentPrice = $this->getCurrentPrice($bundle, $customerGroup);
        if ($currentPrice === false) {
            return array('success' => false, 'bundle' => $bundle->getName(), 'noCustomerGroupPrice' => true);
        }

        //set the current price in the bundle object.
        $bundle->setCurrentPrice($currentPrice);

        //calculate the bundle discount for the passed bundle id.
        //Returns an array with discount data (gross/net prices) for the customer group of the current price property of the bundle
        $discount = $this->getBundleDiscount($bundle);

        //set the discount data into the bundle object.
        $bundle->setDiscount($discount);

        //set the calculated article data into the model to have later access on it.
        $bundle->setArticleData($articles);

        //set the allConfigured flag into the model.
        $bundle->setAllConfigured($allConfigured);

        return $bundle;
    }

    /**
     * Helper function to validate a single bundle.
     * Returns true if the bundle is valid for the current shop session.
     * @param $bundle \Shopware\CustomModels\Bundle\Bundle
     * @return array
     */
    private function validateBundle($bundle)
    {
        $customerGroup = $this->getCurrentCustomerGroup();

        //check if the bundle is allowed for the current customer group
        if ($bundle->getCustomerGroups()->count() > 0 && !$this->isCustomerGroupAllowed($bundle, $customerGroup)) {
            return array('success' => false, 'bundle' => $bundle->getName(), 'notForCustomerGroup' => true);
        }

        //check if the bundle is limited and the bundle has enough stock
        if ($bundle->getLimited() && $bundle->getQuantity() <= 0) {
            return array('success' => false, 'bundle' => $bundle->getName(), 'noStock' => true);
        }

        return true;
    }

    /**
     * Helper function to check if the passed customer group is allowed for the passed bundle.
     *
     * @param $bundle \Shopware\CustomModels\Bundle\Bundle
     * @param $customerGroup \Shopware\Models\Customer\Group
     *
     * @return bool
     */
    protected function isCustomerGroupAllowed($bundle, $customerGroup)
    {
        /**@var $customerGroupBundle \Shopware\Models\Customer\Group*/
        foreach($bundle->getCustomerGroups() as $customerGroupBundle) {
            if ($customerGroup->getKey() === $customerGroupBundle->getKey()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Helper function to validate all bundle discounts of the basket.
     *
     * If one of the bundle discounts removed, the function returns
     * an array with the removed bundle discounts and the error message.
     * In case that no discount removed, the function returns true.
     * @return array
     */
    public function validateBundleDiscounts()
    {
        $bundles = $this->getBasketBundles();
        $validations = array();
        foreach($bundles as $bundle) {
            if (!$bundle instanceof \Shopware\CustomModels\Bundle\Bundle) {
                continue;
            }
            $validation = $this->validateBundle($bundle);

            if (is_array($validation) && $validation['success'] === false) {
                $this->removeBasketBundle($bundle);
                $validations[] = $validation;
            }
        }
        if (empty($validations)) {
            return true;
        } else {
            return $validations;
        }
    }

    /**
     *
     */
    public function updateBundleBasketDiscount()
    {
        $this->getEntityManager()->clear();
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('basket', 'attribute'))
                ->from('Shopware\Models\Order\Basket', 'basket')
                ->innerJoin('basket.attribute', 'attribute')
                ->where('basket.mode = :mode')
                ->andWhere('basket.sessionId = :sessionId')
                ->andWhere('attribute.bundleId IS NOT NULL')
                ->setParameters(array('mode' => 10, 'sessionId' => $this->getSessionId()));

        $basketItems = $builder->getQuery()->getResult();
        
        /**@var $basketItem \Shopware\Models\Order\Basket*/
        foreach($basketItems as $basketItem) {
            $bundle = $this->getBundleRepository()->findOneBy(array(
                'number' => $basketItem->getOrderNumber()
            ));
    
            if (!$bundle instanceof \Shopware\CustomModels\Bundle\Bundle) {
                continue;
            }

            /**@var $basketRow \Shopware\Models\Order\Basket*/
            $basketRow = $this->getEntityManager()->find('Shopware\Models\Order\Basket', $basketItem->getId());

            $netPrice = $basketItem->getNetPrice();

            if ($this->useNetPriceInBasket()) {
                $basketRow->setPrice($netPrice);
            } else {
                $grossPrice = $netPrice / 100 * (100 + $bundle->getArticle()->getTax()->getTax());
                $basketRow->setPrice($grossPrice);
            }
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Helper function to remove the passed bundle flags from the basket.
     * @param $bundle \Shopware\CustomModels\Bundle\Bundle
     */
    public function removeBasketBundle($bundle)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('basket'))
                ->from('Shopware\Models\Order\Basket', 'basket')
                ->where('basket.orderNumber = :number')
                ->andWhere('basket.sessionId = :sessionId')
                ->setParameters(array('number' => $bundle->getNumber(), 'sessionId' => $this->getSessionId()));

        $baskets = $builder->getQuery()->getResult();

        foreach($baskets as $basket) {
            $this->getEntityManager()->remove($basket);
        }

        $this->getEntityManager()->flush();
        $this->removeBundleFlagOfBasket($bundle);
    }

    /**
     * Helper function to remove the bundle ids from all basket positions for the
     * current session id. This function called if the customer removes
     * a bundle positions. All bundle positions will be converted to normal article
     * positions.
     * @param $bundle
     */
    public function removeBundleFlagOfBasket($bundle)
    {
        $this->getEntityManager()->clear();

        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('attribute'))
                ->from('Shopware\Models\Attribute\OrderBasket', 'attribute')
                ->innerJoin('attribute.orderBasket', 'basket')
                ->where('basket.sessionId = :sessionId')
                ->andWhere('attribute.bundleId = :bundleId')
                ->setParameters(array('bundleId' => $bundle->getId(), 'sessionId' => $this->getSessionId()));

        $attributes = $builder->getQuery()->getResult();

        /**@var $attribute \Shopware\Models\Attribute\OrderBasket*/
        foreach($attributes as $attribute) {
            $attribute->setBundleId(null);
        }
        $this->getEntityManager()->flush();
    }

    /**
     * Helper function to get all basket bundles.
     * @return array
     */
    public function getBasketBundles()
    {
        $sql= "
            SELECT ordernumber
            FROM s_order_basket
            WHERE s_order_basket.modus = 10
        ";
        $discounts = $this->getDatabase()->fetchCol($sql);
        $bundles = array();

        foreach($discounts as $discount) {
            if (!empty($discount)) {
                $bundles[] = $this->getBundleRepository()->findOneBy(array(
                    'number' => $discount
                ));
            }
        }

        return $bundles;
    }

    /**
     * Global interface to get the base price calculation of a single article variant.
     *
     * @param $detail \Shopware\Models\Article\Detail
     * @return array
     */
    public function getBasePriceOfDetail($detail)
    {
        if ($detail->getUnit() instanceof \Shopware\Models\Article\Unit &&
            $detail->getPurchaseUnit() > 0 && $detail->getReferenceUnit()) {

            $price = $detail->getPrices()->first();

            $basePrice = $price / $detail->getPurchaseUnit() * $detail->getReferenceUnit();

            return array(
                'unit' => $this->getArticleModule()->sGetUnit($detail->getUnit()->getId()),
                'minPurchase' => $detail->getMinPurchase(),
                'maxPurchase' => $detail->getMaxPurchase(),
                'purchaseUnit' => (float) $detail->getPurchaseUnit(),
                'refrenceUnit' => (float) $detail->getReferenceUnit(),
                'referencePrice' => array(
                    'numeric' => $basePrice,
                    'display' => $this->getArticleModule()->sFormatPrice($basePrice)
                )
            );
        } else {
            return array();
        }
    }

    /**
     * Helper function to check if the current customer should see net prices for articles.
     *
     * @return bool
     */
    public function displayNetPrices()
    {
        return $this->isCustomerGroupNet();
    }

    /**
     * Helper function to check if the shopware basket should use gross or net prices
     * for the current logged in customer.
     * @return bool
     */
    public function useNetPriceInBasket()
    {
        if (!$this->isCustomerGroupNet() && !$this->isShippingCountryNet()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Helper function to check if the selected country would be delivered with net prices.
     *
     * @return bool|int
     */
    public function isShippingCountryNet()
    {
        if (empty($this->getSession()->sCountry)) {
            return false;
        }

        /**@var $country \Shopware\Models\Country\Country */
        $country = $this->getEntityManager()->find('Shopware\Models\Country\Country', $this->getSession()->sCountry);

        return (bool) $country->getTaxFree();
    }

    /**
     * Helper function to check if the current customer would see net or gross prices.
     *
     * @return bool
     */
    public function isCustomerGroupNet()
    {
        return !$this->getCurrentCustomerGroup()->getTax();
    }


    /**
     * Global interface to convert the passed bundle model into
     * an array.
     * This helper function is used for the article detail page in the store front.
     *
     * @param $bundle \Shopware\CustomModels\Bundle\Bundle
     * @return array
     */
    public function getArrayDataOfBundle($bundle)
    {
        $totalPrice = $bundle->getTotalPrice();

        $bundlePrice = $bundle->getCurrentPrice()->getNetPrice();
        if ($bundle->getCurrentPrice()->getCustomerGroup()->getTax()) {
            $bundlePrice = $bundle->getCurrentPrice()->getGrossPrice();
        }

        return array(
            'id' => $bundle->getId(),
            'name' => $bundle->getName(),
            'number' => $bundle->getNumber(),
            'discountType' => $bundle->getDiscountType(),
            'type' => $bundle->getType(),
            'allConfigured' => $bundle->getAllConfigured(),
            'limited' => $bundle->getLimited(),
            'quantity' => $bundle->getQuantity(),
            'price' => array(
                'display' => $bundle->getCurrentPrice()->getDisplayPrice(),
                'numeric' => $bundlePrice
            ),
            'totalPrice' => $totalPrice['display'],
            'discount' => $bundle->getDiscount(),
            'articles' => $bundle->getArticleData(),
        );
    }

    /**
     * Global interface to add a bundle article to the shopware basket.
     * Expects a valid bundle id which defined in the s_articles_bundles table.
     * If the bundle contains some configurator articles, the article configuration
     * is saved in the shopware session. The selection parameter is used for
     * selectable bundles. This bundles can be configured by the customer.
     * The array contains only the selected bundle articles.
     *
     * @param int                                                $bundleId
     * @param array|\Doctrine\Common\Collections\ArrayCollection $selection
     *
     * @return array
     */
    public function addBundleToBasket($bundleId, $selection = array())
    {
        if (!$selection instanceof ArrayCollection && is_array($selection)) {
            $selection = new ArrayCollection($selection);
        }

        $bundleId = (int) $bundleId;

        /**@var $bundle \Shopware\CustomModels\Bundle\Bundle*/
        $bundle = $this->getEntityManager()->find('Shopware\CustomModels\Bundle\Bundle', $bundleId);

        if (!$bundle instanceof \Shopware\CustomModels\Bundle\Bundle) {
            return array('success' => false, 'alreadyInBasket' => true);
        }

        if ($this->isBundleInBasket($bundle)) {
            return array('success' => false, 'alreadyInBasket' => true);
        }

        if ($this->isSomeBundleInBasket()) {
            return array('success' => false, 'alreadyBundleInBasket' => true);
        }

        if ($bundle->getType() === self::SELECTABLE_BUNDLE && empty($selection)) {
            return array('success' => false, 'noSelection' => true);
        }

        //get the calculated bundle data.
        $bundle = $this->getCalculatedBundle($bundle, \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);

        if (!$bundle instanceof \Shopware\CustomModels\Bundle\Bundle) {
            return $bundle;
        }

        //get the article of the bundle as faked bundle position.
        $mainArticle = $this->getBundleMainArticle($bundle);
        if (!$mainArticle instanceof \Shopware\CustomModels\Bundle\Article) {
            return array('success' => false, 'noMainArticle' => true);
        }

        //get the configuration for the main article
        $configuration = $this->getArticleSelection($mainArticle);

        /**@var $selected \Shopware\Models\Article\Detail*/
        $selected = $configuration['selectedDetail'];

        //add the main article to the basket
        $this->getBasketComponent()->addArticle(
            $selected->getNumber(),
            1,
            array('bundleId' => $bundle->getId(), 'bundleArticleId' => 0)
        );

        $result = array();
        //iterate all bundle positions and add them to the shopware basket
        /**@var $bundleArticle \Shopware\CustomModels\Bundle\Article*/
        foreach($bundle->getArticles() as $bundleArticle) {
            //get the configuration for the bundle position
            $configuration = $this->getArticleSelection($bundleArticle);

            /**@var $selected \Shopware\Models\Article\Detail*/
            $selected = $configuration['selectedDetail'];

            //a selectable bundle can be configured by the customer.
            //we have to check if the current bundle article was selected
            //the selected articles are passed in the "$selection" parameter.
            if ($bundle->getType() === Shopware_Components_Bundle::SELECTABLE_BUNDLE &&
                !$selection->contains($bundleArticle)) {
                continue;
            }

            //check if a variant was selected
            if ($selected instanceof \Shopware\Models\Article\Detail) {
                $articleResult = $this->getBasketComponent()->addArticle(
                    $selected->getNumber(),
                    $bundleArticle->getQuantity(),
                    array('bundleId' => $bundle->getId(), 'bundleArticleId' => $bundleArticle->getId())
                );

                if ($articleResult['success'] === false) {
                    $this->removeBasketBundle($bundle);
                    return $articleResult;
                }
                $result[] = $articleResult;
            }
        }

        $discount = array();

        //we have to check the bundle type.
        if ($bundle->getType() === self::NORMAL_BUNDLE) {
            //the discount of normale bundle are already calculated by
            //the getCalculatedBundle function.
            $discount = $bundle->getDiscount();
        } else if ($bundle->getType() === self::SELECTABLE_BUNDLE && $bundle->getDiscountType() === self::PERCENTAGE_DISCOUNT) {
            $discount = $this->getDiscountForSelectableBundles($bundle, $selection);
        } else if ($bundle->getType() === self::SELECTABLE_BUNDLE && $bundle->getDiscountType() === self::ABSOLUTE_DISCOUNT) {
            $discount = $bundle->getDiscount();
        }

        /** @var $namespace Enlight_Components_Snippet_Namespace */
        $namespace = $this->getSnippetNamespace();

        $data = array(
            'sessionID' => $this->getSessionId(),
            'articlename' => $namespace->get('CartItemInfoBundle', 'Bundle discount'),
            'articleID' => 0,
            'ordernumber' => $bundle->getNumber(),
            'shippingfree' => 0,
            'quantity' => 1,
            'price' => $discount['gross'] * -1,
            'netprice' => $discount['net'] * -1,
            'datum' => 'NOW()',
            'modus' => 10,
            'tax_rate' => $bundle->getArticle()->getTax()->getTax(),
            'currencyFactor' => $this->getShop()->getCurrency()->getFactor()
        );
        $this->getDatabase()->insert('s_order_basket', $data);

        $basketId = $this->getDatabase()->lastInsertId('s_order_basket');

        $data = array(
            'basketID' => $basketId,
            'bundle_id' => $bundleId
        );
        $this->getDatabase()->insert('s_order_basket_attributes', $data);

        return true;
    }

    /**
     * Helper function to calculate the discount value for a selectable bundle
     * @param $bundle \Shopware\CustomModels\Bundle\Bundle has to be a full calculated bundle ($this->getCalculatedBundle)
     * @param $selection  \Doctrine\Common\Collections\ArrayCollection
     * @return array
     */
    public function getDiscountForSelectableBundles($bundle, $selection)
    {
        $customerGroup = $this->getCurrentCustomerGroup();

        //the discount for selectable bundles has to be calculted
        //by the "getTotalArticlePriceForSelectableBundle" function to
        //get the total price for the selected articles.
        $bundle->setTotalPrice(
            $this->getTotalArticlePriceForSelectableBundle(
                $bundle,
                $customerGroup,
                $selection
            )
        );

        //after the new total price was set, we can calculate the new bundle prices.
        $prices = $this->getBundlePrices($bundle);

        //we have to clear the prices before to prevent double price definitions.
        $bundle->getPrices()->clear();
        $bundle->setPrices($prices);

        //now we have to set the current price for the customer group
        $bundle->setCurrentPrice(
            $this->getCurrentPrice(
                $bundle,
                $customerGroup
            )
        );

        return $this->getBundleDiscount($bundle);
    }

    /**
     * Global interface to calculate the total article price of the bundle positions.
     * @param $bundle \Shopware\CustomModels\Bundle\Bundle
     * @param $customerGroup \Shopware\Models\Customer\Group
     * @return int
     */
    public function getTotalArticlePriceForCustomerGroup($bundle, $customerGroup)
    {
        if (!$bundle instanceof \Shopware\CustomModels\Bundle\Bundle) {
            return false;
        }

        //to calculate the whole bundle price and check if the whole bundle is configured,
        //we have to fake an additional bundle article position with the bundle main article.
        //The bundle main article will not be returned as bundle position.
        $bundleArticles = array();
        $bundleArticles[] = $this->getBundleMainArticle($bundle);
        $bundleArticles = array_merge($bundleArticles, $bundle->getArticles()->getValues());

        $total = 0;

        //iterate all bundle articles to get the article data.
        /**@var $bundleArticle \Shopware\CustomModels\Bundle\Article*/
        foreach($bundleArticles as $bundleArticle) {
            if (!$bundleArticle instanceof \Shopware\CustomModels\Bundle\Article) {
                continue;
            }

            //get net and gross price for the selected variant and the current customer group.
            $prices = $this->getArticlePrices($bundleArticle->getArticleDetail(), $customerGroup, 1, 'EK');

            if ($customerGroup->getTax()) {
                $total += $prices['gross'];
            } else {
                $total += $prices['net'];
            }
        }

        return $total;
    }

    /**
     * Global interface to calculate the total article price of the bundle positions
     * for a selectable bundle.
     *
     * @param $bundle        \Shopware\CustomModels\Bundle\Bundle
     * @param $customerGroup \Shopware\Models\Customer\Group
     * @param $selection     \Doctrine\Common\Collections\ArrayCollection
     *
     * @return int
     */
    public function getTotalArticlePriceForSelectableBundle($bundle, $customerGroup, $selection)
    {
        if (!$bundle instanceof \Shopware\CustomModels\Bundle\Bundle) {
            return false;
        }

        //to calculate the whole bundle price and check if the whole bundle is configured,
        //we have to fake an additional bundle article position with the bundle main article.
        //The bundle main article will not be returned as bundle position.
        $bundleArticles = array();
        $bundleArticles[] = $this->getBundleMainArticle($bundle);
        $bundleArticles = array_merge($bundleArticles, $bundle->getArticles()->getValues());

        $total = array(
            'net' => 0,
            'gross' => 0
        );

        //iterate all bundle articles to get the article data.
        /**@var $bundleArticle \Shopware\CustomModels\Bundle\Article*/
        foreach($bundleArticles as $bundleArticle) {
            if (!$bundleArticle instanceof \Shopware\CustomModels\Bundle\Article) {
                continue;
            }

            //checks if the current bundle article was selected by the customer
            if ($bundleArticle->getId() > 0 && $bundle->getType() == self::SELECTABLE_BUNDLE &&
                !$selection->contains($bundleArticle)) {
                continue;
            }

            //get net and gross price for the selected variant and the current customer group.
            $prices = $this->getArticlePrices($bundleArticle->getArticleDetail(), $customerGroup, 1, 'EK');

            $total['gross'] += $prices['gross'];
            $total['net'] += $prices['net'];
        }

        return $total;
    }

    /**
     * Helper function to convert the post data from the main article into
     * the bundle configuration structure.
     *
     * @param int $articleId
     * @param array $selectedConfiguration
     *
     * @return void
     */
    public function convertMainArticleConfiguration($articleId, $selectedConfiguration)
    {
        $bundles = $this->getBundleRepository()->getArticleBundlesQuery($articleId)->getResult();
        /**@var $bundle \Shopware\CustomModels\Bundle\Bundle*/
        foreach($bundles as $bundle) {
            $this->getSession()->bundleConfiguration[$bundle->getId()][0] = $selectedConfiguration;
        }
    }

    /**
     * Global interface to check if the passed variant number is already as bundle article position
     * in the basket.
     */
    public function isVariantAsBundleInBasket()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('basket'))
                ->from('Shopware\Models\Order\Basket', 'basket')
                ->innerJoin('basket.attribute', 'attribute')
                ->where('attribute.bundleId > :bundleId')
                ->andWhere('basket.sessionId = :sessionId')
                ->setFirstResult(0)
                ->setMaxResults(1)
                ->setParameters(array('sessionId' => $this->getSessionId(), 'bundleId' => 0));

        $basket = $builder->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);

        if ($basket instanceof \Shopware\Models\Order\Basket) {
            return $basket->getId();
        } else {
            return null;
        }
    }

    /**
     * Global interface to check if the passed variant number is already as normal article position
     * in the basket.
     *
     * @param $number
     *
     * @return int|null
     */
    public function isVariantAsNormalInBasket($number)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('basket'))
                ->from('Shopware\Models\Order\Basket', 'basket')
                ->innerJoin('basket.attribute', 'attribute')
                ->where('attribute.bundleId IS NULL')
                ->andWhere('basket.sessionId = :sessionId')
                ->andWhere('basket.orderNumber = :orderNumber')
                ->setFirstResult(0)
                ->setMaxResults(1)
                ->setParameters(array('sessionId' => $this->getSessionId(), 'orderNumber' => $number));

        $basket = $builder->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);

        if ($basket instanceof \Shopware\Models\Order\Basket) {
            return $basket->getId();
        } else {
            return null;
        }
    }

    /**
     * Global interface to get the article of the bundle as own bundle article position.
     * @param $bundle \Shopware\CustomModels\Bundle\Bundle
     * @return \Shopware\CustomModels\Bundle\Article
     */
    public function getBundleMainArticle($bundle)
    {
        $mainArticle = new \Shopware\CustomModels\Bundle\Article();
        $mainArticle->setArticleDetail($bundle->getArticle()->getMainDetail());
        $mainArticle->setConfigurable(
            ($bundle->getArticle()->getConfiguratorSet() instanceof \Shopware\Models\Article\Configurator\Set)
        );
        $mainArticle->setBundle($bundle);
        $mainArticle->setId(0);
        return $mainArticle;
    }

    /**
     * Internal helper function to get the cover of an article detail.
     * @param $articleDetail \Shopware\Models\Article\Detail
     * @return array
     */
    public function getArticleCover($articleDetail)
    {
        /**@var $selectedDetail \Shopware\Models\Article\Detail*/
        return $this->getArticleModule()->getArticleCover(
            $articleDetail->getArticle()->getId(),
            $articleDetail->getNumber(),
            $this->getArticleAlbum()
        );
    }

    /**
     * Global interface to get the net and gross price for the passed bundle.
     *
     * @param $bundle \Shopware\CustomModels\Bundle\Bundle
     *
     * @return array
     */
    public function getBundlePrices($bundle)
    {
        //get all defined bundle prices as objects
        $prices = $this->getRawBundlePrices($bundle->getId(), \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);

        //if no prices defined, return an error.
        if (empty($prices)) {
            return false;
        }

        /**@var $bundlePrice \Shopware\CustomModels\Bundle\Price*/
        foreach($prices as $bundlePrice) {

            //check if the bundle discount type is defined as percentage
            if ($bundle->getDiscountType() === self::PERCENTAGE_DISCOUNT) {
                //if this is the case we have to calculate the bundle prices based on the total price of the article positions
                $total = $bundle->getTotalPrice();

                //first we set the percentage value from the price property to the expected percentage property
                $bundlePrice->setPercentage($bundlePrice->getPrice());

                //now we calculate the net price
                $bundlePrice->setNetPrice(
                    $total['net'] * (100 - $bundlePrice->getPercentage()) / 100
                );

                $bundlePrice->setGrossPrice(
                    $total['gross'] * (100 - $bundlePrice->getPercentage()) / 100
                );
            } else {
                //if the discount type is defined as absolute we have to calculate the percentage value based on the total price of the article position
                $total = $bundle->getTotalPrice();

                //set the defined backend price as net price
                $bundlePrice->setNetPrice(
                    $bundlePrice->getPrice()
                );

                $bundlePrice->setGrossPrice(
                    $bundlePrice->getPrice() / 100 * ($bundle->getArticle()->getTax()->getTax() + 100)
                );

                //calculate the percentage value.
                $bundlePrice->setPercentage(
                    $bundlePrice->getNetPrice() /  ($total['net'] / 100)
                );
            }

            //check if the customer group prices should be displayed as gross or net prices
            if ($bundlePrice->getCustomerGroup()->getTax()) {
                $bundlePrice->setDisplayPrice(
                    $this->getArticleModule()->sFormatPrice($bundlePrice->getGrossPrice())
                );
            } else {
                $bundlePrice->setDisplayPrice(
                    $this->getArticleModule()->sFormatPrice($bundlePrice->getNetPrice())
                );
            }
        }
        $bundle->getUpdatedPrices()->clear();
        foreach($prices as $price) {
            $bundle->getUpdatedPrices()->add($price);
        }
        $this->getEntityManager()->clear();

        return $bundle->getUpdatedPrices();
    }

    /**
     * Internal helper function to get the price for the passed customer group.
     * @param $bundle \Shopware\CustomModels\Bundle\Bundle
     * @param $customerGroup \Shopware\Models\Customer\Group
     *
     * @return bool
     */
    public function getCurrentPrice($bundle, $customerGroup)
    {
        $price = $bundle->getPriceForCustomerGroup($customerGroup->getKey());

        if (!$price instanceof \Shopware\CustomModels\Bundle\Price) {
            return false;
        }

        return $price;
    }

    /**
     * Internal helper function which returns the article configurator groups and options
     * and checks if the passed bundle article was already configured.
     *
     * @param $article \Shopware\CustomModels\Bundle\Article
     * @return array
     */
    public function getArticleSelection($article)
    {
        $articleIsConfigured = true;
        $configuration = array();
        $configurable = false;

        //an bundle article is only configurable if the assinged article has an defined configurator set and the passed bundle article has an valid id.
        if ($article->getConfigurable() &&
            $article->getArticleDetail()->getArticle()->getConfiguratorSet() instanceof \Shopware\Models\Article\Configurator\Set) {

            $configurable = true;
        }

        $article->setConfigurable($configurable);

        if ($configurable) {
            //get the configurator configuration groups and options for the
            $configuration = $this->getArticleConfiguration($article);
            if (!$configuration['configured']) {
                $articleIsConfigured = false;
            }

            $selectedDetail = $this->getSelectedVariant($article);
            if (!$selectedDetail['success']) {
                $articleIsConfigured = false;
            }

            $selectedDetail = $selectedDetail['detail'];
        } else {
            $selectedDetail = $article->getArticleDetail();
        }

        return array(
            'selectedDetail' => $selectedDetail,
            'isConfigured' => $articleIsConfigured,
            'configuration' => $configuration
        );
    }

    /**
     * Internal helper function to get the current customer group for the customer
     * or the default customer group of the current shop.
     * @return \Shopware\Models\Customer\Group
     */
    public function getCurrentCustomerGroup()
    {
        $customerGroupData = $this->getSession()->sUserGroupData;

        $customerGroup = null;

        /**@var $customerGroup \Shopware\Models\Customer\Group*/
        //check if the customer logged in and get the customer group model for the logged in customer
        if (!empty($customerGroupData['groupkey'])) {
            $customerGroup = $this->getCustomerGroupRepository()->findOneBy(array(
                'key' => $customerGroupData['groupkey']
            )); 
        }

        //if no customer group given, get the default customer group.
        if (!$customerGroup instanceof Shopware\Models\Customer\Group) {
            $customerGroup = $this->getShop()->getCustomerGroup();
        }

        return $customerGroup;
    }

    /**
     * Global interface to get the configurator configuration for the passed bundle article.
     * @param $bundleArticle \Shopware\CustomModels\Bundle\Article
     *
     * @return array
     */
    public function getArticleConfiguration($bundleArticle)
    {
        //get identification objects and ids.
        $bundleId = $bundleArticle->getBundle()->getId();
        $bundleArticleId = $bundleArticle->getId();
        $article = $bundleArticle->getArticleDetail()->getArticle();

        if (!$article->getConfiguratorSet() instanceof \Shopware\Models\Article\Configurator\Set) {
            return array('groups' => array(), 'configured' => true);
        }

        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('configuratorSet', 'groups', 'options'))
                ->from('Shopware\Models\Article\Configurator\Set', 'configuratorSet')
                ->innerJoin('configuratorSet.groups', 'groups', null, null, 'groups.id')
                ->innerJoin('configuratorSet.options', 'options')
                ->where('configuratorSet.id = :setId')
                ->orderBy('groups.position', 'ASC')
                ->addOrderBy('options.position', 'ASC')
                ->setParameters(array('setId' => $article->getConfiguratorSet()->getId()));

        $configuratorSet = $builder->getQuery()->getArrayResult();
        $configuratorSet = $configuratorSet[0];
        $articleConfiguration = $this->getSession()->bundleConfiguration[$bundleId][$bundleArticleId];

        foreach($configuratorSet['options'] as $option) {
            if (array_key_exists($option['groupId'], $configuratorSet['groups'])) {
                $option = $this->getArticleModule()->sGetTranslation($option, $option['id'], 'configuratoroption');
                $configuratorSet['groups'][$option['groupId']]['options'][] = $option;
            }
        }

        foreach($configuratorSet['groups'] as &$group) {
            $group = $this->getArticleModule()->sGetTranslation($group, $group['id'], 'configuratorgroup');

            /**
             * The already selected bundle article configuration is saved in the shopware session in the following format:
             * Session => bundleConfiguration [$bundleId]  [$bundleArticleId] [$configuratorGroupId]
             */
            $group['selected'] = $articleConfiguration[$group['id']];
        }
        $filtered = array_filter($articleConfiguration);

        return array(
            'configured' => (count($configuratorSet['groups']) === count($filtered)),
            'groups' => array_values($configuratorSet['groups'])
        );
    }

    /**
     * Returns the variant selection for the passed bundle article position.
     *
     * The selected configuration are saved in the shopware frontend session.
     * The different configuration are identified over the bundle id and bundle article id.
     *
     * @param $bundleArticle \Shopware\CustomModels\Bundle\Article
     * @return array With success and detail array key.
     */
    public function getSelectedVariant($bundleArticle)
    {
        $bundleId = $bundleArticle->getBundle()->getId();
        $bundleArticleId = $bundleArticle->getId();

        $articleConfiguration = $this->getSession()->bundleConfiguration[$bundleId][$bundleArticleId];

        if (empty($articleConfiguration)) {
            return array(
                'success' => false,
                'detail' => $bundleArticle->getArticleDetail()
            );
        }

        $query = $this->getArticleRepository()->getDetailsForOptionIdsQuery(
            $bundleArticle->getArticleDetail()->getArticle()->getId(),
            $articleConfiguration
        );

        $selected = $query->getResult();

        if (empty($selected)) {
            return array(
                'success' => false,
                'detail' => $bundleArticle->getArticleDetail()
            );
        } else {
            return array(
                'success' => true,
                'detail' => $selected[0]
            );
        }
    }

    /**
     * Internal helper function to get the gross and net price for a single article variant and customer group.
     *
     * @param      $articleDetail \Shopware\Models\Article\Detail
     * @param      $customerGroup \Shopware\Models\Customer\Group
     * @param      $quantity int
     *
     * @param null $fallback
     *
     * @return array
     */
    public function getArticlePrices($articleDetail, $customerGroup, $quantity, $fallback = null)
    {
        if ($fallback === null) {
            $fallback = $this->getShop()->getCustomerGroup()->getKey();
        }

        $prices = $this->getArticleDetailPriceForCustomerGroup(
            $articleDetail->getId(),
            $customerGroup->getKey(),
            $fallback,
            \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT
        );

        $currentPrice = null;

        /**@var $price \Shopware\Models\Article\Price*/
        foreach($prices as $price) {
            if (!is_numeric($price->getTo())) {
                $currentPrice = $price;
                break;
            } else if ($quantity >= $price->getFrom() && $quantity <= $price->getTo()) {
                $currentPrice = $price;
                break;
            }
        }
        //if no price founded,
        if (!$currentPrice instanceof \Shopware\Models\Article\Price) {
            return false;
        }

        return array(
            'net' => $this->calculateNetPrice($currentPrice->getPrice(), $articleDetail),
            'gross' => $this->calculateGrossPrice($currentPrice->getPrice(), $articleDetail)
        );
    }


    /**
     * Global interface to get the prices for the passed article id and customer group key.
     *
     * @param int    $articleDetailId  Contains the unique article detail identifier
     * @param string $customerGroupKey Contains the group key for the customer group
     * @param string $fallbackKey      Contains an fallback group key for the customer group
     * @param int    $hydrationMode    The hydration mode parameter control the result data type.
     *
     * @return array
     */
    public function getArticleDetailPriceForCustomerGroup($articleDetailId, $customerGroupKey, $fallbackKey = 'EK', $hydrationMode = \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY)
    {
        //no group key passed?
        if (empty($customerGroupKey)) {
            $customerGroupKey = $fallbackKey;
        }

        $builder = $this->getPriceQueryBuilder();
        $builder->setParameters(array(
             'articleDetailId' => $articleDetailId,
             'customerGroupKey' => $customerGroupKey
        ));

        $prices = $builder->getQuery()->getResult($hydrationMode);
        if (empty($prices) && $customerGroupKey !== $fallbackKey) {
            return $this->getArticleDetailPriceForCustomerGroup($articleDetailId, $fallbackKey, $fallbackKey, $hydrationMode);
        } else {
            return $prices;
        }
    }

    /**
     * Internal helper function which returns an query builder object which creates an query to select all variant prices
     * for a specify customer group.
     * @return Doctrine\ORM\QueryBuilder
     */
    public function getPriceQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        return $builder->select(array('prices'))
                ->from('Shopware\Models\Article\Price', 'prices')
                ->where('prices.articleDetailsId = :articleDetailId')
                ->andWhere('prices.customerGroupKey = :customerGroupKey')
                ->orderBy('prices.from', 'ASC');
    }


    /**
     * Internal helper function which calculates the net price for the passed price value and the passed article.
     * @param $price float
     * @param $article \Shopware\Models\Article\Detail
     *
     * @return double
     */
    public function calculateNetPrice($price, $article)
    {
        //calculates the net price for the selected article
        return $this->getArticleModule()->sCalculatingPriceNum(
            $price,
            $article->getArticle()->getTax()->getTax(),
            false,
            true,
            $article->getArticle()->getTax()->getId(),
            false,
            $this->getEntityManager()->toArray($article)
        );
    }

    /**
     * Internal helper function which calculates the gross price for the passed price value and the passed article.
     * @param $price float
     * @param $article \Shopware\Models\Article\Detail
     *
     * @return double
     */
    public function calculateGrossPrice($price, $article)
    {
        //calculates the gross price for the selected article
        return $this->getArticleModule()->sCalculatingPriceNum(
            $price,
            $article->getArticle()->getTax()->getTax(),
            false,
            false,
            $article->getArticle()->getTax()->getId(),
            false,
            $this->getEntityManager()->toArray($article)
        );
    }

    /**
     * Global interface to calculate the bundle discount for the passed bundle id.
     * Returns an array with discount data for the different customer groups and
     * gross/net prices.
     *
     * @param $bundle \Shopware\CustomModels\Bundle\Bundle
     *
     * @return array
     */
    public function getBundleDiscount($bundle)
    {
        $total = $bundle->getTotalPrice();
        $discount = array(
            'percentage' => $this->getArticleModule()->sFormatPrice($bundle->getCurrentPrice()->getPercentage()),
            'gross' => $total['gross'] - $bundle->getCurrentPrice()->getGrossPrice(),
            'net' => $total['net'] - $bundle->getCurrentPrice()->getNetPrice()
        );

        $discount['display'] = $this->getArticleModule()->sFormatPrice($discount['net']);
        $discount['usage'] = $discount['net'];

        if ($bundle->getCurrentPrice()->getCustomerGroup()->getTax()) {
            $discount['display'] = $this->getArticleModule()->sFormatPrice($discount['gross']);
            $discount['usage'] = $discount['gross'];
        }

        return $discount;
    }

    /**
     * Global interface which checks if the passed bundle (id) is already added to the shopware basket.
     *
     * @param $bundle \Shopware\CustomModels\Bundle\Bundle
     *
     * @return boolean
     */
    public function isBundleInBasket($bundle)
    {
        $sql= "
            SELECT *
            FROM s_order_basket
            WHERE sessionID = ?
            AND ordernumber = ?
        ";
        $result = $this->getDatabase()->fetchAll($sql, array($this->getSessionId(), $bundle->getNumber()));

        if (empty($result)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Global interface which checks if some bundle articles are in the basket.
     *
     * @return boolean
     */
    public function isSomeBundleInBasket()
    {
        $sql= "
            SELECT *
            FROM s_order_basket basket
            INNER JOIN s_articles_bundles bundle
                ON basket.ordernumber = bundle.ordernumber
            WHERE sessionID = ?
        ";

        $result = $this->getDatabase()->fetchAll($sql, array($this->getSessionId()));

        if (empty($result)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Global interface to get all defined prices for a single bundle.
     * Expects an valid bundle id which defined in the s_articles_bundles.
     * @param     $bundleId
     * @param int $hydrationMode
     *
     * @return array
     */
    public function getRawBundlePrices($bundleId, $hydrationMode = \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('prices'))
                ->from('Shopware\CustomModels\Bundle\Price', 'prices')
                ->where('prices.bundleId = :bundleId')
                ->setParameters(array('bundleId' => $bundleId));

        return $builder->getQuery()->getResult($hydrationMode);
    }

    /**
     * Global interface to decrease the bundle stock.
     * Expects a valid bundle id which defined in the s_articles_bundles.
     * If the limited property of the passed bundle is set to true, the quantity will be decrease by one.
     *
     * @param $bundle \Shopware\CustomModels\Bundle\Bundle
     *
     * @return void
     */
    public function decreaseBundleStock($bundle)
    {
        $sql= "
            UPDATE s_articles_bundles
            SET max_quantity = max_quantity - 1
            WHERE s_articles_bundles.id = ?
        ";
        $this->getDatabase()->query($sql, array($bundle->getId()));
    }
}
