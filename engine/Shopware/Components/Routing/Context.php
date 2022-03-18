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

namespace Shopware\Components\Routing;

use Enlight_Controller_Request_Request as EnlightRequest;
use JsonSerializable;
use Shopware\Components\ObjectJsonSerializeTraitDeprecated;
use Shopware\Models\Shop\Shop as ShopwareShop;
use Shopware_Components_Config as ShopwareConfig;

/**
 * @see http://php.net/manual/en/reflectionclass.iscloneable.php
 * @see http://api.symfony.com/2.0/Symfony/Component/Routing/RequestContext.html
 * @see \Enlight_Controller_Request_Request
 */
class Context implements JsonSerializable
{
    use ObjectJsonSerializeTraitDeprecated;

    /**
     * @var array<string, mixed>
     */
    public $params = [];

    /**
     * Only for NOT mode_rewrite mode
     *
     * @var string
     */
    protected $baseFile = 'shopware.php';

    /**
     * @see \Enlight_Controller_Request_Request::getHttpHost
     *
     * @var string
     */
    protected $host = 'localhost';

    /**
     * @var string
     */
    protected $baseUrl = '';

    /**
     * @var bool
     */
    protected $secure = false;

    /**
     * @var bool
     */
    protected $removeCategory = false;

    /**
     * @var bool
     */
    protected $urlToLower = false;

    /**
     * @var int|null
     */
    protected $shopId;

    /**
     * @var array<string, mixed>
     */
    protected $globalParams = [];

    /**
     * Module keys for retrieving module from params
     *
     * @see \Enlight_Controller_Request_Request::$_moduleKey
     *
     * @var string
     */
    protected $moduleKey = 'module';

    /**
     * Controller key for retrieving controller from params
     *
     * @var string
     */
    protected $controllerKey = 'controller';

    /**
     * Action key for retrieving action from params
     *
     * @var string
     */
    protected $actionKey = 'action';

    /**
     * @param string               $host
     * @param string               $baseUrl
     * @param bool                 $secure
     * @param array<string, mixed> $globalParams
     */
    public function __construct($host = 'localhost', $baseUrl = '', $secure = false, array $globalParams = [])
    {
        $this->host = $host;
        $this->baseUrl = $baseUrl;
        $this->secure = $secure;
        $this->globalParams = $globalParams;
    }

    /**
     * @return string
     */
    public function getBaseFile()
    {
        return $this->baseFile;
    }

    /**
     * @param string $baseFile
     *
     * @return void
     */
    public function setBaseFile($baseFile)
    {
        $this->baseFile = $baseFile;
    }

    /**
     * @return string|null
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string|null $host
     *
     * @return void
     */
    public function setHost($host)
    {
        if ($host !== null) {
            $this->host = $host;
        }
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param string $baseUrl
     *
     * @return void
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @return bool
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * @param bool $secure
     *
     * @return void
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;
    }

    /**
     * @return bool
     */
    public function isRemoveCategory()
    {
        return $this->removeCategory;
    }

    /**
     * @param bool $removeCategory
     *
     * @return void
     */
    public function setRemoveCategory($removeCategory)
    {
        $this->removeCategory = $removeCategory;
    }

    /**
     * @return bool
     */
    public function isUrlToLower()
    {
        return $this->urlToLower;
    }

    /**
     * @param bool $urlToLower
     *
     * @return void
     */
    public function setUrlToLower($urlToLower)
    {
        $this->urlToLower = $urlToLower;
    }

    /**
     * @return int|null
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @param int $shopId
     *
     * @return void
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;
    }

    /**
     * @param string     $name
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function getParam($name, $default = null)
    {
        return $this->params[$name] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string     $name
     * @param mixed|null $param
     *
     * @return void
     */
    public function setParam($name, $param)
    {
        $this->params[$name] = $param;
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return void
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * @param string      $name
     * @param string|null $default
     *
     * @return string
     */
    public function getGlobalParam($name, $default = null)
    {
        return $this->globalParams[$name] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function getGlobalParams()
    {
        return $this->globalParams;
    }

    /**
     * @param string     $name
     * @param mixed|null $globalParam
     *
     * @return void
     */
    public function setGlobalParam($name, $globalParam)
    {
        $this->globalParams[$name] = $globalParam;
    }

    /**
     * @see Enlight_Controller_Router::setGlobalParam
     *
     * @param array<string, mixed> $globalParams
     *
     * @return void
     */
    public function setGlobalParams($globalParams)
    {
        $this->globalParams = $globalParams;
    }

    /**
     * @return string
     */
    public function getModuleKey()
    {
        return $this->moduleKey;
    }

    /**
     * @return string
     */
    public function getControllerKey()
    {
        return $this->controllerKey;
    }

    /**
     * @return string
     */
    public function getActionKey()
    {
        return $this->actionKey;
    }

    /**
     * @return Context
     */
    public static function createEmpty()
    {
        return new self();
    }

    /**
     * @return Context
     */
    public static function createFromEnlightRequest(EnlightRequest $request)
    {
        return new self(
            $request->getHttpHost(),
            $request->getBaseUrl(),
            $request->isSecure(),
            self::getGlobalParamsFromRequest($request)
        );
    }

    /**
     * @see \Enlight_Controller_Router::setGlobalParam
     *
     * @return array{module?: string, controller?: string, action?: string}
     */
    public static function getGlobalParamsFromRequest(EnlightRequest $request)
    {
        $globalParams = [];
        if ($request->getModuleName()) {
            $globalParams['module'] = $request->getModuleName();
            if ($request->getControllerName() !== null) {
                $globalParams['controller'] = $request->getControllerName();
                if ($request->getActionName() !== null) {
                    $globalParams['action'] = $request->getActionName();
                }
            }
        }

        return $globalParams;
    }

    /**
     * @return void
     */
    public function updateFromEnlightRequest(EnlightRequest $request)
    {
        $this->setHost($request->getHttpHost());
        $this->setBaseUrl($request->getBaseUrl());
        $this->setSecure($request->isSecure());
        $this->setGlobalParams(self::getGlobalParamsFromRequest($request));
        $this->setParams($request->getQuery());
    }

    /**
     * @return Context
     */
    public static function createFromShop(ShopwareShop $shop, ShopwareConfig $config)
    {
        $self = new self(
            $shop->getHost() ?? 'localhost',
            $shop->getBaseUrl() ?? '',
            $shop->getSecure(),
            []
        );
        $self->setShopId($shop->getId());
        $self->setUrlToLower($config->get('routerToLower'));
        $self->setBaseFile($config->get('baseFile'));
        $self->setRemoveCategory((bool) $config->get('routerRemoveCategory'));

        return $self;
    }
}
