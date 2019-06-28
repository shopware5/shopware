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

class QueryAliasMapper
{
    /**
     * Array containing the query alias mappings
     *
     * @var string[]
     */
    private $queryAliasMappings;

    /**
     * [
     *    'sSearch' => 'q',
     *    'sPage'   => 'p',
     * ]
     *
     * @param string[] $queryAliasMappings
     */
    public function __construct(array $queryAliasMappings)
    {
        $this->queryAliasMappings = $queryAliasMappings;
    }

    /**
     * @return QueryAliasMapper
     */
    public static function createFromConfig(\Shopware_Components_Config $config)
    {
        $queryAliases = $config->get('SeoQueryAlias');

        return self::createFromString($queryAliases);
    }

    /**
     * @param string $aliases Example: "sSearch=q,sPage=p,sPerPage=n"
     *
     * @return QueryAliasMapper
     */
    public static function createFromString($aliases)
    {
        if (empty($aliases)) {
            return new self([]);
        }

        $queryAliases = [];

        foreach (explode(',', $aliases) as $alias) {
            list($key, $value) = explode('=', trim($alias));
            $queryAliases[$key] = $value;
        }

        return new self($queryAliases);
    }

    /**
     * Return a key / value array containing
     * the long parameter name as array key
     * and the alias name as array value
     *
     * [
     *    'sSearch' => 'q',
     *    'sPage'   => 'p',
     * ]
     *
     * @return string[]
     */
    public function getQueryAliases()
    {
        return $this->queryAliasMappings;
    }

    /**
     * Returns the short form of an given alias
     *
     * $this->getQueryAlias('sSearch') returns 'q'
     *
     * @param string $key
     *
     * @return string|null
     */
    public function getShortAlias($key)
    {
        $list = $this->getQueryAliases();

        return isset($list[$key]) ? $list[$key] : null;
    }

    /**
     * Replaces the query params with their matching long form
     */
    public function replaceShortRequestQueries(\Enlight_Controller_Request_RequestHttp $request)
    {
        foreach ($this->getQueryAliases() as $key => $alias) {
            $value = $request->getQuery($alias);
            if ($value !== null) {
                $request->setQuery($key, $value);
                $request->setQuery($alias, null);
            }
        }
    }

    /**
     * Input:
     * [
     *   'sPage' => 1,
     *   'sSort' => 3,
     *   'foo'   => 'bar,
     * ]
     *
     * Output:
     * [
     *    'p' => 1,
     *    'o' => 3,
     *    'foo' => 'bar'
     * ]
     *
     * @param string[] $params
     *
     * @return string[]
     */
    public function replaceLongParams($params)
    {
        $tmp = [];
        foreach ($params as $key => $value) {
            if ($alias = $this->getShortAlias($key)) {
                $tmp[$alias] = $value;
            } else {
                $tmp[$key] = $value;
            }
        }

        return $tmp;
    }

    /**
     * Input:
     * [
     *    'p' => 1,
     *    'o' => 3,
     *    'foo' => 'bar'
     * ]
     *
     * Output:
     *
     * [
     *   'sPage' => 1,
     *   'sSort' => 3,
     *   'foo'   => 'bar,
     * ]
     *
     * @param string[] $params
     *
     * @return string[]
     */
    public function replaceShortParams($params)
    {
        foreach ($this->getQueryAliases() as $key => $alias) {
            if (isset($params[$alias])) {
                $params[$key] = $params[$alias];
                unset($params[$alias]);
            }
        }

        return $params;
    }
}
