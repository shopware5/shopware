<?php

use SensioLabs\Behat\PageObjectExtension\PageObject\Page, Behat\Mink\Exception\ResponseTextException,
        Behat\Behat\Context\Step;

class Note extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/note';

    /**
     * @param $position
     */
    public function removeArticle($position)
    {
        $this->open();
        $this->clickButton($position, 'a.delete');
    }

    /**
     * @param $position
     */
    public function buyArticle($position)
    {
        $this->open();
        $this->clickButton($position, 'a.basket');
    }

    /**
     * @param $position
     */
    public function compareArticle($position)
    {
        $this->open();
        $this->clickButton($position, 'a.compare_add_article');
    }

    /**
     * @param $position
     */
    public function visitArticleDetails($position)
    {
        $this->open();
        $this->clickButton($position, 'a.detail');
    }

    /**
     * @param $position
     * @param $class
     * @throws Behat\Mink\Exception\ResponseTextException
     */
    private function clickButton($position, $class)
    {
        $class = sprintf('div.table_row:nth-of-type(%d) %s', $position + 1, $class);

        $button = $this->find('css', $class);

        if (empty($button)) {
            $message = sprintf('Note page has no article on position %d', $position);
            throw new ResponseTextException($message, $this->getSession());
        }

        $button->click();
    }

    /**
     * @param $count
     * @throws Behat\Mink\Exception\ResponseTextException
     */
    public function countArticles($count)
    {
        $this->open();

        $articles = $this->findAll('css', 'div.table_row');

        if (count($articles) != $count) {
            $message = sprintf('There are %d articles on the note (should be %d)', count($articles), $count);
            throw new ResponseTextException($message, $this->getSession());
        }
    }

    /**
     * @param $articles
     * @throws Behat\Mink\Exception\ResponseTextException
     */
    public function checkList($articles)
    {
        $this->open();

        foreach ($articles as $key => $article) {
            $class = sprintf('div.table_row:nth-of-type(%d) ', $key + 2);

            $elements = array(
                    'a-thumb' => $this->find('css', $class . 'a.thumb_image'),
                    'img' => $this->find('css', $class . 'img'),
                    'a-zoom' => $this->find('css', $class . 'a.zoom_picture'),
                    'a-title' => $this->find('css', $class . 'a.title'),
                    'div-supplier' => $this->find('css', $class . 'div.supplier'),
                    'p-number' => $this->find('css', $class . 'p.ordernumber'),
                    'p-desc' => $this->find('css', $class . 'p.desc'),
                    'strong-price' => $this->find('css', $class . 'strong.price'),
                    'a-detail' => $this->find('css', $class . 'a.detail'),
            );

            $check = array();

            if (!empty($article['name'])) {
                $check[] = array($elements['a-thumb']->getAttribute('title'), $article['name']);
                $check[] = array($elements['img']->getAttribute('alt'), $article['name']);
                $check[] = array($elements['a-title']->getAttribute('title'), $article['name']);
                $check[] = array($elements['a-title']->getText(), $article['name']);
                $check[] = array($elements['a-detail']->getAttribute('title'), $article['name']);
            }

            if (!empty($article['supplier'])) {
                $check[] = array($elements['div-supplier']->getText(), $article['supplier']);
            }

            if (!empty($article['ordernumber'])) {
                $check[] = array($elements['p-number']->getText(), $article['ordernumber']);
            }

            if (!empty($article['text'])) {
                $check[] = array($elements['p-desc']->getText(), $article['text']);
            }

            if (!empty($article['price'])) {
                $check[] = $this->toFloat(array($elements['strong-price']->getText(), $article['price']));
            }

            if (!empty($article['image'])) {
                $check[] = array($elements['img']->getAttribute('src'), $article['image']);
            }

            if (!empty($article['link'])) {
                $check[] = array($elements['a-thumb']->getAttribute('href'), $article['link']);
                $check[] = array($elements['a-title']->getAttribute('href'), $article['link']);
                $check[] = array($elements['a-detail']->getAttribute('href'), $article['link']);
            }

            if (!$this->checkArray($check)) {
                $message = sprintf('The article on position %d is different', $key + 1);
                throw new ResponseTextException($message, $this->getSession());
            }
        }
    }

    /**
     * Helper function to check each row of an array. If each second sub-element of a row is in its first, check is true
     * @param array $check
     * @return bool
     */
    private function checkArray($check)
    {
        foreach ($check as $compare) {
            if ($compare[0] === $compare[1]) {
                continue;
            }

            if (strpos($compare[0], $compare[1]) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Helper function to validate prices to floats
     * @param array $values
     * @return array
     */
    private function toFloat($values)
    {
        foreach ($values as $key => $value) {
            $value = str_replace(array('ab', ' ', '.'), '', $value);
            $value = str_replace(',', '.', $value);

            $values[$key] = floatval($value);
        }

        return $values;
    }
}
