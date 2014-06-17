<?php
namespace Responsive;

use Behat\Behat\Context\Step;

class Listing extends \Emotion\Listing
{
    public $cssLocator = array(
        'view' => array(
            'table' => 'a.action--link.link--table-view',
            'list' => 'a.action--link.link--list-view'),
        'active' => '.is--active',
        'filterContainer' => 'div.filter--container',
        'filterCloseLinks' => 'div.filter--group > div > ul > li > a.filter--link.link--close',
        'filterGroups' => 'div > span.filter--header',
        'filterProperties' => 'div.filter--group:nth-of-type(%d) > div > ul > li > a.filter--link',
        'articleBox' => 'li.product--box.panel',
        'articlePrice' => 'li.product--box.panel:nth-of-type(%d) div.product--price',
        'listingBox' => 'div.listing--container'
    );

    protected $viewSwitchCount = 1;
    protected $filterGroupsHasBrackets = false;
    protected $filterPropertyFactor = 1;
}
