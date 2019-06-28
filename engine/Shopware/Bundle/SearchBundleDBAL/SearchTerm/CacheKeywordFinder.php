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

namespace Shopware\Bundle\SearchBundleDBAL\SearchTerm;

use Shopware\Bundle\SearchBundleDBAL\KeywordFinderInterface;
use Shopware\Bundle\StoreFrontBundle\Service\CacheInterface;

class CacheKeywordFinder implements KeywordFinderInterface
{
    /**
     * @var KeywordFinderInterface
     */
    private $keywordFinder;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @internal param $CacheInterface
     */
    public function __construct(
        CacheInterface $cache,
        \Shopware_Components_Config $config,
        KeywordFinderInterface $keywordFinder
    ) {
        $this->cache = $cache;
        $this->config = $config;
        $this->keywordFinder = $keywordFinder;
    }

    /**
     * @param string $term
     *
     * @return Keyword[]
     */
    public function getKeywordsOfTerm($term)
    {
        $id = md5('Shopware_Modules_Search_' . $term);

        if (($keywords = $this->cache->fetch($id)) !== false) {
            return $keywords;
        }

        $keywords = $this->keywordFinder->getKeywordsOfTerm($term);

        $this->cache->save(
            $id,
            $keywords,
            $this->config->get('cachesearch')
        );

        return $keywords;
    }
}
