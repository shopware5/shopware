<?php

namespace Shopware\Framework\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
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
     * @var string
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
     * @var ShopFinder
     */
    private $shopFinder;

    /**
     * @var SeoUrlReaderInterface
     */
    private $seoUrlReader;

    /**
     * @var LoaderInterface
     */
    private $routingLoader;

    public function __construct(
        $resource,
        \AppKernel $kernel,
        ?RequestContext $context = null,
        LoggerInterface $logger = null,
        SeoUrlReaderInterface $seoUrlReader,
        ShopFinder $shopFinder,
        LoaderInterface $routingLoader
    ) {
        $this->resource = $resource;
        $this->context = $context;
        $this->logger = $logger;

        $this->plugins = $kernel->getPlugins();
        $this->seoUrlReader = $seoUrlReader;
        $this->shopFinder = $shopFinder;
        $this->routingLoader = $routingLoader;
    }

    public function setContext(RequestContext $context): void
    {
        $this->context = $context;
    }

    public function getContext(): ?RequestContext
    {
        return $this->context;
    }

    /**
     * @return RouteCollection
     */
    public function getRouteCollection(): RouteCollection
    {
        if (null === $this->routes) {
            $this->routes = $this->loadRoutes();
        }

        return $this->routes;
    }

    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH): string
    {
        $generator = new UrlGenerator(
            $this->getRouteCollection(),
            $this->getContext(),
            $this->logger
        );

        $url = $generator->generate($name, $parameters, $referenceType);

        if (!$context = $this->getContext()) {
            return $url;
        }

        if (!$shop = $context->getParameter('shop')) {
            return $url;
        }

        $base = $generator->generate('homepage', [], $referenceType);

        $pathInfo = str_replace($base, '', $url);

        $seoUrl = $this->seoUrlReader->fetchSeoUrl($shop['id'], $pathInfo);

        if ($seoUrl) {
            $url = str_replace($pathInfo, $seoUrl, $url);
        }

        $result = rtrim($url, '/');

        return $result;
    }

    public function match($pathinfo)
    {

    }

    public function matchRequest(Request $request): array
    {
        $shop = $this->shopFinder->findShopByRequest($this->context);

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
            $seoUrl = $this->seoUrlReader->fetchUrl($shop['id'], trim($pathInfo, '/'));

            if ($seoUrl) {
                $pathInfo = $seoUrl;
            }

            //rewrite base url for url generator
            $this->context->setBaseUrl(rtrim($shop['base_path'], '/'));
        }

        $pathInfo = '/' . trim($pathInfo, '/');

        $this->context->setPathInfo($pathInfo);

        $matcher = new UrlMatcher($this->getRouteCollection(), $this->getContext());

        return $matcher->match($pathInfo);
    }

    private function loadRoutes(): RouteCollection
    {
        /** @var RouteCollection $routes */
        $routes = $this->routingLoader->load($this->resource);

        foreach ($this->plugins->getPlugins() as $plugin) {
            $file = $plugin->getPath() . '/Resources/config/routing.yml';

            if (!file_exists($file)) {
                continue;
            }

            $routes->addCollection($this->routingLoader->load($file));
        }

        return $routes;
    }
}