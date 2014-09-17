<?php
namespace Page\Responsive;

use Behat\Behat\Context\Step;

class Sitemap extends \Page\Emotion\Sitemap
{
    public $cssLocator = array(
        'sitemapGroups' => 'div.sitemap--content div.sitemap--category',
        'sitemapNodes' => 'div > ul > ul > li',
        'sitemapSubNodes' => 'li > ul > li',
        'nodeLink' => 'li > a',
        'navigationNodes' => 'ul.categories--navigation.is--level0 > li'
    );
}
