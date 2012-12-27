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
 * @package    Shopware_Components_HttpCache
 * @subpackage HttpCache
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

namespace Shopware\Components\HttpCache;

use Symfony\Component\HttpKernel\HttpCache\Store as BaseStore,
    Symfony\Component\HttpFoundation\Request;

/**
 * Shopware Application
 *
 * todo@all: Documentation
 * <code>
 * $httpCacheStore = new Shopware\Components\HttpCache\Store($root);
 * $httpCacheStore->purgeByHeader($name);
 * </code>
 */
class Store extends BaseStore
{
    protected  $root;

    protected function getCacheKey(Request $request)
    {
        if (isset($this->keyCache[$request])) {
            return $this->keyCache[$request];
        }
        $uri = $request->getUri();
        $cookieName = 'controller-options-'
                    . $request->getBaseUrl()
                    . $request->getPathInfo();
        if ($request->cookies->has($cookieName)) {
            $uri .= '&' . $request->cookies->get($cookieName);
        }
        if ($request->cookies->has('shop')) {
            $uri .= '&__shop=' . $request->cookies->get('shop');
        }
        if ($request->cookies->has('currency')) {
            $uri .= '&__currency=' . $request->cookies->get('currency');
        }
        return $this->keyCache[$request] = 'md' . sha1($uri);
    }

    /**
     * Purges data for the given Header.
     *
     * @param   $name
     * @param   null $value
     * @return  bool
     */
    public function purgeByHeader($name, $value=null)
    {
        $headerDir = $this->root . DIRECTORY_SEPARATOR . 'md';

        if (!file_exists($headerDir)) {
            return false;
        }

        $headerFiles = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($headerDir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($headerFiles as $headerFile) {
            if (!$headerFile->isFile()) {
                continue;
            }
            $headerData = file_get_contents($headerFile->getPathname());
            $headerData = unserialize($headerData);
            $changed = false;
            foreach ($headerData as $headerIndex => $header) {
                if (!isset($header[1][$name])) {
                    continue;
                }
                $headerValue = implode(', ', $header[1][$name]). ', ';
                if(isset($value) && strpos($headerValue, $value. ', ') === false) {
                    continue;
                }
                $cacheKey = $header[1]['x-content-digest'][0];
                if (file_exists($path = $this->getPath($cacheKey))) {
                    unlink($path);
                }
                $changed = true;
                unset($headerData[$headerIndex]);
            }

            if ($changed) {
                if (empty($headerData)) {
                    unlink($headerFile->getPathname());
                } else {
                    $headerData = serialize($headerData);
                    file_put_contents($headerFile->getPathname(), $headerData);
                }
            }
        }

        return true;
    }
}
