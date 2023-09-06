<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

use Shopware\Components\LegacyRequestWrapper\CookieWrapper;
use Shopware\Components\LegacyRequestWrapper\GetWrapper;
use Shopware\Components\LegacyRequestWrapper\PostWrapper;

/**
 * @deprecated Will be removed with Shopware 5.8
 */
class sSystem implements Enlight_Hook
{
    /**
     * Shopware configuration
     *
     * @var Shopware_Components_Config
     *
     * @deprecated Will be removed with Shopware 5.8. Use Shopware()->Config() instead
     */
    public $sCONFIG;

    /**
     * Current session id
     *
     * @var string
     *
     * @deprecated Will be removed with Shopware 5.8. Use Shopware()->Session()->get('sessionId') instead
     */
    public $sSESSION_ID;

    /**
     * Pointer to Smarty
     *
     * @var Enlight_Template_Manager
     *
     * @deprecated Will be removed with Shopware 5.8. Use Shopware()->Template() instead
     */
    public $sSMARTY;

    /**
     * Pointer to the different modules and its inherits
     *
     * @var Shopware_Components_Modules
     *
     * @deprecated Will be removed with Shopware 5.8. Use Shopware()->Modules() instead
     */
    public $sMODULES;

    /**
     * Current customer group
     *
     * @var string
     *
     * @deprecated Will be removed with Shopware 5.8. Use ContextServiceInterface->getShopContext()->getCurrentCustomerGroup() instead
     */
    public $sUSERGROUP;

    /**
     * Information about customer group
     *
     * @var array
     *
     * @deprecated Will be removed with Shopware 5.8. Use ContextServiceInterface->getShopContext()->getCurrentCustomerGroup() instead
     */
    public $sUSERGROUPDATA;

    /**
     * Session data
     *
     * @var Enlight_Components_Session_Namespace Session
     *
     * @deprecated Will be removed with Shopware 5.8. Use Shopware()->Session() instead
     */
    public $_SESSION;

    /**
     * Path to product images
     *
     * @var string
     *
     * @deprecated Will be removed with Shopware 5.8 without replacement
     */
    public $sPathArticleImg;

    /**
     * Path to banners
     *
     * @var string
     *
     * @deprecated Will be removed with Shopware 5.8 without replacement
     */
    public $sPathBanner;

    /**
     * Path to Start
     *
     * @var string
     *
     * @deprecated Will be removed with Shopware 5.8 without replacement
     */
    public $sPathStart;

    /**
     * Current active currency
     *
     * @var array
     *
     * @deprecated Will be removed with Shopware 5.8. Use Shopware()->Shop()->getCurrency() or Shopware()->Shop()->getCurrency()->toArray() instead.
     */
    public $sCurrency;

    /**
     * Pointer to PHP-Mailer Object
     *
     * @var Enlight_Components_Mail
     *
     * @deprecated Will be removed with Shopware 5.8. Use Shopware()->Mail() instead
     */
    public $sMailer;

    /**
     * True if user is identified as bot
     *
     * @var bool
     *
     * @deprecated Will be removed with Shopware 5.8. Use Shopware()->Session()->get('Bot') instead
     */
    public $sBotSession;

    /**
     * Reference to $this, for compatibility reasons.
     *
     * @var sSystem
     *
     * @deprecated Will be removed with Shopware 5.8 without replacement
     */
    public $sSYSTEM;

    /**
     * @var PostWrapper Wrapper for _POST
     */
    private $postWrapper;

    /**
     * @var GetWrapper Wrapper for _GET
     */
    private $getWrapper;

    /**
     * @var CookieWrapper Wrapper for _COOKIE
     */
    private $cookieWrapper;

    /**
     * @param Enlight_Controller_Request_Request $request The request object
     */
    public function __construct(?Enlight_Controller_Request_Request $request = null)
    {
        $request = $request ?: Enlight_Controller_Request_RequestHttp::createFromGlobals();
        $this->postWrapper = new PostWrapper($request);
        $this->getWrapper = new GetWrapper($request);
        $this->cookieWrapper = new CookieWrapper($request);
        $this->sSYSTEM = $this;
    }

    public function __set($property, $value)
    {
        switch ($property) {
            case '_POST':
                $this->postWrapper->setAll($value);
                break;
            case '_GET':
                $this->getWrapper->setAll($value);
                break;
        }
    }

    public function __get($property)
    {
        switch ($property) {
            case '_POST':
                return $this->postWrapper;
            case '_GET':
                return $this->getWrapper;
            case '_COOKIE':
                return $this->cookieWrapper;
        }

        return null;
    }

    /**
     * @deprecated Will be removed with Shopware 5.8. Use Shopware()->Modules()->Core()->method_name() instead
     *
     * @param string $name
     */
    public function __call($name, $params = null)
    {
        return Shopware()->Modules()->Core()->$name($params);
    }
}
