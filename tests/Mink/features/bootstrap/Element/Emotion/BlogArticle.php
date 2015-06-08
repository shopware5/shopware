<?php

namespace Element\Emotion;

use Behat\Mink\Element\NodeElement;
use Element\MultipleElement;

require_once 'tests/Mink/features/bootstrap/Element/MultipleElement.php';

class BlogArticle extends MultipleElement implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.emotion-element > div.blog-element');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'article' => 'div.blog-entry',
            'articleTitle' => 'h2 > a',
            'articleLink' => 'div.blog_img > a',
            'articleText' => 'p'
        );
    }

    /**
     * @param string[] $properties
     * @return array[]
     */
    public function getArticles(array $properties)
    {
        $elements = \Helper::findAllOfElements($this, ['article']);

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
     * @param NodeElement $article
     * @return array
     */
    public function getTitleProperty(NodeElement $article)
    {
        $selectors = \Helper::getRequiredSelectors($this, ['articleTitle', 'articleLink']);

        $title = $article->find('css', $selectors['articleTitle']);

        $titles = [
            'titleTitle' => $title->getAttribute('title'),
            'linkTitle' => $article->find('css', $selectors['articleLink'])->getAttribute('title'),
            'title' => rtrim($title->getText(), '.')
        ];

        return $this->getUniqueTitle($titles);
    }

    /**
     * @param NodeElement $article
     * @return mixed|null
     */
    public function getImageProperty(NodeElement $article)
    {
        $selector = \Helper::getRequiredSelector($this, 'articleLink');
        return $article->find('css', $selector)->getAttribute('style');
    }

    /**
     * @param NodeElement $article
     * @return string
     */
    public function getLinkProperty(NodeElement $article)
    {
        $selectors = \Helper::getRequiredSelectors($this, ['articleTitle', 'articleLink']);

        $links = [
            'titleLink' => $article->find('css', $selectors['articleTitle'])->getAttribute('href'),
            'link' => $article->find('css', $selectors['articleLink'])->getAttribute('href')
        ];

        return \Helper::getUnique($links);
    }

    /**
     * @param NodeElement $article
     * @return null|string
     */
    public function getTextProperty(NodeElement $article)
    {
        $selector = \Helper::getRequiredSelector($this, 'articleText');
        return $article->find('css', $selector)->getText();
    }

    /**
     * @param array $titles
     * @return string
     * @throws \Exception
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
                $check = array($title);
                $result = \Helper::checkArray($check);
                break;

            default:
                $result = false;
                break;
        }

        if ($result !== true) {
            $messages = array('The blog article has different titles!');
            foreach ($title as $key => $value) {
                $messages[] = sprintf('"%s" (Key: "%s")', $value, $key);
            }

            \Helper::throwException($messages);
        }

        return $title['titleTitle'];
    }
}