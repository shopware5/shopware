<?php
namespace  Shopware\Tests\Mink\Page;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

class GenericPage extends Page implements HelperSelectorInterface
{
    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'canonical' => 'link[rel=canonical]',
            'next' => 'link[rel=next]',
            'prev' => 'link[rel=prev]',
            'robots' => 'meta[name=robots]'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [];
    }

    /**
     * Checks if the canonical/next/prev links matches the given path and query
     * Fails validation if the matches are not exact for either argument
     * If null arguments are provided, no next page link is expected, and validation
     * will fail if one is found
     *
     * @param locator
     * @param $path
     * @param $query
     */
    public function checkLink($locator, $path = null, $query = [])
    {
        $elements = Helper::findElements($this, [$locator], false);
        $linkElement = $elements[$locator];

        if ($path !== null && empty($linkElement)) {
            Helper::throwException(["Link expected but not found while looking for " . $locator]);
        } elseif ($path === null && !empty($linkElement)) {
            Helper::throwException(["Link not expected but found while looking for " . $locator]);
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

            Helper::throwException([$message]);
        }
    }

    /**
     * Checks if the robots meta exists and matches the expected content
     *
     * @param $content
     */
    public function checkRobots($content = [])
    {
        $elements = Helper::findElements($this, ['robots']);
        $robotsElement = $elements['robots'];
        $robotsValue = $robotsElement->getAttribute('content');
        $robotsParts = explode(',', $robotsValue);

        $robotsParts = array_map('trim', $robotsParts);

        if (empty($robotsParts)) {
            Helper::throwException(['Missing robots data']);
        }

        if ($robotsParts != $content) {
            $message = sprintf(
                'Canonical link "%s" does not match expected value "%s"',
                implode(', ', $robotsParts),
                implode(', ', $content)
            );

            Helper::throwException([$message]);
        }
    }
}
