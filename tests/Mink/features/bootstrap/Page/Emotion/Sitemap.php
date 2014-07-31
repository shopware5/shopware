<?php
namespace Emotion;

use Behat\Mink\Element\NodeElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Behat\Context\Step;

class Sitemap extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/sitemap{xml}';

    public $cssLocator = array(
        'sitemapGroups' => 'div#center.sitemap > div:not(.clear)',
        'sitemapNodes' => 'div > ul > ul > li',
        'sitemapSubNodes' => 'li > ul > li',
        'nodeLink' => 'li > a',
        'navigationNodes' => 'div#left > ul.categories.level0 > li'
    );

    protected $specialGroupsOrder = array('customPages', 'supplierPages', 'landingPages');

    /**
     * Compares the category tree (left navigation) with the sitemap
     * @throws \Behat\Mink\Exception\ResponseTextException
     */
    public function checkCategories()
    {
        $categories = $this->getSitemapLinks();
        $navigation = $this->getNavigationLinks();

        $result = \Helper::compareArrays($navigation, $categories);

        if ($result !== true) {
            switch ($result['error']) {
                case 'keyNotExists':
                    $message = sprintf('The category "%s" was not found in the sitemap', $result['value']['name']);
                    break;

                case 'comparisonFailed':
                    $message = sprintf(
                        'The category "%s" is different in navigation and sitemap ("%s")',
                        $result['value'],
                        $result['value2']
                    );
                    break;

                default:
                    $message = 'An error occurred';
                    break;
            }

            throw new ResponseTextException($message, $this->getSession());
        }
    }

    /**
     * Helper function to read all categories from sitemap
     * @return array
     */
    private function getSitemapLinks()
    {
        $this->open();

        $categoryGroups = $this->getSitemapGroups();

        $links = array();

        foreach ($categoryGroups as $categoryGroup) {
            //Read a-Tag for name and link
            $locators = array('nodeLink');
            $elements = \Helper::findElements($categoryGroup, $locators, $this->cssLocator);

            $links[] = array(
                'name' => $elements['nodeLink']->getText(),
                'link' => $elements['nodeLink']->getAttribute('href'),
                'children' => $this->readSitemapGroup($categoryGroup)
            );
        }

        return $links;
    }

    /**
     * Helper function to read all categories from left navigation
     * @return array
     */
    private function getNavigationLinks()
    {
        $this->open();

        $locators = array('navigationNodes');
        $elements = \Helper::findElements($this, $locators, null, true);

        $navigation = array();

        foreach ($elements['navigationNodes'] as $navigationNode) {
            $navigation[] = $this->readNavigationNode($navigationNode);
        }

        return $navigation;
    }

    /**
     * Recursive helper function to read all category children from left navigation (by clicking through the category tree)
     * @param  NodeElement $node
     * @return array
     */
    private function readNavigationNode($node)
    {
        $locators = array('nodeLink');
        $elements = \Helper::findElements($node, $locators, $this->cssLocator);

        $navigationNode = array(
            'name' => $elements['nodeLink']->getText(),
            'link' => $elements['nodeLink']->getAttribute('href'),
            'children' => array()
        );

        $nodeLink = $elements['nodeLink'];
        $nodeLink->click();

        $locators = array('sitemapSubNodes');
        $elements = \Helper::findElements($node, $locators, $this->cssLocator, true, false);

        if (!isset($elements['sitemapSubNodes'])) {
            return $navigationNode;
        }

        foreach ($elements['sitemapSubNodes'] as $navigationSubNode) {
            $navigationNode['children'][] = $this->readNavigationNode($navigationSubNode);

            if ($navigationSubNode !== end($elements['sitemapSubNodes'])) {
                $nodeLink->click();
            }
        }

        return $navigationNode;
    }

    /**
     * Compares the category tree (left navigation) with the sitemap.xml
     * @throws \Behat\Mink\Exception\ResponseTextException
     */
    public function checkXmlCategories()
    {
        $categories = array();
        $xmlArray = array();
        $check = array();

        $navigation = $this->getNavigationLinks();

        foreach ($navigation as $category) {
            $categories = array_merge($categories, $this->getCategoryLinks($category));
        }

        $this->open(array('xml' => '.xml'));

        $parser = xml_parser_create();
        xml_parse_into_struct($parser, $this->getContent(), $xmlArray);

        $i = 0;
        foreach ($xmlArray as $xml) {
            if ($xml['tag'] !== 'LOC') {
                continue;
            }

            $check[] = array($xml['value'], $categories[$i]);

            if (end($categories) === $categories[$i]) {
                break;
            }

            $i++;
        }

        $result = \Helper::checkArray($check);

        if ($result !== true) {
            $message = sprintf(
                'The category "%s" has a different link in navigation (%s)',
                $check[$result][0],
                $check[$result][1]
            );
            throw new ResponseTextException($message, $this->getSession());
        }
    }

    /**
     * Recursive helper function to add all category children to first level of an array
     * @param  array $category
     * @return array
     */
    private function getCategoryLinks($category)
    {
        $categories = array($category['link']);

        foreach ($category['children'] as $child) {
            $categories = array_merge($categories, $this->getCategoryLinks($child));
        }

        return $categories;
    }

    /**
     * Compares the custom pages tree (left navigation) with the sitemap
     */
    public function checkCustomPages()
    {
        $customPagesGroup = $this->getSitemapGroups('customPages');

        $links = $this->readSitemapGroup($customPagesGroup);
    }

    /**
     * Compares the supplier pages list with the sitemap
     */
    public function checkSupplierPages()
    {
        $supplierPagesGroup = $this->getSitemapGroups('supplierPages');

        $links = $this->readSitemapGroup($supplierPagesGroup);
    }

    /**
     * Compares all landing pages found in categories with the sitemap
     */
    public function checkLandingPages()
    {
        $landingPagesGroup = $this->getSitemapGroups('landingPages');

        $links = $this->readSitemapGroup($landingPagesGroup);
    }

    /**
     * Reads the complete sitemap tree of the group
     * @param  NodeElement $group
     * @return array
     */
    private function readSitemapGroup($group)
    {
        $locators = array('sitemapNodes');
        $elements = \Helper::findElements($group, $locators, $this->cssLocator, true, false);

        $links = array();

        if (empty($elements['sitemapNodes'])) {
            return $links;
        }

        foreach ($elements['sitemapNodes'] as $node) {
            $links[] = $this->getNodeData($node);
        }

        return $links;
    }

    /**
     * Recursive helper function to read all Node data
     * @param  NodeElement $node
     * @return array
     */
    private function getNodeData($node)
    {
        //Read a-Tag for name and link
        $locators = array('nodeLink');
        $elements = \Helper::findElements($node, $locators, $this->cssLocator);

        $data = array(
            'name' => $elements['nodeLink']->getText(),
            'link' => $elements['nodeLink']->getAttribute('href'),
            'children' => array()
        );

        //Look for deeper ul-Tags and read them recursively
        $locators = array('sitemapSubNodes');
        $elements = \Helper::findElements($node, $locators, $this->cssLocator, true, false);

        if (empty($elements['sitemapSubNodes'])) {
            return $data;
        }

        foreach ($elements['sitemapSubNodes'] as $subNode) {
            $data['children'][] = $this->getNodeData($subNode);
        }

        return $data;
    }

    /**
     * Helper function to get all sitemap groups of a type (categories, custom pages, supplier pages, landing pages)
     * @param  string            $groupName
     * @return array|NodeElement
     */
    private function getSitemapGroups($groupName = 'categories')
    {
        $this->open();

        $locators = array('sitemapGroups');
        $elements = \Helper::findElements($this, $locators, null, true);

        if (in_array($groupName, $this->specialGroupsOrder)) {
            $groups = array_reverse($elements['sitemapGroups']);
            $specialGroupsOrder = array_reverse($this->specialGroupsOrder);
            $specialGroupsOrder = array_flip($specialGroupsOrder);
            $groupId = $specialGroupsOrder[$groupName];

            return $groups[$groupId];
        }

        $groups = $elements['sitemapGroups'];
        foreach ($this->specialGroupsOrder as $specialGroup) {
            array_pop($groups);
        }

        return $groups;
    }

}
