<?php

use SensioLabs\Behat\PageObjectExtension\PageObject\Page, Behat\Mink\Exception\ResponseTextException,
        Behat\Behat\Context\Step;

class Homepage extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/';

    /**
     * @param string $searchTerm
     * @return array
     */
    public function searchFor($searchTerm)
    {
        $this->fillField('searchfield', $searchTerm);
        $this->pressButton('submit_search_btn');
        $this->verifyResponse();
    }

    /**
     * @param string $image
     * @param mixed $links
     * @throws Behat\Mink\Exception\ResponseTextException
     */
    public function checkBanner($image, $links = null)
    {
        $banners = $this->findAllEmotionParentElements('banner');

        $return = array();

        foreach ($banners as $bannerKey => $banner) {

            $elements = array();

            $cssClass = 'div.' . str_replace(' ', '.', $banner->getAttribute('class'));

            $class = sprintf('div.emotion-element %s ', $cssClass);
            $elements['img'] = $this->find('css', $class . 'div.mapping img');

            if (isset($links)) {
                if (is_array($links)) {
                    $elements['mapping'] = array();
                    $mapping = array();

                    $maps = $this->findAllEmotionElements($cssClass, 'div.banner-mapping a');

                    foreach ($maps as $mapKey => $map) {
                        $class = sprintf(
                                'div.emotion-element %s div.banner-mapping a:nth-of-type(%d) ',
                                $cssClass,
                                $mapKey + 1
                        );

                        $mapping['a'] = $this->find('css', $class);

                        $elements['mapping'][] = $mapping;
                    }
                } else {
                    $elements['a'] = $this->find('css', $class . 'div.mapping a');
                }
            }

            $return[] = $elements;
        }

        foreach ($return as $itemKey => $item) {
            $check = array(
                    array($item['img']->getAttribute('src'), $image)
            );

            if (isset($links)) {
                if (is_array($links)) {
                    foreach ($item['mapping'] as $subKey => $subitem) {
                        $check[] = array($subitem['a']->getAttribute('href'), $links[$subKey]['mapping']);
                    }
                } else {
                    $check[] = array($item['a']->getAttribute('href'), $links);
                }
            }

            if ($this->checkArray($check)) {
                unset($return[$itemKey]);
                return;
            }
        }

        $message = sprintf('The given banner was not found!');
        throw new ResponseTextException($message, $this->getSession());
    }

    /**
     * @param array $articles
     * @throws Behat\Mink\Exception\ResponseTextException
     */
    public function checkBlogArticles($articles)
    {
        $return = array();
        $blogs = $this->findAllEmotionParentElements('blog');

        foreach ($blogs as $blog_key => $blog) {
            $cssClass = 'div.' . str_replace(' ', '.', $blog->getAttribute('class'));

            $entries = $this->findAllEmotionElements($cssClass, 'div.blog-entry');

            foreach ($entries as $entry_key => $entry) {
                $elements = array();

                $class = sprintf('div.emotion-element %s div.blog-entry:nth-of-type(%d) ', $cssClass, $entry_key + 1);
                $elements['a-image'] = $this->find('css', $class . 'div.blog_img a');
                $elements['a-title'] = $this->find('css', $class . 'h2 a');
                $elements['p-text'] = $this->find('css', $class . 'p');

                $return[$blog_key][] = $elements;
            }
        }

        foreach ($articles as $article) {
            $found = false;

            foreach ($blogs as $blog_key => $blog) {
                foreach ($return[$blog_key] as $itemKey => $item) {
                    $check = array(
                            array($item['a-image']->getAttribute('title'), $article['title']),
                            array($item['a-image']->getAttribute('style'), $article['image']),
                            array($item['a-image']->getAttribute('href'), $article['link']),
                            array($item['a-title']->getAttribute('title'), $article['title']),
                            array($item['a-title']->getAttribute('href'), $article['link']),
                            array($item['a-title']->getText(), $article['title']),
                            array($item['p-text']->getText(), $article['text'])
                    );

                    if ($this->checkArray($check)) {
                        $found = true;
                        unset($return[$blog_key][$itemKey]);
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
     * @param string $code
     * @throws Behat\Mink\Exception\ResponseTextException
     */
    public function checkYoutubeVideo($code)
    {
        $videos = $this->findAllEmotionParentElements('youtube');

        foreach ($videos as $video_key => $video) {
            $cssClass = 'div.' . str_replace(' ', '.', $video->getAttribute('class'));

            $class = sprintf('div.emotion-element %s ', $cssClass);
            $source = $this->find('css', $class . 'iframe')->getAttribute('src');

            if (strpos($source, $code) !== false) {
                return;
            }
        }

        $message = sprintf('The YouTube-Video "%s" was not found!', $code);
        throw new ResponseTextException($message, $this->getSession());
    }

    /**
     * @param string $type
     * @param array $slides
     * @throws Behat\Mink\Exception\ResponseTextException
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
                                    array($item['img']->getAttribute('src'), $slide['image'])
                            );
                            if (!empty($slide['title'])) {
                                $check[] = array($item['img']->getAttribute('title'), $slide['title']);
                            }
                            if (!empty($slide['alt'])) {
                                $check[] = array($item['img']->getAttribute('alt'), $slide['alt']);
                            }
                            if (!empty($slide['link'])) {
                                $check[] = array($item['a']->getAttribute('href'), $slide['link']);
                            }
                            break;

                        case 'manufacturer':
                            $check = array(
                                    array($item['a-image']->getAttribute('href'), $slide['link']),
                                    array($item['a-image']->getAttribute('title'), $slide['name']),
                                    array($item['img']->getAttribute('src'), $slide['image']),
                                    array($item['img']->getAttribute('alt'), $slide['name'])
                            );
                            break;

                        case 'article':
                            $check = array(
                                    array($item['a-thumb']->getAttribute('href'), $slide['link']),
                                    array($item['a-thumb']->getAttribute('title'), $slide['name']),
                                    array($item['img']->getAttribute('src'), $slide['image']),
                                    array($item['img']->getAttribute('title'), $slide['name']),
                                    array($item['a-title']->getAttribute('href'), $slide['link']),
                                    array($item['a-title']->getAttribute('title'), $slide['name']),
                                    array($item['a-title']->getText(), $slide['name']),
                                    $this->toFloat(array($item['p-price']->getText(), $slide['price']))
                            );
                            break;
                    }

                    if ($this->checkArray($check)) {
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
                        $message = sprintf('The slide "%s" with its given properties was not!', $slide['name']);
                    }
                    throw new ResponseTextException($message, $this->getSession());
                }
            }
        }
    }

    /**
     * @param string $title
     * @param string $image
     * @param string $link
     * @throws Behat\Mink\Exception\ResponseTextException
     */
    public function checkCategoryTeaser($title, $image, $link)
    {
        $teasers = $this->findAllEmotionParentElements('category-teaser');

        foreach ($teasers as $teaser) {
            $cssClass = 'div.' . str_replace(' ', '.', $teaser->getAttribute('class'));

            $elements = array();

            $class = sprintf('div.emotion-element %s div.teaser_box ', $cssClass);
            $elements['a'] = $this->find('css', $class . 'a');
            $elements['div-image'] = $this->find('css', $class . 'div.teaser_img');
            $elements['h3'] = $this->find('css', $class . 'h3');

            $check = array(
                    array($elements['a']->getAttribute('href'), $link),
                    array($elements['a']->getAttribute('title'), $title),
                    array($elements['div-image']->getAttribute('style'), $image),
                    array($elements['h3']->getText(), $title)
            );

            if ($this->checkArray($check)) {
                break;
            }

            if ($teaser == end($teasers)) {
                $message = sprintf('The category teaser "%s" with its given properties was not found!', $title);
                throw new ResponseTextException($message, $this->getSession());
            }
        }
    }

    /**
     * @param array $data
     * @throws Behat\Mink\Exception\ResponseTextException
     */
    public function checkArticle($data)
    {
        $articles = $this->findAllEmotionParentElements('article');

        $title = '';

        foreach ($articles as $article) {
            $cssClass = 'div.' . str_replace(' ', '.', $article->getAttribute('class'));

            $elements = array();

            $class = sprintf('div.emotion-element %s div.artbox ', $cssClass);
            $elements['a-thumb'] = $this->find('css', $class . 'a.artbox_thumb');
            $elements['a-title'] = $this->find('css', $class . 'a.title');
            $elements['p-text'] = $this->find('css', $class . 'p.desc');
            $elements['p-price'] = $this->find('css', $class . 'p.price');
            $elements['a-more'] = $this->find('css', $class . 'a.more');

            $check = array();

            foreach ($data as $row) {
                switch ($row['property']) {
                    case 'image':
                        $check[] = array($elements['a-thumb']->getAttribute('style'), $row['value']);
                        break;

                    case 'title':
                        $check[] = array($elements['a-thumb']->getAttribute('title'), $row['value']);
                        $check[] = array($elements['a-title']->getAttribute('title'), $row['value']);
                        $check[] = array($elements['a-title']->getText(), $row['value']);
                        $check[] = array($elements['a-more']->getAttribute('title'), $row['value']);
                        $title = $row['value'];
                        break;

                    case 'text':
                        $check[] = array($elements['p-text']->getText(), $row['value']);
                        break;

                    case 'price':
                        $check[] = $this->toFloat(array($elements['p-price']->getText(), $row['value']));
                        break;

                    case 'link':
                        $check[] = array($elements['a-thumb']->getAttribute('href'), $row['value']);
                        $check[] = array($elements['a-title']->getAttribute('href'), $row['value']);
                        $check[] = array($elements['a-more']->getAttribute('href'), $row['value']);
                        break;
                }
            }

            if ($this->checkArray($check)) {
                break;
            }

            if ($article == end($articles)) {
                $message = sprintf('The article "%s" with its given properties was not found!', $title);
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
     * Helper function to find all emotion slider and their important tags
     * @param string $type
     * @return array
     */
    private function findAllEmotionSlider($type)
    {
        $selector = $type . '-slider';

        $sliders = $this->findAllEmotionParentElements($selector);

        $return = array();

        foreach ($sliders as $slider_key => $slider) {
            $cssClass = 'div.' . str_replace(' ', '.', $slider->getAttribute('class'));

            switch ($type) {
                case 'banner':
                    $return[$slider_key] = $this->findAllEmotionBannerSliderElements($cssClass);
                    break;

                case 'manufacturer':
                    $return[$slider_key] = $this->findAllEmotionManufacturerSliderElements($cssClass);
                    break;

                case 'article':
                    $return[$slider_key] = $this->findAllEmotionArticleSliderElements($cssClass);
                    break;
            }
        }

        return $return;
    }

    /**
     * Helper function to get all important tags of a banner slider
     * @param string $cssClass
     * @return array
     */
    private function findAllEmotionBannerSliderElements($cssClass)
    {
        $return = array();

        $class = 'div.slide';
        $slides = $this->findAllEmotionElements($cssClass, $class);

        foreach ($slides as $slide_key => $slide) {
            $elements = array();

            $class = sprintf('div.emotion-element %s div.slide:nth-of-type(%d) ', $cssClass, $slide_key + 1);
            $elements['a'] = $this->find('css', $class . 'a');
            $elements['img'] = $this->find('css', $class . 'img');

            $return[] = $elements;
        }
        return $return;
    }

    /**
     * Helper function to get all important tags of a manufacturer slider
     * @param string $cssClass
     * @return array
     */
    private function findAllEmotionManufacturerSliderElements($cssClass)
    {
        $return = array();

        $class = 'div.slide';
        $slides = $this->findAllEmotionElements($cssClass, $class);

        foreach ($slides as $slide_key => $slide) {
            $class = sprintf('div.slide:nth-of-type(%d) div.supplier', $slide_key + 1);
            $suppliers = $this->findAllEmotionElements($cssClass, $class);

            foreach ($suppliers as $supplier_key => $supplier) {
                $elements = array();

                $class = sprintf(
                        'div.emotion-element %s div.slide:nth-of-type(%d) div.supplier:nth-of-type(%d) ',
                        $cssClass,
                        $slide_key + 1,
                        $supplier_key + 1
                );
                $elements['a-image'] = $this->find('css', $class . 'a.image-wrapper');
                $elements['img'] = $this->find('css', $class . 'img');

                $return[] = $elements;
            }
        }
        return $return;
    }

    /**
     * Helper function to get all important tags of an article slider
     * @param string $cssClass
     * @return array
     */
    private function findAllEmotionArticleSliderElements($cssClass)
    {
        $return = array();

        $class = 'div.slide';
        $slides = $this->findAllEmotionElements($cssClass, $class);

        foreach ($slides as $slide_key => $slide) {
            $class = sprintf('div.slide:nth-of-type(%d) div.outer-article-box', $slide_key + 1);
            $articles = $this->findAllEmotionElements($cssClass, $class);

            foreach ($articles as $article_key => $article) {
                $elements = array();

                $class = sprintf(
                        'div.emotion-element %s div.slide:nth-of-type(%d) div.outer-article-box:nth-of-type(%d) ',
                        $cssClass,
                        $slide_key + 1,
                        $article_key + 1
                );
                $elements['a-thumb'] = $this->find('css', $class . 'a.article-thumb-wrapper');
                $elements['img'] = $this->find('css', $class . 'img');
                $elements['a-title'] = $this->find('css', $class . 'a.title');
                $elements['p-price'] = $this->find('css', $class . 'p.price');

                $return[] = $elements;
            }
        }
        return $return;
    }

    /**
     * Helper function to find all emotion parents elements of one type
     * @param string $type
     * @return array
     */

    private function findAllEmotionParentElements($type)
    {
        $selector = 'div.' . $type . '-element';

        $elements = $this->findAllEmotionElements($selector);

        return $elements;
    }

    /**
     * Helper function to find all emotion sub-elements of a parent
     * @param string $parentClass
     * @param string $class
     * @return array
     */
    private function findAllEmotionElements($parentClass, $class = '')
    {
        $selector = 'div.emotion-element ' . $parentClass;

        if (!empty($class)) {
            $selector .= ' ' . $class;
        }

        $elements = $this->findAll('css', $selector);

        return $elements;
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
