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

namespace Shopware\Components;

use Enlight_Controller_Front;
use Fig\Link\GenericLinkProvider;
use Fig\Link\Link;

/**
 * Class WebLinkManager
 * @package Shopware\Components
 */
class WebLinkManager
{
    /**
     * @var Enlight_Controller_Front
     */
    private $front;

    /**
     * WebLinkManager constructor.
     * @param Enlight_Controller_Front $front
     */
    public function __construct(Enlight_Controller_Front $front)
    {
        $this->front = $front;
    }

    /**
     * Adds a "Link" HTTP header.
     *
     * @param string $uri        The relation URI
     * @param string $rel        The relation type (e.g. "preload", "prefetch", "prerender" or "dns-prefetch")
     * @param array  $attributes The attributes of this link (e.g. "array('as' => true)", "array('pr' => 0.5)")
     *
     * @return string The relation URI
     */
    public function link($uri, $rel, array $attributes = array())
    {
        // Remove smarty quotes
        $uri = $value = trim($uri, '"\'');

        foreach ($attributes as &$attribute) {
            $attribute = trim($attribute, '"\'');
        }

        if (empty($uri)) {
            return $uri;
        }

        if (strpos($uri, 'http') === 0) {
            $uri = parse_url($uri, PHP_URL_PATH);
        }

        $link = new Link($rel, $uri);
        foreach ($attributes as $key => $value) {
            $link = $link->withAttribute($key, $value);
        }
        $linkProvider = $this->front->Request()->getParam('_links', new GenericLinkProvider());
        $this->front->Request()->setParam('_links', $linkProvider->withLink($link));
        return $uri;
    }

    /**
     * Preloads a resource.
     *
     * @param string $uri        A public path
     * @param array  $attributes The attributes of this link (e.g. "array('as' => true)", "array('crossorigin' => 'use-credentials')")
     *
     * @return string The path of the asset
     */
    public function preload($uri, array $attributes = array())
    {
        return $this->link($uri, 'preload', $attributes);
    }

    /**
     * Resolves a resource origin as early as possible.
     *
     * @param string $uri        A public path
     * @param array  $attributes The attributes of this link (e.g. "array('as' => true)", "array('pr' => 0.5)")
     *
     * @return string The path of the asset
     */
    public function dnsPrefetch($uri, array $attributes = array())
    {
        return $this->link($uri, 'dns-prefetch', $attributes);
    }

    /**
     * Initiates a early connection to a resource (DNS resolution, TCP handshake, TLS negotiation).
     *
     * @param string $uri        A public path
     * @param array  $attributes The attributes of this link (e.g. "array('as' => true)", "array('pr' => 0.5)")
     *
     * @return string The path of the asset
     */
    public function preconnect($uri, array $attributes = array())
    {
        return $this->link($uri, 'preconnect', $attributes);
    }

    /**
     * Indicates to the client that it should prefetch this resource.
     *
     * @param string $uri        A public path
     * @param array  $attributes The attributes of this link (e.g. "array('as' => true)", "array('pr' => 0.5)")
     *
     * @return string The path of the asset
     */
    public function prefetch($uri, array $attributes = array())
    {
        return $this->link($uri, 'prefetch', $attributes);
    }

    /**
     * Indicates to the client that it should prerender this resource .
     *
     * @param string $uri        A public path
     * @param array  $attributes The attributes of this link (e.g. "array('as' => true)", "array('pr' => 0.5)")
     *
     * @return string The path of the asset
     */
    public function prerender($uri, array $attributes = array())
    {
        return $this->link($uri, 'prerender', $attributes);
    }
}