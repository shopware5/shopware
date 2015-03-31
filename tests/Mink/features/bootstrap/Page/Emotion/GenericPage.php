<?php
namespace Page\Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class GenericPage extends Page implements \HelperSelectorInterface
{
    public $cssLocator = array(
        'canonical' => 'link[rel=canonical]',
        'next' => 'link[rel=next]',
        'prev' => 'link[rel=prev]',
        'robots' => 'meta[name=robots]'
    );

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'canonical' => 'link[rel=canonical]',
            'next' => 'link[rel=next]',
            'prev' => 'link[rel=prev]',
            'robots' => 'meta[name=robots]'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array();
    }

    /**
     * Checks if the canonical link matches the given path and query
     * Fails validation if the matches are not exact for either argument
     * If null arguments are provided, no canonical link is expected, and validation
     * will fail if one is found
     *
     * @param $path
     * @param $query
     */
    public function checkCanonical($path = null, $query = array())
    {
        $locator = 'canonical';

        $this->checkLink($locator, $path, $query);
    }

    /**
     * Checks if the next page link matches the given path and query
     * Fails validation if the matches are not exact for either argument
     * If null arguments are provided, no next page link is expected, and validation
     * will fail if one is found
     *
     * @param $path
     * @param $query
     */
    public function checkPaginationNext($path = null, $query = array())
    {
        $locator = 'next';

        $this->checkLink($locator, $path, $query);
    }

    /**
     * Checks if the prev page link matches the given path and query
     * Fails validation if the matches are not exact for either argument
     * If null arguments are provided, no next page link is expected, and validation
     * will fail if one is found
     *
     * @param $path
     * @param $query
     */
    public function checkPaginationPrev($path = null, $query = array())
    {
        $locator = 'prev';

        $this->checkLink($locator, $path, $query);
    }

    /**
     * Helper method to check canonical/next/prev links
     *
     * @param locator
     * @param $path
     * @param $query
     */
    private function checkLink($locator, $path = null, $query = array())
    {
        $elements = \Helper::findElements($this, array($locator), false);
        $linkElement = $elements[$locator];

        if ($path !== null && empty($linkElement)) {
            \Helper::throwException(array("Link expected but not found while looking for " . $locator));
        } elseif ($path === null && !empty($linkElement)) {
            \Helper::throwException(array("Link not expected but found while looking for " . $locator));
        } elseif ($path === null && empty($linkElement)) {
            return;
        }

        $link = $linkElement->getAttribute('href');
        $linkParts = parse_url($link);

        $expectedUrl = rtrim($this->getParameter('base_url'), '/') . '/' . rtrim($path, '/'). '/';
        if (!empty($query)) {
            $expectedUrl .= '?' . http_build_query($query);
        }

        $expectedUrlParts = parse_url($expectedUrl);

        if ($linkParts != $expectedUrlParts) {
            $message = sprintf(
                'Link "%s" does not match expected value "%s" while looking for ' . $locator,
                $link,
                $expectedUrl
            );

            \Helper::throwException(array($message));
        }
    }

    /**
     * Checks if the robots meta exists and matches the expected content
     *
     * @param $content
     */
    public function checkRobots($content = array())
    {
        $locator = array('robots');

        $elements = \Helper::findElements($this, $locator);
        $robotsElement = $elements['robots'];
        $robotsValue = $robotsElement->getAttribute('content');
        $robotsParts = explode(',', $robotsValue);

        $robotsParts = array_map('trim', $robotsParts);

        if (empty($robotsParts)) {
            \Helper::throwException(array('Missing robots data'));
        }

        if ($robotsParts != $content) {
            $message = sprintf(
                'Canonical link "%s" does not match expected value "%s"',
                implode(', ', $robotsParts),
                implode(', ', $content)
            );

            \Helper::throwException(array($message));
        }
    }
}
