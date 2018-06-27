<?php


namespace Shopware\Tests\Mink\Page;


use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Helper;

class SitemapIndexXml extends Page
{
    /**
     * @var string
     */
    protected $path = '/sitemap_index.xml';


    /**
     * @param array $links
     *
     * @throws \Exception
     */
    public function checkXml(array $links)
    {
        $homepageUrl = rtrim($this->getParameter('base_url'), '/');
        $xml = json_decode(json_encode(simplexml_load_string($this->getContent())), true);

        if (!isset($xml['sitemap']['loc'])) {
            Helper::throwException('Sitemap is missing in /sitemap_index.xml');
        }

        $expected = $homepageUrl . '/web/sitemap/' . $links[0]['name'];
        if ($xml['sitemap']['loc'] !== $expected) {
            Helper::throwException(sprintf('Sitemap url does not match excepted, excepted: %s, given %s', $expected, $xml['sitemap']['loc']));
        }
    }
}