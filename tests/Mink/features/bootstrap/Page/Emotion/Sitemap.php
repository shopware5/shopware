<?php
namespace Page\Emotion;

use Element\Emotion\SitemapGroup;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Sitemap extends Page implements \HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/sitemap{xml}';

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array();
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
     * @param SitemapGroup|string $group
     * @param string $link
     * @param array $links
     */
    public function checkGroup($group, $link, array $sites)
    {
        if(!($group instanceof SitemapGroup)) {
            $message = sprintf('Sitemap group "%s" was not found!', $group);
            \Helper::throwException($message);
        }

        $data = \Helper::getElementData($group, false);

        $this->checkGroupTitleLink($group->getText(), $link, $data['titleLink']);

        foreach($sites as $site) {
            $level = 1;

            if(isset($site['level'])) {
                $level = $site['level'];
            }

            $this->checkGroupSite($site['value'], $site['link'], $data['level' . $level]);
        }
    }

    /**
     * @param string $title
     * @param string $link
     * @param array $data
     * @throws \Exception
     */
    private function checkGroupTitleLink($title, $link, array $data)
    {
        $check = array(
            'title' => array($data['title'], $title),
            'link'  => array($data['link'],  $link),
        );

        $result = \Helper::checkArray($check);

        if($result === true) {
            return;
        }

        if ($result === 'title') {
            $message = sprintf('Title of "%s" has a different value! (is "%s")', $check['title'][1], $check['title'][0]);
        } elseif (empty($link)) {
            $message = array(
                sprintf('There is a link for the group "%s"!', $title),
                $check['link'][0]
            );
        } else {
            $message = sprintf('The link of "%s" is different! ("%s" not found in "%s")', $title, $check['link'][1], $check['link'][0]);
        }

        \Helper::throwException($message);
    }

    /**
     * @param string $title
     * @param string $link
     * @param array $data
     * @throws \Exception
     */
    private function checkGroupSite($title, $link, array $data)
    {
        foreach($data as $site) {
            $check = array(
                array($site['value'], $title),
                array($site['title'], $title),
                array($site['link'],  $link)
            );

            $result = \Helper::checkArray($check);

            if($result === true) {
                return;
            }
        }

        $message = sprintf('The site "%s" with link "%s" was not found!', $title, $link);
        \Helper::throwException($message);
    }

    /**
     * @param array $links
     */
    public function checkXml(array $links)
    {
        $homepageUrl = rtrim($this->getParameter('base_url'), '/');
        $xml = new \SimpleXMLElement($this->getContent());

        $check = array();
        $i = 0;

        foreach($xml as $link) {
            if(empty($links[$i])) {
                $messages = array(
                    'There are more links in the sitemap.xml as expected!',
                    sprintf('(%d sites in sitemap.xml, %d in test data', count($xml), count($links))
                );

                \Helper::throwException($messages);
            }

            $check[] = array((string) $link->loc, $homepageUrl . $links[$i]['link']);
            $i++;
        }

        $result = \Helper::checkArray($check, true);

        if($result === true) {
            return;
        }

        $messages = array(
            'A link is different!',
            'Read: ' . $check[$result][0],
            'Expected: ' . $check[$result][1]
        );

        \Helper::throwException($messages);
    }
}
