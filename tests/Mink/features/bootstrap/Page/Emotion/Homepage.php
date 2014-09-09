<?php

namespace Emotion;

use Behat\Mink\Element\Element;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Element\TraversableElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page, Behat\Mink\Exception\ResponseTextException,
    Behat\Behat\Context\Step;

class Homepage extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/';

    public $cssLocator = array(
        'contentBlock' => 'div#content > div.inner',
        'searchForm' => 'div#searchcontainer form',
        'newsletterForm' => 'div.footer_column.col4 > form',
        'newsletterFormSubmit' => 'div.footer_column.col4 > form input[type="submit"]',
        'emotionElement' => 'div.emotion-element > div.%s-element',
        'emotionSliderElement' => 'div.emotion-element > div.%s-slider-element',
        'bannerImage' => 'div.mapping img',
        'bannerLink' => 'div.mapping > a',
        'bannerMapping' => 'div.banner-mapping > a',
        'sliderSlide' => 'div.slide',
        'slideImage' => 'img',
        'slideLink' => 'a',
        'slideSupplier' => 'div.supplier',
        'slideArticle' => 'div.outer-article-box > div.article_box',
        'slideArticleImageLink' => 'a.article-thumb-wrapper',
        'slideArticleTitleLink' => 'a.title',
        'slideArticlePrice' => 'p.price',
        'blogEntry' => 'div.blog-entry > div.blog-entry-inner',
        'blogEntryImage' => 'div.blog_img > a',
        'blogEntryTitle' => 'h2 > a',
        'blogEntryText' => 'p',
        'youtubeVideo' => 'iframe',
        'categoryTeaserImage' => 'div.teaser_img',
        'categoryTeaserLink' => 'a',
        'categoryTeaserHeader' => 'h3',
        'articleImage' => 'a.artbox_thumb',
        'articleTitle' => 'a.title',
        'articleDescription' => 'p.desc',
        'articlePrice' => 'p.price',
        'articleMore' => 'a.more',
        'controller' => array(
            'account' => 'body.ctl_account',
            'checkout' => 'body.ctl_checkout',
            'newsletter' => 'body.ctl_newsletter'
        )
    );

    protected $srcAttribute = 'src';

    /**
     * Searches the given term in the shop
     * @param string $searchTerm
     */
    public function searchFor($searchTerm)
    {
        $this->getElement('SearchForm')->submit($searchTerm);
        $this->verifyResponse();
    }

    /**
     * Search the given term using live search
     * @param $searchTerm
     */
    public function receiveSearchResultsFor($searchTerm)
    {
        $this->getElement('SearchForm')->receiveSearchResultsFor($searchTerm);
    }

    /**
     * Checks an emotion banner element
     * @param  string                                      $image
     * @param  mixed                                       $links
     * @throws \Behat\Mink\Exception\ResponseTextException
     */
    public function checkBanner($image, $links = null)
    {
        $testBanner = array(
            'image' => $image,
            'links' => $links
        );

        $banners = $this->findAllEmotionParentElements('banner');

        /** @var NodeElement $banner */
        foreach ($banners as $banner) {
            $locators = array('bannerImage');

            if (is_string($links)) {
                $locators[] = 'bannerLink';
            }

            $elements = \Helper::findElements($banner, $locators, $this->cssLocator, false, false);

            $image = $elements['bannerImage']->getAttribute($this->srcAttribute);
            $mapping = null;

            if (!empty($elements['bannerLink'])) {
                $mapping = $elements['bannerLink']->getAttribute('href');
            } elseif (is_array($links)) {
                $locators = array('bannerMapping');
                $elements = \Helper::findElements($banner, $locators, $this->cssLocator, true, false);

                if (isset($elements['bannerMapping'])) {
                    foreach ($elements['bannerMapping'] as $link) {
                        $mapping[] = array('mapping' => $link->getAttribute('href'));
                    }
                }
            }

            $readBanner = array(
                'image' => $image,
                'links' => $mapping
            );

            $result = \Helper::compareArrays($readBanner, $testBanner);

            if ($result === true) {
                return;
            }
        }

        $message = sprintf('The given banner was not found!');
        throw new ResponseTextException($message, $this->getSession());
    }

    /**
     * Checks an emotion blog element
     * @param  array                                       $articles
     * @throws \Behat\Mink\Exception\ResponseTextException
     */
    public function checkBlogArticles($articles)
    {
        $return = array();
        $blogs = $this->findAllEmotionParentElements('blog');

        foreach ($blogs as $blogKey => $blog) {
            $locators = array('blogEntry');
            $elements = \Helper::findElements($blog, $locators, $this->cssLocator, true);

            $entries = $elements['blogEntry'];

            foreach ($entries as $entry) {
                $locators = array('blogEntryImage', 'blogEntryTitle', 'blogEntryText');
                $elements = \Helper::findElements($entry, $locators, $this->cssLocator);

                $return[$blogKey][] = $elements;
            }
        }

        foreach ($articles as $article) {
            $found = false;

            foreach ($blogs as $blogKey => $blog) {
                foreach ($return[$blogKey] as $itemKey => $item) {
                    $check = array(
                        array($item['blogEntryImage']->getAttribute('title'), $article['title']),
                        array($item['blogEntryImage']->getAttribute('style'), $article['image']),
                        array($item['blogEntryImage']->getAttribute('href'), $article['link']),
                        array($item['blogEntryTitle']->getAttribute('title'), $article['title']),
                        array($item['blogEntryTitle']->getAttribute('href'), $article['link']),
                        array($item['blogEntryTitle']->getText(), $article['title']),
                        array($item['blogEntryText']->getText(), $article['text'])
                    );

                    $result = \Helper::checkArray($check);
                    if ($result === true) {
                        $found = true;
                        unset($return[$blogKey][$itemKey]);
                        break;
                    }
                }

                if ($found) {
                    break;
                }

                if ($blog == end($blogs)) {
                    $message = sprintf(
                        'The blog article "%s" with its given properties was not found!',
                        $article['title']
                    );
                    throw new ResponseTextException($message, $this->getSession());
                }
            }
        }
    }

    /**
     * Checks an emotion Youtube element
     * @param  string                                      $code
     * @throws \Behat\Mink\Exception\ResponseTextException
     */
    public function checkYoutubeVideo($code)
    {
        $videos = $this->findAllEmotionParentElements('youtube');

        foreach ($videos as $video) {
            $locators = array('youtubeVideo');
            $elements = \Helper::findElements($video, $locators, $this->cssLocator);

            $source = $elements['youtubeVideo']->getAttribute('src');

            if (strpos($source, $code) !== false) {
                return;
            }
        }

        $message = sprintf('The YouTube-Video "%s" was not found!', $code);
        throw new ResponseTextException($message, $this->getSession());
    }

    /**
     * Checks an emotion slider element
     * @param  string                                      $type
     * @param  array                                       $slides
     * @throws \Behat\Mink\Exception\ResponseTextException
     */
    public function checkSlider($type, $slides)
    {
        $sliders = $this->findAllEmotionSlider($type);

        $check = array();

        foreach ($slides as $slide) {
            $found = false;

            foreach ($sliders as $sliderKey => $slider) {
                foreach ($sliders[$sliderKey] as $itemKey => $item) {
                    switch ($type) {
                        case 'banner':
                            $check = array(
                                array($item['slideImage']->getAttribute('src'), $slide['image'])
                            );
                            if (!empty($slide['title'])) {
                                $check[] = array($item['slideImage']->getAttribute('title'), $slide['title']);
                            }
                            if (!empty($slide['alt'])) {
                                $check[] = array($item['slideImage']->getAttribute('alt'), $slide['alt']);
                            }
                            if (!empty($slide['link'])) {
                                $check[] = array($item['slideLink']->getAttribute('href'), $slide['link']);
                            }
                            break;

                        case 'manufacturer':
                            $check = array(
                                array($item['slideLink']->getAttribute('href'), $slide['link']),
                                array($item['slideLink']->getAttribute('title'), $slide['name']),
                                array($item['slideImage']->getAttribute('src'), $slide['image']),
                                array($item['slideImage']->getAttribute('alt'), $slide['name'])
                            );
                            break;

                        case 'article':
                            $check = array(
                                array($item['slideArticleImageLink']->getAttribute('href'), $slide['link']),
                                array($item['slideArticleImageLink']->getAttribute('title'), $slide['name']),
                                array($item['slideImage']->getAttribute('src'), $slide['image']),
                                array($item['slideImage']->getAttribute('title'), $slide['name']),
                                array($item['slideArticleTitleLink']->getAttribute('href'), $slide['link']),
                                array($item['slideArticleTitleLink']->getAttribute('title'), $slide['name']),
                                array($item['slideArticleTitleLink']->getText(), $slide['name']),
                                \Helper::toFloat(array($item['slideArticlePrice']->getText(), $slide['price']))
                            );
                            break;
                    }

                    $result = \Helper::checkArray($check);
                    if ($result === true) {
                        $found = true;
                        unset($sliders[$sliderKey][$itemKey]);
                        break;
                    }
                }

                if ($found) {
                    break;
                }

                if ($slider == end($sliders)) {
                    if ($type = 'banner') {
                        $message = sprintf('The image %s was not found in a slider', $slide['image']);
                    } else {
                        $message = sprintf('The slide "%s" with its given properties was not found!', $slide['name']);
                    }
                    throw new ResponseTextException($message, $this->getSession());
                }
            }
        }
    }

    /**
     * Checks an emotion category teaser element
     * @param  string                                      $title
     * @param  string                                      $image
     * @param  string                                      $link
     * @throws \Behat\Mink\Exception\ResponseTextException
     */
    public function checkCategoryTeaser($title, $image, $link)
    {
        $teasers = $this->findAllEmotionParentElements('category-teaser');

        foreach ($teasers as $teaser) {
            $locators = array('categoryTeaserImage', 'categoryTeaserLink', 'categoryTeaserHeader');
            $elements = \Helper::findElements($teaser, $locators, $this->cssLocator);

            $check = array(
                array($elements['categoryTeaserLink']->getAttribute('href'), $link),
                array($elements['categoryTeaserLink']->getAttribute('title'), $title),
                array($elements['categoryTeaserImage']->getAttribute('style'), $image),
                array($elements['categoryTeaserHeader']->getText(), $title)
            );

            $result = \Helper::checkArray($check);
            if ($result === true) {
                break;
            }

            if ($teaser == end($teasers)) {
                $message = sprintf('The category teaser "%s" with its given properties was not found!', $title);
                throw new ResponseTextException($message, $this->getSession());
            }
        }
    }

    /**
     * Checks an emotion article element
     * @param  array                                       $data
     * @throws \Behat\Mink\Exception\ResponseTextException
     */
    public function checkArticle($data)
    {
        $articles = $this->findAllEmotionParentElements('article');

        $title = '';

        foreach ($articles as $article) {
            $locators = array('articleImage', 'articleTitle', 'articleDescription', 'articlePrice', 'articleMore');
            $elements = \Helper::findElements($article, $locators, $this->cssLocator);

            $check = array();

            foreach ($data as $row) {
                switch ($row['property']) {
                    case 'image':
                        $check[] = array($elements['articleImage']->getAttribute('style'), $row['value']);
                        break;

                    case 'title':
                        $check[] = array($elements['articleImage']->getAttribute('title'), $row['value']);
                        $check[] = array($elements['articleTitle']->getAttribute('title'), $row['value']);
                        $check[] = array($elements['articleTitle']->getText(), $row['value']);
                        $check[] = array($elements['articleMore']->getAttribute('title'), $row['value']);
                        $title = $row['value'];
                        break;

                    case 'text':
                        $check[] = array($elements['articleDescription']->getText(), $row['value']);
                        break;

                    case 'price':
                        $check[] = \Helper::toFloat(array($elements['articlePrice']->getText(), $row['value']));
                        break;

                    case 'link':
                        $check[] = array($elements['articleImage']->getAttribute('href'), $row['value']);
                        $check[] = array($elements['articleTitle']->getAttribute('href'), $row['value']);
                        $check[] = array($elements['articleMore']->getAttribute('href'), $row['value']);
                        break;
                }
            }

            $result = \Helper::checkArray($check);
            if ($result === true) {
                break;
            }

            if ($article == end($articles)) {
                $message = sprintf('The article "%s" with its given properties was not found!', $title);
                throw new ResponseTextException($message, $this->getSession());
            }
        }
    }

    /**
     * Helper function to find all emotion slider and their important tags
     * @param  string $type
     * @return array
     */
    private function findAllEmotionSlider($type)
    {
        $sliders = $this->findAllEmotionParentElements($type, 'emotionSliderElement');

        $return = array();

        foreach ($sliders as $sliderKey => $slider) {
            switch ($type) {
                case 'banner':
                    $return[$sliderKey] = $this->findAllEmotionBannerSliderElements($slider);
                    break;

                case 'manufacturer':
                    $return[$sliderKey] = $this->findAllEmotionManufacturerSliderElements($slider);
                    break;

                case 'article':
                    $return[$sliderKey] = $this->findAllEmotionArticleSliderElements($slider);
                    break;
            }
        }

        return $return;
    }

    /**
     * Helper function to get all important tags of a banner slider
     * @param  string $cssClass
     * @return array
     */
    private function findAllEmotionBannerSliderElements(NodeElement $slider)
    {
        $return = array();

        $locators = array('sliderSlide');
        $elements = \Helper::findElements($slider, $locators, $this->cssLocator, true);

        $slides = $elements['sliderSlide'];

        foreach ($slides as $slide) {
            $locators = array('slideImage', 'slideLink');
            $elements = \Helper::findElements($slide, $locators, $this->cssLocator, false, false);

            $return[] = $elements;
        }

        return $return;
    }

    /**
     * Helper function to get all important tags of a manufacturer slider
     * @param  string $cssClass
     * @return array
     */
    private function findAllEmotionManufacturerSliderElements(NodeElement $slider)
    {
        $return = array();

        $locators = array('sliderSlide');
        $elements = \Helper::findElements($slider, $locators, $this->cssLocator, true);

        $slides = $elements['sliderSlide'];

        foreach ($slides as $slide) {
            $locators = array('slideSupplier');
            $elements = \Helper::findElements($slide, $locators, $this->cssLocator, true);

            $suppliers = $elements['slideSupplier'];

            foreach ($suppliers as $supplier) {
                $locators = array('slideImage', 'slideLink');
                $elements = \Helper::findElements($supplier, $locators, $this->cssLocator, false);

                $return[] = $elements;
            }
        }

        return $return;
    }

    /**
     * Helper function to get all important tags of an article slider
     * @param  string $cssClass
     * @return array
     */
    private function findAllEmotionArticleSliderElements(NodeElement $slider)
    {
        $return = array();

        $locators = array('sliderSlide');
        $elements = \Helper::findElements($slider, $locators, $this->cssLocator, true);

        $slides = $elements['sliderSlide'];

        foreach ($slides as $slide) {
            $locators = array('slideArticle');
            $elements = \Helper::findElements($slide, $locators, $this->cssLocator, true);

            $articles = $elements['slideArticle'];

            foreach ($articles as $article) {
                $locators = array('slideImage', 'slideArticleImageLink', 'slideArticleTitleLink', 'slideArticlePrice');
                $elements = \Helper::findElements($article, $locators, $this->cssLocator, false);

                $return[] = $elements;
            }
        }

        return $return;
    }

    /**
     * Helper function to find all emotion parents elements of one type
     * @param  string $type
     * @return array
     */
    private function findAllEmotionParentElements($type, $locator = 'emotionElement')
    {
        $locators = array($locator => $type);
        $elements = \Helper::findElements($this, $locators, null, true);

        return $elements[$locator];
    }

    /**
     * Compares the comparison list with the given list of articles
     * @param  array                                       $articles
     * @throws \Behat\Mink\Exception\ResponseTextException
     */
    public function checkComparison($articles)
    {
        $result = \Helper::countElements($this, 'div.compare_article', count($articles));

        if ($result !== true) {
            $message = sprintf('There are %d articles in the comparison (should be %d)', $result, count($articles));
            \Helper::throwException(array($message));
        }

        $articlesInComparison = $this->findAll('css', 'div.compare_article');

        foreach ($articles as $articleKey => $article) {
            foreach ($articlesInComparison as $articleInComparisonKey => $articleInComparison) {

                $locator = sprintf('div.compare_article:nth-of-type(%d) ', $articleInComparisonKey + 2);

                $elements = array(
                    'a-picture' => $this->find('css', $locator . 'div.picture a'),
                    'img' => $this->find('css', $locator . 'div.picture img'),
                    'h3-a-name' => $this->find('css', $locator . 'div.name h3 a'),
                    'a-name' => $this->find('css', $locator . 'div.name a.button-right'),
                    'div-votes' => $this->find('css', $locator . 'div.votes div.star'),
                    'p-desc' => $this->find('css', $locator . 'div.desc'),
                    'strong-price' => $this->find('css', $locator . 'div.price strong')
                );

                $check = array();

                if (!empty($article['image'])) {
                    $check[] = array($elements['img']->getAttribute('src'), $article['image']);
                }

                if (!empty($article['name'])) {
                    $check[] = array($elements['a-picture']->getAttribute('title'), $article['name']);
                    $check[] = array($elements['img']->getAttribute('alt'), $article['name']);
                    $check[] = array($elements['h3-a-name']->getAttribute('title'), $article['name']);
                    $check[] = array($elements['h3-a-name']->getText(), $article['name']);
                    $check[] = array($elements['a-name']->getAttribute('title'), $article['name']);
                }

                if (!empty($article['ranking'])) {
                    $check[] = array($elements['div-votes']->getAttribute('class'), $article['ranking']);
                }

                if (!empty($article['text'])) {
                    $check[] = array($elements['p-desc']->getText(), $article['text']);
                }

                if (!empty($article['price'])) {
                    $check[] = \Helper::toFloat(array($elements['strong-price']->getText(), $article['price']));
                }

                if (!empty($article['link'])) {
                    $check[] = array($elements['a-picture']->getAttribute('href'), $article['link']);
                    $check[] = array($elements['h3-a-name']->getAttribute('href'), $article['link']);
                    $check[] = array($elements['a-name']->getAttribute('href'), $article['link']);
                }

                $result = \Helper::checkArray($check);

                if ($result === true) {
                    unset($articlesInComparison[$articleInComparisonKey]);
                    break;
                }

                if ($articleInComparison == end($articlesInComparison)) {
                    $message = sprintf(
                        'The article on position %d was not found!',
                        $articleKey + 1
                    );
                    throw new ResponseTextException($message, $this->getSession());
                }
            }
        }
    }

    /**
     * @param array $data
     */
    public function subscribeNewsletter(array $data)
    {
        \Helper::fillForm($this, 'newsletterForm', $data);

        $locators = array('newsletterFormSubmit');
        $elements = \Helper::findElements($this, $locators);
        $elements['newsletterFormSubmit']->press();
    }

    /**
     * Global method to check the count of an MultipleElement
     * @param \MultipleElement $elements
     * @param int              $count
     */
    public function assertElementCount(\MultipleElement $elements, $count = 0)
    {
        if ($count !== count($elements)) {
            $message = sprintf(
                'There are %d elements of type "%s" on page (should be %d)',
                count($elements),
                get_class($elements),
                $count
            );
            \Helper::throwException($message);
        }
    }

    /**
     * Global method to check the content of an Element or Page
     * @param TraversableElement $element
     * @param array              $content
     */
    public function assertElementContent(TraversableElement $element, $content)
    {
        $check = array();

        foreach ($content as $subCheck) {
            $checkMethod = sprintf('get%ssToCheck', ucfirst($subCheck['position']));

            if (!method_exists($element, $checkMethod)) {
                $message = sprintf('%s->%s() does not exist!', get_class($element), $checkMethod);
                \Helper::throwException($message);
            }

            $checkValues = $element->$checkMethod();

            if (!is_array($checkValues) || empty($checkValues)) {
                $message = sprintf('%s->%s() returned no values to check!', get_class($element), $checkMethod);
                \Helper::throwException($message);
            }

            foreach ($checkValues as $key => $checkValue) {
                //Convert the contentValue to a float if checkValue is also one
                if (is_float($checkValue)) {
                    $subCheck['content'] = \Helper::toFloat($subCheck['content']);
                }

                $check[$key] = array($checkValue, $subCheck['content']);
            }
        }

        $result = \Helper::checkArray($check);

        if ($result !== true) {
            $message = sprintf(
                '"%s" not found in "%s" of "%s"! (is "%s")',
                $check[$result][1],
                $result,
                get_class($element),
                $check[$result][0]
            );
            \Helper::throwException($message);
        }
    }

    /**
     * @param string             $formLocatorName
     * @param TraversableElement $element
     * @param array              $values
     */
    public function submitForm($formLocatorName, TraversableElement $element, $values)
    {
        $locators = array(
            'form' => $element->cssLocator[$formLocatorName],
            'formSubmitButton' => $element->cssLocator[$formLocatorName] . ' *[type="submit"]'
        );
        $elements = \Helper::findElements($element, $locators, $locators, false, false);

        if(empty($elements['form'])) {
            $message = sprintf('The form "%s" was not found!', $formLocatorName);
            \Helper::throwException($message);
        }

        $form = $elements['form'];
        $formSubmit = $elements['formSubmitButton'];

        if(empty($formSubmit)) {
            $locators = array(
                'submitButton' => '*[type="submit"]'
            );
            $elements = \Helper::findElements($element, $locators, $locators, true, false);

            $formId = $form->getAttribute('id');

            foreach($elements['submitButton'] as $submit) {
                if($submit->getAttribute('form') === $formId) {
                    $formSubmit = $submit;
                    break;
                }
            }
        }

        if(empty($formSubmit)) {
            $message = sprintf('The form "%s" has no submit button!', $formLocatorName);
            \Helper::throwException($message);
        }

        foreach ($values as $value) {
            $tempFieldName = $fieldName = $value['field'];
            unset($value['field']);

            foreach ($value as $key => $fieldValue) {
                if ($key !== 'value') {
                    $fieldName = sprintf('%s[%s]', $key, $tempFieldName);
                }

                $field = $form->findField($fieldName);

                if (empty($field)) {
                    if (empty($fieldValue)) {
                        continue;
                    }

                    $message = sprintf('The form "%s" has no field "%s"!', $formLocatorName, $fieldName);
                    \Helper::throwException($message);
                }

                $fieldType = $field->getAttribute('type');

                //Select
                if (empty($fieldType)) {
                    $field->selectOption($fieldValue);
                    continue;
                }

                //Checkbox
                if ($fieldType === 'checkbox') {
                    $field->check();
                    continue;
                }

                //Text
                $field->setValue($fieldValue);
            }
        }

        $formSubmit->press();
    }

    /**
     * Returns the called Shopware controller
     * @return string
     */
    public function getController()
    {
        $elements = \Helper::findElements($this, $this->cssLocator['controller'], $this->cssLocator['controller'], false, false);
        $elements = array_filter($elements);
        return key($elements);
    }
}
