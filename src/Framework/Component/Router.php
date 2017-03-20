<?php

namespace Shopware\Framework\Component;

use Shopware\Framework\Component\Plugin\Registry;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Psr\Log\LoggerInterface;

class Router implements RouterInterface, RequestMatcherInterface
{
    /**
     * @var RequestContext
     */
    private $context;

    /**
     * @var RouteCollection
     */
    private $routes;

    /**
     * @var
     */
    private $resource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \PluginCollection
     */
    private $plugins;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(
        $resource,
        \AppKernel $kernel,
        ContainerInterface $container,
        ?RequestContext $context = null,
        LoggerInterface $logger = null
    ) {
        $this->resource = $resource;
        $this->context = $context;
        $this->logger = $logger;
        $this->container = $container;

        $this->plugins = $kernel->getPlugins();
    }

    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return RouteCollection
     */
    public function getRouteCollection()
    {
        if (null === $this->routes) {
            $this->routes = $this->loadRoutes();
        }

        return $this->routes;
    }

    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        $generator = new UrlGenerator(
            $this->getRouteCollection(),
            $this->getContext(),
            $this->logger
        );
        
        $url = $generator->generate($name, $parameters, $referenceType);

        if (!$shop = $this->getContext()->getParameter('shop')) {
            return $url;
        }

        $pathInfo = str_replace($shop['base_path'], '', $url);

        $urls = $this->getSeoUrls($shop['id']);
        $urls = array_flip($urls);

        if (array_key_exists($pathInfo, $urls)) {
            $url = str_replace($pathInfo, $urls[$pathInfo], $url);
        }

        return rtrim($url, '/');
    }

    public function match($pathinfo)
    {

    }

    public function matchRequest(Request $request)
    {
        $shop = $this->findShop($this->context);

        $pathInfo = $this->context->getPathInfo();

        if ($shop['id']) {
            //save detected shop to context for further processes
            $this->context->setParameter('shop', $shop);
            $request->attributes->set('_shop_id', $shop['id']);
            $request->attributes->set('_shop', $shop);

            //set shop locale
            $request->setLocale($shop['locale']);

            $url = implode('', [$request->getBaseUrl(), $request->getPathInfo()]);

            //generate new path info for detected shop
            $pathInfo = preg_replace('#^' . $shop['base_path'] . '#i', '', $url);

            //resolve seo urls to use symfony url matcher for route detection
            $pathInfo = $this->resolveSeoUrl($pathInfo, $shop['id']);

            //rewrite base url for url generator
            $this->context->setBaseUrl(rtrim($shop['base_path'], '/'));
        }

        $pathInfo = '/' . trim($pathInfo, '/');

        $this->context->setPathInfo($pathInfo);

        $matcher = new UrlMatcher($this->getRouteCollection(), $this->getContext());

        return $matcher->match($pathInfo);
    }

    private function resolveSeoUrl(string $pathInfo, int $shopId): string
    {
        $urls = $this->getSeoUrls($shopId);

        if (array_key_exists($pathInfo, $urls)) {
            return $urls[$pathInfo];
        }

        return $pathInfo;
    }

    private function findShop(RequestContext $requestContext): array
    {
        $query = $this->container->get('dbal_connection')->createQueryBuilder();

        $query->select(['shop.*', 'locale.locale']);
        $query->from('s_core_shops', 'shop');
        $query->innerJoin('shop', 's_core_locales', 'locale', 'locale.id=shop.locale_id');

        $shops = $query->execute()->fetchAll();

        array_walk($shops, function (&$shop) {
            $shop['base_path'] = rtrim($shop['base_path'], '/') . '/';
        });

        $url = rtrim($requestContext->getPathInfo(), '/') . '/';
        
        $matching = array_filter($shops, function($shop) use ($url) {
            return strpos($url, $shop['base_path']) === 0;
        });

        $bestMatch = ['id' => null, 'base_path' => null];
        foreach ($matching as $match) {
            if (strlen($match['base_path']) > strlen($bestMatch['base_path'])) {
                $bestMatch = $match;
            }
        }

        return $bestMatch;
    }

    /**
     * @param int $shopId
     * @return string[]
     */
    private function getSeoUrls(int $shopId): array
    {
        $urls = [
            1 => [
                'freizeit' => 'seo/3',
            ],
            2 => [
                'freetime' => 'seo/3'
            ],
            3 => [
                'loisir' => 'seo/3'
            ],
            4 => [
                'freizeit' => 'seo/3'
            ],
            5 => [
                'freizeit' => 'seo/3'
            ],
            6 => [
                'freizeit' => 'seo/3'
            ]
        ];

        return $urls[$shopId];
    }

    /**
     * @return RouteCollection
     */
    private function loadRoutes()
    {
        /** @var RouteCollection $routes */
        $routes = $this->container->get('routing.loader')->load($this->resource);

        foreach ($this->plugins->getPlugins() as $plugin) {
            $file = $plugin->getPath() . '/Resources/config/routing.yml';

            if (!file_exists($file)) continue;

            $routes->addCollection($this->container->get('routing.loader')->load($file));
        }

        return $routes;
    }
}