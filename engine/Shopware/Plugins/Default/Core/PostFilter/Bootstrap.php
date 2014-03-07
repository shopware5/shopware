<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
 *
 * Shopware Application
 */
class Shopware_Plugins_Core_PostFilter_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
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

    protected static $shopConfig;
    protected static $baseFile = '';
    protected $basePathUrl = '';
    protected $basePath = '';
    protected $mediaPaths;
    protected $useSecure = false;
    protected $backLinkWhiteList = array();

    /**
     * Plugin event method
     *
     * @param Enlight_Event_EventArgs $args
     * @return mixed
     */
    public function onFilterRender(Enlight_Event_EventArgs $args)
    {
        $request = $args->getSubject()->Action()->Request();
        /** @var $response Enlight_Controller_Response_ResponseHttp */
        $response = $args->getSubject()->Action()->Response();

        $headers = $response->getHeaders();
        foreach ($headers as $header) {
            if ($header['name'] == 'Content-Type' && strpos($header['value'], 'application/javascript') === 0) {
                $source = $args->getReturn();

                $source = str_replace(array("\r\n", "\r"), "\n", $source);
                $expressions = array(
                    // Remove comments
                    '#/\*.*?\*/#ms' => '',
                    '#^\s*//.*$#m' => '',
                    //'#\n\s+#ms' => '',
                    '#^\s+#ms' => '',
                    //'#\s+$#ms' => '',
                );
                $source = preg_replace(array_keys($expressions), array_values($expressions), $source);

                return $source;
            }
        }

        if ($request->getModuleName() !== 'frontend' && $request->getModuleName() !== 'widgets') {
            return $args->getReturn();
        }
        return $this->filterSource($args->getReturn());
    }

    /**
     * Initializes plugin config
     */
    public function initConfig()
    {
        self::$shopConfig = Shopware()->Config();
        self::$baseFile = self::$shopConfig->baseFile;
        $this->useSecure = Shopware()->Front()->Request()->isSecure();

        $request = Shopware()->Front()->Request();
        $this->basePath = $request->getHttpHost() . $request->getBasePath() . '/';
        $this->basePathUrl = $request->getScheme() . '://' . $this->basePath;

        $this->backLinkWhiteList = preg_replace('#\s#', '', self::$shopConfig->seoBackLinkWhiteList);
        $this->backLinkWhiteList = explode(',', $this->backLinkWhiteList);

        if (!empty(Shopware()->System()->sSubShops)) {
            foreach (Shopware()->System()->sSubShops as $subshop) {
                $domains = explode("\n", $subshop['domainaliase']);
                $domain = trim(reset($domains));
                if (!empty($domain)) {
                    $this->backLinkWhiteList[] = $domain;
                }
            }
        }
    }

    /**
     * Filter html source
     *
     * @param string $source
     * @return string
     */
    public function &filterSource($source)
    {
        // Rewrite path for <link href - CSS-Styles
        $source = preg_replace_callback('#<(a|form|iframe|link|img)[^<>]*(href|src|action)="([^"]*)".*>#Umsi', array($this, 'rewriteSrc'), $source);

        // User defined, runtime rewriterules
        //todo@hl Add this as plugin config
        //$sql = 'SELECT search, `replace` FROM s_core_rewrite ORDER BY id ASC';
        //$replaceRules = Shopware()->Db()->fetchPairs($sql);
        //if (!empty($replaceRules)) {
        //    $source = preg_replace(array_keys($replaceRules), array_values($replaceRules), $source);
        //}

        return $source;
    }

    /**
     * Rewrite source link
     *
     * @param array $src
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
            if ($src[1] == 'a' && preg_match('#^https?://#', $src[3])) {
                $host = @parse_url($src[3], PHP_URL_HOST);
                if (!strstr($src[0], 'rel=') && !in_array($host, $this->backLinkWhiteList)) {
                    $src[0] = rtrim($src[0], '>') . ' rel="nofollow">';
                }
            }
        }

        $link = $src[3];
        switch ($src[1]) {
            case 'td':
            case 'input':
            case 'img':
            case 'link':
            case 'script':
                if (!empty($this->mediaPaths) && strpos($src[3], '../../') === 0) {
                    $file = substr($src[3], 6);
                    $file = str_replace('get.php?file=', '', $file);
                    $query = strstr($file, '?');
                    $file = parse_url($file, PHP_URL_PATH);
                    foreach ($this->mediaPaths as $testpath) {
                        if (file_exists(Shopware()->OldPath() . $testpath . $file)) {
                            $link = $this->basePathUrl . $testpath . $file;
                            if (!empty($query)) {
                                $link .= $query;
                            }
                            break;
                        }
                    }
                } elseif (strpos($src[3], self::$baseFile) === 0) {
                    if (preg_match('#title="([^"]+)"#', $src[0], $match)) {
                        $title = $match[1];
                    } else {
                        $title = null;
                    }
                    $link = $this->rewriteLink($src[3], $title);
                }
                break;
            case 'form':
            case 'a':
                if (strpos($src[3], self::$baseFile) === 0) {
                    if (preg_match('#title="([^"]+)"#', $src[0], $match)) {
                        $title = $match[1];
                    } else {
                        $title = null;
                    }
                    $link = $this->rewriteLink($src[3], $title);
                }
                break;
            case 'iframe':
                // Bugfix for external payment means
                if (preg_match('#^[./]+engine/connectors/#', $src[3])) {
                    $link = $this->basePathUrl . preg_replace('#^[./]+#', '', $src[3]);
                }
                break;
            default:
                break;
        }

        if (strpos($link, 'www.') === 0) {
            $link = 'http://' . $link;
        }
        if (!preg_match('#^[a-z]+:|^\#|^/#', $link)) {
            $link = $this->basePathUrl . $link;
        }

        //check canonical shopware configuration
        $forceUnsecureCanonical = Shopware()->Config()->get('forceUnsecureCanonical');

        //check if the current link is a canonical link
        $isCanonical = (strpos($src[0], 'rel="canonical"') !== false);

        $replaceCanonical = !($isCanonical && $forceUnsecureCanonical);

        if ($this->useSecure && $src[1] != 'a' && $replaceCanonical) {
            $link = str_replace('http://' . $this->basePath, 'https://' . $this->basePath, $link);
        }

        $src[0] = str_replace($src[2] . '="' . $src[3] . '"', $src[2] . '="' . $link . '"', $src[0]);
        return $src[0];
    }

    /**
     * Rewrite a link with the title
     *
     * @param string $link
     * @param string $title
     * @return string
     */
    public static function rewriteLink($link = null, $title = null)
    {
        if (!isset(self::$shopConfig)) {
            self::$shopConfig = Shopware()->Config();
            self::$baseFile = self::$shopConfig->baseFile;
        }

        if (strpos($link, self::$baseFile) !== 0) {
            return htmlspecialchars($link);
        }

        $url = str_replace(',', '=', $link);
        $url = html_entity_decode($url);
        $query = parse_url($url, PHP_URL_QUERY);
        parse_str($query, $query);

        if (!empty($title)) {
            $query['title'] = $title;
        }

        return htmlspecialchars(Shopware()->Front()->Router()->assemble($query));
    }

    /**
     * Returns plugin capabilities
     *
     * @return array
     */
    public function getCapabilities()
    {
        return array(
            'install' => false,
            'enable' => false,
            'update' => true
        );
    }
}
