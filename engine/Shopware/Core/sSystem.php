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

use Shopware\Components\LegacyRequestWrapper\PostWrapper;
use Shopware\Components\LegacyRequestWrapper\GetWrapper;
use Shopware\Components\LegacyRequestWrapper\CookieWrapper;

/**
 * Deprecated Shopware Class
 */
class sSystem
{
    /**
     * Shopware configuration
     *
     * @var Shopware_Components_Config
     * @deprecated Use Shopware()->Config()
     */
    public $sCONFIG;

    /**
     * Current session id
     *
     * @var string
     * @deprecated Use Shopware()->SessionID()
     */
    public $sSESSION_ID;

    /**
     * Pointer to Smarty
     *
     * @var Enlight_Template_Manager
     * @deprecated Use Shopware()->Template()
     */
    public $sSMARTY;

    /**
     * Pointer to the different modules and its inherits
     *
     * @var Shopware_Components_Modules
     * @deprecated Use Shopware()->Modules()
     */
    public $sMODULES;

    /**
     * Current customer group
     *
     * @var string
     */
    public $sUSERGROUP;

    /**
     * Information about customer group
     *
     * @var array
     */
    public $sUSERGROUPDATA;

    /**
     * Session data
     *
     * @var Enlight_Components_Session_Namespace Session
     * @deprecated Use Shopware()->Session()
     */
    public $_SESSION;

    /**
     * @var \Shopware\Components\LegacyRequestWrapper\PostWrapper Wrapper for _POST
     */
    private $postWrapper;

    /**
     * @var \Shopware\Components\LegacyRequestWrapper\GetWrapper Wrapper for _GET
     */
    private $getWrapper;

    /**
     * @var \Shopware\Components\LegacyRequestWrapper\CookieWrapper Wrapper for _COOKIE
     */
    private $cookieWrapper;

    /**
     * Path to article images
     *
     * @var string
     */
    public $sPathArticleImg;

    /**
     * Path to banners
     *
     * @var string
     */
    public $sPathBanner;

    /**
     * Path to Start
     *
     * @var string
     */
    public $sPathStart;

    /**
     * Current active currency
     *
     * @var array
     * @deprecated Use Shopware()->Shop()->getCurrency() or Shopware()->Shop()->getCurrency()->toArray()
     */
    public $sCurrency;

    /**
     * Pointer to PHP-Mailer Object
     *
     * @var
     * @deprecated Use Shopware()->Mail()
     */
    public $sMailer;

    /**
     * True if user is identified as bot
     *
     * @var bool
     * @deprecated Use Shopware()->Session()->Bot
     */
    public $sBotSession;

    /**
     * Reference to $this, for compability reasons.
     *
     * @var sSystem
     * @deprecated
     */
    public $sSYSTEM;

    /**
     * @param Enlight_Controller_Request_Request $request The request object
     */
    public function __construct(Enlight_Controller_Request_Request $request = null)
    {
        $request = $request ? : new Enlight_Controller_Request_RequestHttp();
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
                break;
            case '_GET':
                return $this->getWrapper;
                break;
            case '_COOKIE':
                return $this->cookieWrapper;
                break;
        }

        return null;
    }

    /**
     * @deprecated Use Shopware()->Modules()->Core()->(method name)
     *
     * @param $name
     * @param null $params
     * @return mixed
     */
    public function __call($name, $params = null)
    {
        return call_user_func_array(array(Shopware()->Modules()->Core(), $name), $params);
    }
}
