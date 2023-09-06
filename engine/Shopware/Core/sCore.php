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

/**
 * Shopware Class providing helper functions for post dispatch url rewriting
 */
class sCore implements \Enlight_Hook
{
    /**
     * The Front controller object
     * Needed to retrieve the request and router
     *
     * @var Enlight_Controller_Front
     */
    private $front;

    public function __construct($front = null)
    {
        $this->front = $front ?: Shopware()->Front();
    }

    /**
     * Creates query string for an url based on sVariables and Request GET variables
     *
     * @param array $sVariables Variables that configure the generated url
     *
     * @return string
     */
    public function sBuildLink($sVariables)
    {
        $url = [];
        $allowedCategoryVariables = ['sCategory', 'sPage'];

        $tempGET = $this->front->Request() ? $this->front->Request()->getParams() : [];

        // If viewport is available, this will be the first variable
        if (!empty($tempGET['sViewport'])) {
            $url['sViewport'] = $tempGET['sViewport'];
            if ($url['sViewport'] === 'cat') {
                foreach ($allowedCategoryVariables as $allowedVariable) {
                    if (!empty($tempGET[$allowedVariable])) {
                        $url[$allowedVariable] = $tempGET[$allowedVariable];
                        unset($tempGET[$allowedVariable]);
                    }
                }
                $tempGET = [];
            }
            unset($tempGET['sViewport']);
        }

        // Strip new variables from _GET
        foreach ($sVariables as $getKey => $getValue) {
            $tempGET[$getKey] = $getValue;
        }

        // Strip session from array
        unset($tempGET['coreID']);
        unset($tempGET['sPartner']);

        foreach ($tempGET as $getKey => $getValue) {
            if ($getValue) {
                $url[$getKey] = $getValue;
            }
        }

        if (!empty($url)) {
            $queryString = '?' . http_build_query($url, '', '&');
        } else {
            $queryString = '';
        }

        return $queryString;
    }

    /**
     * Tries to rewrite the provided link using SEO friendly urls
     *
     * @param string|null $link  the link to rewrite
     * @param string|null $title title of the link or related element
     *
     * @return mixed|string Complete url, rewritten if possible
     */
    public function sRewriteLink($link = null, $title = null)
    {
        $url = html_entity_decode(str_replace(',', '=', $link ?? ''));
        $query = (string) parse_url($url, PHP_URL_QUERY);
        parse_str($query, $queryArray);

        if (!empty($title)) {
            $queryArray['title'] = $title;
        }
        $queryArray['module'] = 'frontend';

        $newQueryArray = [];
        foreach ($queryArray as $key => $queryItem) {
            $newQueryArray[(string) $key] = $queryItem;
        }

        return $this->front->ensureRouter()->assemble($newQueryArray);
    }
}
