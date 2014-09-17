<?php
namespace Page\Responsive;

class Listing extends \Page\Emotion\Listing
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
        'listingBox' => 'div.listing--container'
    );

    protected $viewSwitchCount = 1;
    protected $filterGroupsHasBrackets = false;
    protected $filterPropertyFactor = 1;
}
