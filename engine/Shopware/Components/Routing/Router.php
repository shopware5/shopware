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
use Enlight_Controller_Router as EnlightRouter;

class Router extends EnlightRouter implements RouterInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var MatcherInterface[]
     */
    protected $matchers;

    /**
     * @var GeneratorInterface[]|GeneratorListInterface[]
     */
    protected $generators;

    /**
     * @var PreFilterInterface[]
     */
    protected $preFilters;

    /**
     * @var PostFilterInterface[]
     */
    protected $postFilters;

    /**
     * The DI constructor of shopware router
     *
     * @param MatcherInterface[]    $matchers
     * @param PreFilterInterface[]  $preFilters
     * @param PostFilterInterface[] $postFilters
     */
    public function __construct(
        Context $context,
        array $matchers = [],
        array $generators = [],
        array $preFilters = [],
        array $postFilters = []
    ) {
        parent::__construct();

        $this->context = $context;
        $this->matchers = $matchers;
        $this->generators = $generators;
        $this->preFilters = $preFilters;
        $this->postFilters = $postFilters;
    }

    /**
     * Switch the context
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function match($pathInfo, Context $context = null)
    {
        if ($context === null) {
            $context = clone $this->context;
        }

        foreach ($this->matchers as $route) {
            $params = $route->match($pathInfo, $context);
            if (is_array($params)) {
                // Adds support for rewrite queries
                return array_merge($params, $context->getParams());
            }
        }

        return false;
    }

    /**
     * @param array[] $list
     * @param Context $context
     *
     * @return string[]|false[]
     */
    public function generateList(array $list, Context $context = null)
    {
        $context = $context === null ? $this->context : $context;
        $contextList = [];

        foreach ($list as $key => &$userParams) {
            $contextList[$key] = clone $context;
            foreach ($this->preFilters as $preFilter) {
                if ($preFilter instanceof PreFilterInterface) {
                    $userParams = $preFilter->preFilter($userParams, $contextList[$key]);
                }
            }
        }

        $urls = [];
        foreach ($this->generators as $route) {
            if ($route instanceof GeneratorListInterface) {
                $urls = $route->generateList($list, $context);
            } elseif ($route instanceof GeneratorInterface) {
                foreach ($list as $key => $params) {
                    if (isset($urls[$key]) && is_string($urls[$key])) {
                        continue;
                    }
                    $urls[$key] = $route->generate($params, $contextList[$key]);
                }
            }
        }

        foreach ($this->postFilters as $postFilter) {
            foreach ($urls as $key => &$url) {
                if ($postFilter instanceof PostFilterInterface) {
                    $url = $postFilter->postFilter($url, $contextList[$key]);
                }
            }
        }

        return $urls;
    }

    /**
     * @param array|string $userParams
     * @param Context      $context
     *
     * @return string|false
     */
    public function assemble($userParams = [], Context $context = null)
    {
        if ($context === null) {
            $context = clone $this->context;
        }
        foreach ($this->preFilters as $preFilter) {
            if ($preFilter instanceof PreFilterInterface) {
                $userParams = $preFilter->preFilter($userParams, $context);
            }
        }
        /** @var array|string|false $url */
        $url = false;
        foreach ($this->generators as $route) {
            if ($route instanceof GeneratorInterface) {
                $url = $route->generate($userParams, $context);
                if (is_string($url)) {
                    break;
                }
            }
        }
        if (!is_string($url)) {
            return false;
        }
        foreach ($this->postFilters as $postFilter) {
            if ($postFilter instanceof PostFilterInterface) {
                $url = $postFilter->postFilter($url, $context);
            }
        }

        return $url;
    }

    /**
     * @return MatcherInterface[]
     */
    public function getMatchers()
    {
        return $this->matchers;
    }

    /**
     * @param MatcherInterface[] $matchers
     */
    public function setMatchers($matchers)
    {
        $this->matchers = $matchers;
    }

    /**
     * @return GeneratorInterface[]|GeneratorListInterface[]
     */
    public function getGenerators()
    {
        return $this->generators;
    }

    /**
     * @param GeneratorInterface[]|GeneratorListInterface[] $generators
     */
    public function setGenerators($generators)
    {
        $this->generators = $generators;
    }

    /**
     * @return PreFilterInterface[]
     */
    public function getPreFilters()
    {
        return $this->preFilters;
    }

    /**
     * @param PreFilterInterface[] $preFilters
     */
    public function setPreFilters($preFilters)
    {
        $this->preFilters = $preFilters;
    }

    /**
     * @return PostFilterInterface[]
     */
    public function getPostFilters()
    {
        return $this->postFilters;
    }

    /**
     * @param PostFilterInterface[] $postFilters
     */
    public function setPostFilters($postFilters)
    {
        $this->postFilters = $postFilters;
    }

    /**
     * @deprecated Use self::match()
     *
     * @return EnlightRequest
     */
    public function route(EnlightRequest $request)
    {
        /* For enlight routing  */
        $this->context->updateFromEnlightRequest($request);

        $params = $this->match($request->getPathInfo(), $this->context);
        if ($params !== false) {
            /* For shopware routing (query === userParams)  */
            $request->setQuery($params);
        }

        /* For enlight routing  */
        $this->context->updateFromEnlightRequest($request);
        $this->context->setParams([]);

        return $request;
    }

    /**
     * Sets a global parameter.
     *
     * @see \Shopware_Controllers_Backend_Newsletter::initMailing
     * @see \Enlight_Controller_Router::setGlobalParam
     * @deprecated Use the context
     *
     * @param string $name
     * @param string $value
     *
     * @return self
     */
    public function setGlobalParam($name, $value)
    {
        $this->context->setGlobalParam($name, $value);

        return $this;
    }
}
