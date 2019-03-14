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

/**
 * Shopware Application
 */
class Shopware_Plugins_Core_PostFilter_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @var string
     */
    protected static $baseFile;

    /**
     * @var string
     */
    protected $basePathUrl = '';

    /**
     * @var string
     */
    protected $basePath = '';

    /**
     * @var bool
     */
    protected $useSecure = false;

    /**
     * @var string[]
     */
    protected $backLinkWhiteList = [];

    /**
     * @var string[]
     */
    protected $urls;

    /**
     * Install filter plugin
     *
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Plugins_ViewRenderer_FilterRender',
            'onFilterRender'
        );

        return true;
    }

    /**
     * Plugin event method
     */
    public function onFilterRender(Enlight_Event_EventArgs $args)
    {
        /** @var Enlight_Controller_Request_RequestHttp $request */
        $request = $args->getSubject()->Action()->Request();
        /** @var Enlight_Controller_Response_ResponseHttp $response */
        $response = $args->getSubject()->Action()->Response();

        $source = $args->getReturn();

        $headers = $response->getHeaders();
        foreach ($headers as $header) {
            if ($header['name'] === 'Content-Type' && strpos($header['value'], 'application/javascript') === 0) {
                $source = str_replace(["\r\n", "\r"], "\n", $source);
                $expressions = [
                    // Remove comments
                    '#/\*.*?\*/#ms' => '',
                    '#^\s*//.*$#m' => '',
                    //'#\n\s+#ms' => '',
                    '#^\s+#ms' => '',
                    //'#\s+$#ms' => '',
                ];
                $source = preg_replace(array_keys($expressions), array_values($expressions), $source);

                return $source;
            }
        }
        if (!in_array($request->getModuleName(), ['frontend', 'widgets'], true)) {
            return $args->getReturn();
        }
        $source = $this->filterUrls($source);

        return $this->filterSource($source);
    }

    /**
     * Initializes plugin config
     *
     * @throws Exception
     */
    public function initConfig()
    {
        $shopConfig = Shopware()->Config();
        self::$baseFile = $shopConfig->baseFile;
        $this->useSecure = Shopware()->Front()->Request()->isSecure();

        $request = Shopware()->Front()->Request();
        $this->basePath = $request->getHttpHost() . $request->getBasePath() . '/';
        $this->basePathUrl = $request->getScheme() . '://' . $this->basePath;

        $this->backLinkWhiteList = preg_replace('#\s#', '', $shopConfig->seoBackLinkWhiteList);
        $this->backLinkWhiteList = explode(',', $this->backLinkWhiteList);

        $hosts = $this->getShopHosts();

        $this->backLinkWhiteList = array_merge(
            $this->backLinkWhiteList,
            array_map('trim', $hosts)
        );
    }

    /**
     * Filter html source
     *
     * @param string $source
     *
     * @return string|null
     */
    public function &filterSource($source)
    {
        // To allow the return of a reference, we need to add an interim variable
        $source = preg_replace_callback('#<(a|form|iframe|link|img)[^<>]*(href|src|action)="([^"]*)".*>#Umsi', [$this, 'rewriteSrc'], $source);

        return $source;
    }

    /**
     * Rewrite source link
     *
     * @see \Shopware_Controllers_Backend_Newsletter::outputFilter
     *
     * @param array $src
     *
     * @throws Exception
     * @throws SmartyException
     *
     * @return string
     */
    public function rewriteSrc($src)
    {
        if (!$this->basePath) {
            $this->initConfig();
        }

        if (empty($src[3])) {
            return $src[0];
        }

        if (!empty($this->backLinkWhiteList)) {
            if ($src[1] === 'a' && preg_match('#^https?://#', $src[3])) {
                $host = @parse_url($src[3], PHP_URL_HOST);
                if (!strstr($src[0], 'rel=') && !in_array($host, $this->backLinkWhiteList)) {
                    $src[0] = rtrim($src[0], '>') . ' rel="nofollow noopener">';
                }
            }
        }

        $link = $src[3];

        $anchorPart = '';
        if (strpos($link, '#') !== false) {
            $anchorPart = substr($link, strpos($link, '#'));
        }

        switch ($src[1]) {
            case 'input':
            case 'img':
            case 'link':
            case 'script':
                if (strpos($src[3], self::$baseFile) === 0) {
                    $link = $this->rewriteLink($src[3]);
                }
                break;
            case 'form':
            case 'a':
                if (strpos($src[3], self::$baseFile) === 0) {
                    $link = $this->rewriteLink($src[3]);
                }
                break;
            default:
                break;
        }

        if (strpos($link, '{media') === 0) {
            $link = $this->handleMediaPlugin($link);
        } else {
            if (strpos($link, 'www.') === 0) {
                $link = 'http://' . $link;
            }
            if (!preg_match('#^[a-z]+:|^\#|^/#', $link)) {
                $link = $this->basePathUrl . $link;
            }
        }

        if ($anchorPart !== '' && strpos($link, $anchorPart) === false) {
            $link .= $anchorPart;
        }

        // Check if the current link is a canonical link
        $isCanonical = (
            strpos($src[0], 'rel="canonical"') !== false
            || strpos($src[0], 'rel="prev"') !== false
            || strpos($src[0], 'rel="next"') !== false
        );

        if ($this->useSecure && !$isCanonical && $src[1] !== 'a') {
            $link = str_replace('http://' . $this->basePath, 'https://' . $this->basePath, $link);
        }

        $src[0] = str_replace($src[2] . '="' . $src[3] . '"', $src[2] . '="' . $link . '"', $src[0]);

        return $src[0];
    }

    /**
     * Returns plugin capabilities
     *
     * @return array
     */
    public function getCapabilities()
    {
        return [
            'install' => false,
            'enable' => false,
            'update' => true,
        ];
    }

    /**
     * @param string $source
     *
     * @return string|string[]|null
     */
    protected function filterUrls($source)
    {
        /** @var \Shopware\Components\Routing\RouterInterface $router */
        $router = $this->get('router');
        $baseFile = preg_quote($router->getContext()->getBaseFile(), '#');
        $regex = '#<(a|form|iframe|link|img)[^<>]*(href|src|action)="(' . $baseFile . '[^"]*)".*>#Umsi';
        if (preg_match_all($regex, $source, $matches) > 0) {
            $urls = array_map('htmlspecialchars_decode', $matches[3]);
            $this->urls = array_combine($matches[3], $router->generateList($urls));
        }
        // Rewrite urls in rss and atom feeds
        $regex = '#<(guid|link|id)>(' . $baseFile . '[^<]*)</(guid|link|id)>#Umsi';
        if (preg_match_all($regex, $source, $matches) > 0) {
            $urls = array_map('htmlspecialchars_decode', $matches[2]);
            $urls = array_combine($matches[2], $router->generateList($urls));
            $source = preg_replace_callback($regex, function ($found) use (&$urls) {
                return '<' . $found[1] . '>' . $urls[$found[2]] . '</' . $found[3] . '>';
            }, $source);
        }

        return $source;
    }

    /**
     * Rewrite a link
     *
     * @param string $link
     *
     * @return string
     */
    protected function rewriteLink($link = null)
    {
        return isset($this->urls[$link]) ? htmlspecialchars($this->urls[$link]) : $link;
    }

    /**
     * @throws Exception
     *
     * @return array
     */
    private function getShopHosts()
    {
        $shop = $this->get('shop');
        if ($shop->getMain()) {
            $shop = $shop->getMain();
        }

        $shopHosts = $this->get('dbal_connection')->fetchAssoc(
            'SELECT host, hosts FROM s_core_shops WHERE id = :id',
            [':id' => $shop->getId()]
        );

        $hosts = [$shopHosts['host']];
        if (!empty($shopHosts['hosts'])) {
            $hosts = array_merge($hosts, explode("\n", $shopHosts['hosts']));
        }
        $hosts = array_filter($hosts);

        return $hosts;
    }

    /**
     * @param string $link
     *
     * @throws SmartyException
     *
     * @return string
     */
    private function handleMediaPlugin($link)
    {
        // remove beginning and end of tag {media ...}
        $link = ltrim($link, '{media ');
        $link = substr($link, 0, -1);

        $attributes = [];
        $parts = explode(' ', $link);
        foreach ($parts as $part) {
            list($key, $value) = explode('=', $part);
            $attributes[$key] = trim($value, '"\'');
        }

        // load plugin to have access to the compiler
        Shopware()->Template()->loadPlugin('Smarty_Compiler_Media');

        $compiler = new Smarty_Compiler_Media();
        $attributes = $compiler->parseAttributes($attributes);

        return $attributes['path'];
    }
}
