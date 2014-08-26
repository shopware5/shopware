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

namespace Shopware\Bundle\SearchBundleDBAL\SearchTerm;

use Shopware\Bundle\SearchBundleDBAL\KeywordFinderInterface;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundleDBAL\SearchTerm
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CacheKeywordFinder implements KeywordFinderInterface
{
    /**
     * @var KeywordFinderInterface
     */
    private $keywordFinder;

    /**
     * @var \Zend_Cache_Core
     */
    private $cache;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @param \Zend_Cache_Core $cache
     * @param \Shopware_Components_Config $config
     * @param KeywordFinderInterface $keywordFinder
     */
    public function __construct(
        \Zend_Cache_Core $cache,
        \Shopware_Components_Config $config,
        KeywordFinderInterface $keywordFinder
    ) {
        $this->cache = $cache;
        $this->config = $config;
        $this->keywordFinder = $keywordFinder;
    }

    /**
     * @param $term
     * @return Keyword[]
     */
    public function getKeywordsOfTerm($term)
    {
        $id = md5('Shopware_Modules_Search_' . $term);

        if (($keywords = $this->cache->load($id)) !== false) {
            return $keywords;
        }

        $keywords = $this->keywordFinder->getKeywordsOfTerm($term);

        $this->cache->save(
            $keywords,
            $id,
            array('Shopware_Modules_Search'),
            $this->config->get('cachesearch')
        );

        return $keywords;
    }
}
