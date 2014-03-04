<?php

use SensioLabs\Behat\PageObjectExtension\PageObject\Page,
    Behat\Mink\Exception\ResponseTextException,
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

    public function checkBanner($image, $links = null)
    {
        $banners = $this->findAll('css', 'div.banner-element img');

        $parent = null;

        foreach ($banners as $banner) {
            $bannerImage = $banner->getAttribute('src');

            if (strpos($bannerImage, $image) !== false) {

                $parent = $banner->getParent();
                break;
            }
        }

        if (empty($parent)) {
            $message = sprintf('The given banner was not found');
            throw new ResponseTextException($message, $this->getSession());
        }

        if (isset($links)) {
            //Banner besitzt Mapping
            if (is_array($links)) {
                $mapping = $this->findAll('css', 'div.banner-element a.emotion-banner-mapping');
                $parent = $this->getEmotionParent($parent);

                foreach ($links as $link) {
                    $link = $link['mapping'];

                    foreach ($mapping as $key => $mappingLink) {
                        $mapLink = $mappingLink->getAttribute('href');

                        if (strpos($mapLink, $link) !== false) {
                            $myParent = $this->getEmotionParent($mappingLink);

                            if ($this->compareNodes($parent, $myParent)) {
                                unset($mapping[$key]);
                                break;
                            }
                        }

                        if ($mappingLink == end($mapping)) {
                            $message = sprintf('The given banner redirects not to %s', $link);
                            throw new ResponseTextException($message, $this->getSession());
                        }
                    }
                }
            } //Banner leitet zu einer einzigen URL weiter
            else {
                $link = $parent->getAttribute('href');

                if (strpos($link, $links) === false) {
                    $message = sprintf('The given banner redirects not to %s', $links);
                    throw new ResponseTextException($message, $this->getSession());
                }
            }
        }
    }

    public function checkBlogArticles($articles)
    {
        $return = array();
        $blogs = $this->findAllEmotionElements('blog');

        foreach($blogs as $blog_key => $blog)
        {
            $entries = $this->findAllEmotionElements('blog', $blog_key + 1, 'div.blog-entry');

            foreach($entries as $entry_key => $entry)
            {
                $elements = array();

                $class = sprintf('div.emotion-element div.blog-element:nth-of-type(%d) div.blog-entry:nth-of-type(%d) ', $blog_key + 1,  $entry_key + 1);
                $elements['a-image'] = $this->find('css', $class.'div.blog_img a');
                $elements['a-title'] = $this->find('css', $class.'h2 a');
                $elements['p-text']  = $this->find('css', $class.'p');

                $return[$blog_key][] = $elements;
            }
        }

        foreach($articles as $article)
        {
            $found = false;

            foreach($blogs as $blog_key => $blog)
            {
                foreach($return[$blog_key] as $itemKey => $item)
                {
                    $check = array(
                        array($item['a-image']->getAttribute('title'), $article['title']),
                        array($item['a-image']->getAttribute('style'), $article['image']),
                        array($item['a-image']->getAttribute('href'),  $article['link']),
                        array($item['a-title']->getAttribute('title'), $article['title']),
                        array($item['a-title']->getAttribute('href'),  $article['link']),
                        array($item['a-title']->getText(),             $article['title']),
                        array($item['p-text'] ->getText(),             $article['text'])
                    );

                    if ($this->checkArray($check)) {
                        $found = true;
                        unset($return[$blog_key][$itemKey]);
                        break;
                    }
                }

                if($found) break;

                if ($blog == end($blogs)) {
                    $message = sprintf('The blog article "%s" with its given properties was not found!', $article['title']);
                    throw new ResponseTextException($message, $this->getSession());
                }
            }
        }
    }

    public function checkYoutubeVideo($code)
    {
        $videos = $this->findAllEmotionElements('youtube');

        foreach($videos as $video_key => $video)
        {
            $class = sprintf('div.emotion-element div.youtube-element:nth-of-type(%d) ', $video_key + 1);
            $source = $this->find('css', $class.'iframe')->getAttribute('src');

            if(strpos($source, $code) !== FALSE)
                return;
        }

        $message = sprintf('The YouTube-Video "%s" was not found!', $code);
        throw new ResponseTextException($message, $this->getSession());
    }

    public function checkSlider($type, $slides)
    {
        $sliders = $this->findAllEmotionSlider($type);

        $check = array();

        foreach($slides as $slide)
        {
            $found = false;

            foreach($sliders as $sliderKey => $slider)
            {
                foreach($sliders[$sliderKey] as $itemKey => $item)
                {
                    switch($type)
                    {
                        case 'banner':
                            $check = array(
                                    array($item['img']->getAttribute('src'),        $slide['image'])
                            );
                            if (!empty($slide['title'])) {
                                $check[] = array($item['img']->getAttribute('title'),$slide['title']);
                            }
                            if (!empty($slide['alt'])) {
                                $check[] = array($item['img']->getAttribute('alt'), $slide['alt']);
                            }
                            if (!empty($slide['link'])) {
                                $check[] = array($item['a']->getAttribute('href'),  $slide['link']);
                            }
                            break;

                        case 'manufacturer':
                            $check = array(
                                    array($item['a-image']->getAttribute('href'),   $slide['link']),
                                    array($item['a-image']->getAttribute('title'),  $slide['name']),
                                    array($item['img']    ->getAttribute('src'),    $slide['image']),
                                    array($item['img']    ->getAttribute('alt'),    $slide['name'])
                            );
                            break;

                        case 'article':
                            $check = array(
                                    array($item['a-thumb']->getAttribute('href'),   $slide['link']),
                                    array($item['a-thumb']->getAttribute('title'),  $slide['name']),
                                    array($item['img']    ->getAttribute('src'),    $slide['image']),
                                    array($item['img']    ->getAttribute('title'),  $slide['name']),
                                    array($item['a-title']->getAttribute('href'),   $slide['link']),
                                    array($item['a-title']->getAttribute('title'),  $slide['name']),
                                    array($item['a-title']->getText(),              $slide['name']),
                                    array($item['p-price']->getText(),              $slide['price'])
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
     * Helper function to check each row of an array. If each second sub-element of a row is in its first, check is true
     * @param $check
     * @return bool
     */
    private function checkArray($check)
    {
        foreach ($check as $compare) {
//            var_dump($compare);
            if (strpos($compare[0], $compare[1]) === false) {
                return false;
            }
        }

        return true;
    }

    /** Helper function to find all emotion slider and their important tags */
    private function findAllEmotionSlider($type)
    {
        $selector = $type . '-slider';

        $sliders = $this->findAllEmotionElements($selector);

        $return = array();

        foreach($sliders as $slider_key => $slider)
        {
            switch($type)
            {
                case 'banner':
                    $return[$slider_key] = $this->findAllEmotionBannerSliderElements($slider_key);
                    break;

                case 'manufacturer':
                    $return[$slider_key] = $this->findAllEmotionManufacturerSliderElements($slider_key);
                    break;

                case 'article':
                    $return[$slider_key] = $this->findAllEmotionArticleSliderElements($slider_key);
                    break;
            }
        }

        return $return;
    }

    /**
     * Helper function to get all important tags of a banner slider
     * @param $slider_key
     * @return array
     */
    private function findAllEmotionBannerSliderElements($slider_key)
    {
        $return = array();

        $class = 'div.slide';
        $slides = $this->findAllEmotionElements('banner-slider', $slider_key + 1, $class);

        foreach($slides as $slide_key => $slide)
        {
            $elements = array();

            $class = sprintf('div.emotion-element div.banner-slider-element:nth-of-type(%d) div.slide:nth-of-type(%d) ', $slider_key + 1,  $slide_key + 1);
            $elements['a']   = $this->find('css', $class.'a');
            $elements['img'] = $this->find('css', $class.'img');

            $return[] = $elements;
        }
        return $return;
    }

    /**
     * Helper function to get all important tags of a manufacturer slider
     * @param $slider_key
     * @return array
     */
    private function findAllEmotionManufacturerSliderElements($slider_key)
    {
        $return = array();

        $class = 'div.slide';
        $slides = $this->findAllEmotionElements('manufacturer-slider', $slider_key + 1, $class);

        foreach($slides as $slide_key => $slide)
        {
            $class = sprintf('div.slide:nth-of-type(%d) div.supplier', $slide_key + 1);
            $suppliers = $this->findAllEmotionElements('manufacturer-slider', $slider_key + 1, $class);

            foreach($suppliers as $supplier_key => $supplier)
            {
                $elements = array();

                $class = sprintf('div.emotion-element div.manufacturer-slider-element:nth-of-type(%d) div.slide:nth-of-type(%d) div.supplier:nth-of-type(%d) ', $slider_key + 1,  $slide_key + 1, $supplier_key + 1);
                $elements['a-image'] = $this->find('css', $class.'a.image-wrapper');
                $elements['img']     = $this->find('css', $class.'img');

                $return[] = $elements;
            }
        }
        return $return;
    }

    /**
     * Helper function to get all important tags of an article slider
     * @param $slider_key
     * @return array
     */
    private function findAllEmotionArticleSliderElements($slider_key)
    {
        $return = array();

        $class = 'div.slide';
        $slides = $this->findAllEmotionElements('article-slider', $slider_key + 1, $class);

        foreach($slides as $slide_key => $slide)
        {
            $class = sprintf('div.slide:nth-of-type(%d) div.outer-article-box', $slide_key + 1);
            $articles = $this->findAllEmotionElements('article-slider', $slider_key + 1, $class);

            foreach($articles as $article_key => $article)
            {
                $elements = array();

                $class = sprintf('div.emotion-element div.article-slider-element:nth-of-type(%d) div.slide:nth-of-type(%d) div.outer-article-box:nth-of-type(%d) ', $slider_key + 1,  $slide_key + 1, $article_key + 1);
                $elements['a-thumb'] = $this->find('css', $class.'a.article-thumb-wrapper');
                $elements['img']     = $this->find('css', $class.'img');
                $elements['a-title'] = $this->find('css', $class.'a.title');
                $elements['p-price'] = $this->find('css', $class.'p.price');

                $return[] = $elements;
            }
        }
        return $return;
    }

    /**
     * Helper function to find all emotion elements of one typ
     * @param $type
     * @param int $id
     * @param string $class
     * @return array
     */

    private function findAllEmotionElements($type, $id = 0, $class = '')
    {
        $selector = 'div.emotion-element div.' . $type . '-element';

        if (!empty($id)) {
            $selector .= ':nth-of-type(' . $id . ')';
        }

        if (!empty($class)) {
            $selector .= ' ' . $class;
        }

//        var_dump($selector);
        $elements = $this->findAll('css', $selector);

        return $elements;
    }

    /**
     * Helper function to compare two nodes by their classes
     * @param \Behat\Mink\Element\NodeElement $node1
     * @param \Behat\Mink\Element\NodeElement $node2
     * @return bool
     */
    private function compareNodes(Behat\Mink\Element\NodeElement $node1, Behat\Mink\Element\NodeElement $node2)
    {
        $class1 = $node1->getAttribute('class');
        $class2 = $node2->getAttribute('class');

        if ($class1 === $class2) {
            return true;
        }

        return false;
    }

    /**
     * Helper function to get the top-parent of an emotion element (using its class)
     * @param \Behat\Mink\Element\NodeElement $node
     * @return \Behat\Mink\Element\NodeElement
     */
    private function getEmotionParent(Behat\Mink\Element\NodeElement $node)
    {
        do {
            $node = $node->getParent();
        } while (strpos($node->getAttribute('class'), 'emotion-element') === false);

        return $node;
    }
}
