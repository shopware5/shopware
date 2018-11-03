<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Mink\Element;

use Behat\Mink\Element\NodeElement;
use Shopware\Tests\Mink\Helper;

/**
 * Element: BlogArticle
 * Location: Emotion element for blog articles
 *
 * Available retrievable properties (per blog article):
 * - image (string, e.g. "beach1503f8532d4648.jpg")
 * - link (string, e.g. "/Campaign/index/emotionId/6")
 * - alt (string, e.g. "foo")
 * - title (string, e.g. "bar")
 */
class BlogArticle extends MultipleElement implements \Shopware\Tests\Mink\HelperSelectorInterface
{
    /**
     * @var array
     */
    protected $selector = ['css' => 'div.emotion--blog'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'article' => '.blog--entry',
            'articleTitle' => '.blog--title',
            'articleLink' => '.blog--image',
            'articleText' => '.blog--description',
        ];
    }

    /**
     * Returns all blog articles of the element
     *
     * @param string[] $properties
     *
     * @return array[]
     */
    public function getArticles(array $properties)
    {
        $elements = Helper::findAllOfElements($this, ['article']);

        $articles = [];

        /** @var NodeElement $article */
        foreach ($elements['article'] as $article) {
            $articleProperties = [];

            foreach ($properties as $property) {
                $method = 'get' . ucfirst($property) . 'Property';
                $articleProperties[$property] = $this->$method($article);
            }

            $articles[] = $articleProperties;
        }

        return $articles;
    }

    /**
     * Returns the title of the blog article
     *
     * @param NodeElement $article
     *
     * @return string
     */
    public function getTitleProperty(NodeElement $article)
    {
        $selectors = Helper::getRequiredSelectors($this, ['articleTitle', 'articleLink']);

        $title = $article->find('css', $selectors['articleTitle']);

        $titles = [
            'titleTitle' => $title->getAttribute('title'),
            'linkTitle' => $article->find('css', $selectors['articleLink'])->getAttribute('title'),
            'title' => rtrim($title->getText(), '.'),
        ];

        return $this->getUniqueTitle($titles);
    }

    /**
     * Returns the image of the blog article
     *
     * @param NodeElement $article
     *
     * @return string|null
     */
    public function getImageProperty(NodeElement $article)
    {
        $selector = Helper::getRequiredSelector($this, 'articleLink');

        return $article->find('css', $selector)->getAttribute('style');
    }

    /**
     * Returns the link to the blog article
     *
     * @param NodeElement $article
     *
     * @return string
     */
    public function getLinkProperty(NodeElement $article)
    {
        $selectors = Helper::getRequiredSelectors($this, ['articleTitle', 'articleLink']);

        $links = [
            'titleLink' => $article->find('css', $selectors['articleTitle'])->getAttribute('href'),
            'link' => $article->find('css', $selectors['articleLink'])->getAttribute('href'),
        ];

        return Helper::getUnique($links);
    }

    /**
     * Returns the text preview of the blog article
     *
     * @param NodeElement $article
     *
     * @return null|string
     */
    public function getTextProperty(NodeElement $article)
    {
        $selector = Helper::getRequiredSelector($this, 'articleText');

        return $article->find('css', $selector)->getText();
    }

    /**
     * Helper method to get the unique title
     *
     * @param string[] $titles
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function getUniqueTitle(array $titles)
    {
        $title = array_unique($titles);

        switch (count($title)) {
            //normal case
            case 1:
                return current($title);

            //if blog article name is too long, it will be cut. So it's different from the other and has to be checked separately
            case 2:
                $check = [$title];
                $result = Helper::checkArray($check);
                break;

            default:
                $result = false;
                break;
        }

        if ($result !== true) {
            $messages = ['The blog article has different titles!'];
            foreach ($title as $key => $value) {
                $messages[] = sprintf('"%s" (Key: "%s")', $value, $key);
            }

            Helper::throwException($messages);
        }

        return $title['titleTitle'];
    }
}
