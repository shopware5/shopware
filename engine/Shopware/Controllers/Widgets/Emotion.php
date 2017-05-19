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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\SearchBundle\Sorting\PopularitySorting;
use Shopware\Bundle\SearchBundle\Sorting\PriceSorting;
use Shopware\Bundle\SearchBundle\Sorting\ReleaseDateSorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Components\Model\Query\SqlWalker;
use Shopware\Models\Emotion\Repository;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Widgets_Emotion extends Enlight_Controller_Action
{
    /**
     * The getEmotions function selects all emotions for the passed category id
     * and sets the result into the view variable "sEmotions".
     */
    public function indexAction()
    {
        /** @var $repository Repository */
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Emotion\Emotion');
        $emotions = $this->getEmotion($repository);

        /** @var ShopContext $shopContext */
        $shopContext = $this->get('shopware_storefront.context_service')->getShopContext();
        $shopId = $shopContext->getShop()->getId();
        $translator = new Shopware_Components_Translation();
        $fallbackId = $shopContext->getShop()->getFallbackId();

        $preview = $this->Request()->getParam('preview', false);

        $elementIds = [];
        foreach ($emotions as $emotion) {
            $elementIds += array_column($emotion['elements'], 'id');
        }

        $viewports = $repository->getElementsViewports($elementIds);

        //iterate all emotions to select the element data.
        foreach ($emotions as &$emotion) {
            // Support for emotions which are available on multiple devices.
            $emotion['device'] = explode(',', $emotion['device']);
            $categoryId = $this->Request()->getParam('categoryId') ?: $emotion['categories'][0]['id'];

            // Mapping for the grid settings
            $emotion['grid'] = [
                'cols' => $emotion['cols'],
                'rows' => $emotion['rows'],
                'gutter' => $emotion['cellSpacing'],
                'cellHeight' => $emotion['cellHeight'],
                'articleHeight' => $emotion['articleHeight'],
            ];

            // The preview only shows the normal size.
            if ($preview) {
                $emotion['fullscreen'] = false;
            }

            //for each emotion we have to iterate the elements to get the element data.
            foreach ($emotion['elements'] as &$element) {
                $data = $this->handleElement($element, $repository, $categoryId);
                $translation = $translator->readWithFallback($shopId, $fallbackId, 'emotionElement', $element['id']);
                $element['data'] = array_merge($data, $translation);

                $element['viewports'] = [];

                if (isset($viewports[$element['id']])) {
                    $element['viewports'] = $viewports[$element['id']];
                }
            }
        }

        if (empty($emotions[0]['template'])) {
            $this->View()->loadTemplate('widgets/emotion/index.tpl');
        } else {
            $this->View()->loadTemplate('widgets/emotion/' . $emotions[0]['template']['file']);
        }

        $this->View()->assign('categoryId', (int) $this->Request()->getParam('categoryId'));
        $this->View()->assign('sEmotions', $emotions, true);
        $this->View()->assign('Controller', (string) $this->Request()->getParam('controllerName'));
    }

    /**
     * Get emotion by category
     *
     * @param $repository Repository
     *
     * @return array
     */
    public function getEmotion(Repository $repository)
    {
        $categoryId = (int) $this->Request()->getParam('categoryId');
        $emotionId = (int) $this->Request()->getParam('emotionId');

        if ($emotionId) {
            $query = $repository->getEmotionDetailQuery($emotionId);
        } else {
            $query = $repository->getCategoryEmotionsQuery($categoryId);
        }

        $emotions = $query->getArrayResult();

        foreach ($emotions as &$emotion) {
            $emotion['elements'] = $repository->getEmotionElementsQuery($emotion['id'])->getQuery()->getArrayResult();
        }

        return $emotions;
    }

    /**
     * Action that will be triggered by product slider type top seller
     *
     * @deprecated use emotionArticleSliderAction instead
     */
    public function emotionTopSellerAction()
    {
        $this->Request()->setParam('sort', 'topseller');
        $this->emotionArticleSliderAction();
    }

    /**
     * Action that will be triggered by product slider type newcomer
     *
     * @deprecated use emotionArticleSliderAction instead
     */
    public function emotionNewcomerAction()
    {
        $this->Request()->setParam('sort', 'newcomer');
        $this->emotionArticleSliderAction();
    }

    /**
     * Action that will be triggered by product slider type top seller
     */
    public function emotionArticleSliderAction()
    {
        $category = (int) $this->Request()->getParam('category');
        if (!$category) {
            $this->Response()->setHttpResponseCode(404);

            return;
        }

        $this->View()->loadTemplate('frontend/_includes/product_slider_items.tpl');

        $limit = (int) $this->Request()->getParam('limit', 5);
        $sort = $this->Request()->getParam('sort', 'newcomer');
        $pages = $this->Request()->getParam('pages');
        $offset = (int) $this->Request()->getParam('start', $limit * ($pages - 1));
        $max = $this->Request()->getParam('max');

        if ($limit != 0) {
            $maxPages = round($max / $limit);
        } else {
            $maxPages = 0;
        }

        $values = $this->getProductSliderData($category, $offset, $limit, $sort);

        $this->View()->assign('articles', $values['values']);
        $this->View()->assign('productBoxLayout', $this->Request()->getParam('productBoxLayout', 'emotion'));
        $this->View()->assign('fixedImageSize', $this->Request()->getParam('fixedImageSize', true));
        $this->View()->assign('pages', $values['pages'] > $maxPages ? $maxPages : $values['pages']);
        $this->View()->assign('sPerPage', $limit);
    }

    /**
     * preview action method
     *
     * generates the backend iframe emotion preview
     */
    public function previewAction()
    {
        $emotionId = $this->Request()->getParam('emotionId');

        $emotion = $this->get('emotion_device_configuration')->getById($emotionId);

        // The user can preview the emotion for every device.
        $emotion['devices'] = '0,1,2,3,4';

        $viewAssignments['emotion'] = $emotion;
        $viewAssignments['hasEmotion'] = (!empty($emotion));

        $viewAssignments['showListing'] = (bool) max(array_column($emotion, 'showListing'));

        $showListing = (empty($emotion) || !empty($emotion['show_listing']));
        $viewAssignments['showListing'] = $showListing;

        $this->View()->assign($viewAssignments);

        //fake to prevent rendering the templates with the widgets module.
        //otherwise the template engine don't accept to load templates of the `frontend` module
        $this->Request()->setModuleName('frontend');
    }

    public function productStreamArticleSliderAction()
    {
        $this->View()->loadTemplate('frontend/_includes/product_slider_items.tpl');
        $limit = (int) $this->Request()->getParam('limit', 5);

        $streamId = $this->Request()->getParam('streamId');

        $pages = $this->Request()->getParam('pages', 1);
        $offset = (int) $this->Request()->getParam('start', $limit * ($pages - 1));

        $maxPages = 0;
        $max = $this->Request()->getParam('max');
        if ($limit != 0) {
            $maxPages = round($max / $limit);
        } else {
            $limit = 0;
        }

        $values = $this->getProductStream($streamId, $offset, $limit);

        $this->View()->assign('articles', $values['values']);
        $this->View()->assign('productBoxLayout', $this->Request()->getParam('productBoxLayout', 'emotion'));
        $this->View()->assign('fixedImageSize', $this->Request()->getParam('fixedImageSize', true));
        $this->View()->assign('pages', $values['pages'] > $maxPages ? $maxPages : $values['pages']);
        $this->View()->assign('sPerPage', $limit);
    }

    /**
     * @param array      $element
     * @param Repository $repository
     * @param int        $categoryId
     *
     * @return array
     */
    protected function handleElement(array &$element, Repository $repository, $categoryId)
    {
        $component = $element['component'];
        $elementQuery = $repository->getElementDataQuery($element['id'], $element['componentId']);
        $componentData = $elementQuery->getArrayResult();

        $data = [];
        $data['objectId'] = md5($element['id']);

        //we have to iterate the component data to decode the values.
        foreach ($componentData as $entry) {
            switch (strtolower($entry['valueType'])) {
                case 'json':
                    if ($entry['value'] != '') {
                        $value = Zend_Json::decode($entry['value']);
                    } else {
                        $value = null;
                    }
                    break;
                case 'string':
                default:
                    $value = $entry['value'];
                    break;
            }
            $data[$entry['name']] = $value;
        }

        $data = Shopware()->Events()->filter(
            'Shopware_Controllers_Widgets_Emotion_AddElement',
            $data,
            ['subject' => $this, 'element' => $element]
        );

        if (!empty($component['convertFunction'])) {
            $convertFunction = $component['convertFunction'];
            $data = $this->$convertFunction($data, $categoryId, $element);
        }

        return $data;
    }

    /**
     * Convert Function called by handleElement()
     *
     * @param array $data
     * @param int   $categoryId
     * @param array $element
     *
     * @return array
     */
    private function getArticle($data, $categoryId, $element)
    {
        $categoryId = empty($data['article_category']) ? $categoryId : $data['article_category'];
        $type = empty($data['article_type']) ? 'selected_article' : $data['article_type'];

        if ($type == 'selected_article' && !empty($data['article'])) {
            return array_merge($data, $this->articleByNumber($data['article']));
        }

        if ($type == 'newcomer') {
            return array_merge($data, Shopware()->Modules()->Articles()->sGetPromotionById('new', $categoryId, 0, false));
        }

        if ($type == 'topseller') {
            $article = Shopware()->Modules()->Articles()->sGetPromotionById('top', $categoryId, 0, false);

            if (empty($article['articleID'])) {
                return array_merge($data, Shopware()->Modules()->Articles()->sGetPromotionById('random', $categoryId, 0, false));
            }

            return array_merge($data, $article);
        }

        if ($type == 'random_article') {
            return array_merge($data, Shopware()->Modules()->Articles()->sGetPromotionById('random', $categoryId, 0, false));
        }

        return $data;
    }

    /**
     * Gets a random blog entry from the database
     *
     * @param $category
     *
     * @return array {Array} $result
     */
    private function getRandomBlogEntry($category)
    {
        $data = ['entry_amount' => 50];
        $result = $this->getBlogEntry($data, $category);

        return $result['entries'][array_rand($result['entries'])];
    }

    /**
     * Gets the specific blog entry from the database.
     *
     * @param $data
     * @param $category
     *
     * @internal param $ {Array} $data
     *
     * @return array {Array} $data
     */
    private function getBlogEntry($data, $category)
    {
        $entryAmount = (int) $data['entry_amount'];

        if (isset($data['blog_entry_selection']) && $data['blog_entry_selection']) {
            $category = $data['blog_entry_selection'];
        }

        if ($category === null) {
            return $data;
        }

        // Get the category model for the given category ID
        /** @var $category \Shopware\Models\Category\Category */
        $category = Shopware()->Models()->find('Shopware\Models\Category\Category', $category);

        if (!$category) {
            return $data;
        }

        $builder = Shopware()->Models()->createQueryBuilder();

        if (isset($data['blog_entry_selection']) && $data['blog_entry_selection']) {
            $builder->select(['blog', 'media', 'mappingMedia'])
                ->from('Shopware\Models\Blog\Blog', 'blog')
                ->leftJoin('blog.media', 'mappingMedia', \Doctrine\ORM\Query\Expr\Join::WITH, 'mappingMedia.preview = 1')
                ->leftJoin('mappingMedia.media', 'media')
                ->leftJoin('blog.category', 'category')
                ->where('blog.active = 1')
                ->andWhere('blog.displayDate <= :displayDate')
                ->andWhere('blog.categoryId = :category')
                ->orderBy('blog.displayDate', 'DESC')
                ->setFirstResult(0)
                ->setMaxResults($entryAmount)
                ->setParameter('displayDate', date('Y-m-d H:i:s'))
                ->setParameter('category', $category->getId());
        } else {
            $builder->select(['blog', 'media', 'mappingMedia'])
                ->from('Shopware\Models\Blog\Blog', 'blog')
                ->leftJoin('blog.media', 'mappingMedia', \Doctrine\ORM\Query\Expr\Join::WITH, 'mappingMedia.preview = 1')
                ->leftJoin('mappingMedia.media', 'media')
                ->leftJoin('blog.category', 'category')
                ->where('blog.active = 1')
                ->andWhere('blog.displayDate <= :displayDate')
                ->andWhere('(category.path LIKE :path OR category.id = :categoryId)')
                ->orderBy('blog.displayDate', 'DESC')
                ->setFirstResult(0)
                ->setMaxResults($entryAmount)
                ->setParameter('displayDate', date('Y-m-d H:i:s'))
                ->setParameter('categoryId', $category->getId())
                ->setParameter('path', '%|' . $category->getId() . '|%')
            ;
        }

        $query = $this->getForceIndexQuery($builder->getQuery(), 'emotion_get_blog_entry');
        $result = $query->getArrayResult();

        $mediaIds = [];
        foreach ($result as $entry) {
            $mediaIds = array_merge(
                array_column($entry['media'], 'mediaId'),
                $mediaIds
            );
        }
        $context = $this->get('shopware_storefront.context_service')->getShopContext();
        $medias = $this->get('shopware_storefront.media_service')->getList($mediaIds, $context);

        $links = [];
        foreach ($result as $blog) {
            $links[$blog['id']] = [
                'controller' => 'blog',
                'action' => 'detail',
                'sCategory' => $blog['categoryId'],
                'blogArticle' => $blog['id'],
            ];
        }
        $urls = $this->get('router')->generateList($links);

        foreach ($result as &$entry) {
            $entry['link'] = null;

            if (array_key_exists($entry['id'], $urls)) {
                $entry['link'] = $urls[$entry['id']];
            }

            foreach ($entry['media'] as $media) {
                if (empty($media['mediaId'])) {
                    continue;
                }
                $id = $media['mediaId'];

                if (!isset($medias[$id])) {
                    continue;
                }

                $struct = $medias[$id];

                $mediaData = Shopware()->Container()->get('legacy_struct_converter')->convertMediaStruct($struct);
                $entry['media'] = $mediaData;
            }
        }

        if (!empty($result)) {
            $data['totalCount'] = count($result);
            $data['entries'] = $result;
        }

        return $data;
    }

    /**
     * Helper function to set the FORCE INDEX path.
     *
     * @param $query \Doctrine\ORM\Query
     * @param $index String
     *
     * @return \Doctrine\ORM\Query
     */
    private function getForceIndexQuery($query, $index)
    {
        $query->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Shopware\Components\Model\Query\SqlWalker\ForceIndexWalker');
        $query->setHint(SqlWalker\ForceIndexWalker::HINT_FORCE_INDEX, $index);
        $query->setHint(SqlWalker\ForceIndexWalker::HINT_STRAIGHT_JOIN, true);

        return $query;
    }

    /**
     * Convert Function called by handleElement()
     *
     * @param array $data
     * @param int   $category
     * @param array $element
     *
     * @return array
     */
    private function getArticleByNumber($data, $category, $element)
    {
        return $data;
    }

    /**
     * Convert Function called by handleElement()
     *
     * @param array $data
     * @param int   $category
     * @param array $element
     *
     * @return array
     */
    private function getCategoryTeaser($data, $category, $element)
    {
        // First get category name
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select('category.name')
            ->from('Shopware\Models\Category\Category', 'category')
            ->where('category.id = ?1')
            ->setParameter(1, $data['category_selection']);

        $categoryName = $builder->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $data['categoryName'] = $categoryName['name'];
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        // Second get category image per random, if configured
        if ($data['image_type'] != 'selected_image') {
            if ($data['blog_category']) {
                $result = $this->getRandomBlogEntry($data['category_selection']);

                $data['image'] = $result['media'];
            } else {
                // Get random article from selected $category
                $temp = Shopware()->Modules()->Articles()->sGetPromotionById('random', $data['category_selection'], 0, true);

                $data['image'] = $temp['image'];
                $data['images'] = $temp['images'];
            }
        } else {
            $mediaId = Shopware()->Db()->fetchOne('SELECT id FROM s_media WHERE path = ?', [$data['image']]);
            $context = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext();
            $media = Shopware()->Container()->get('shopware_storefront.media_service')->get($mediaId, $context);
            if ($media instanceof \Shopware\Bundle\StoreFrontBundle\Struct\Media) {
                $data['media'] = Shopware()->Container()->get('legacy_struct_converter')->convertMediaStruct($media);
            } else {
                $data['media'] = [];
            }
        }

        return $data;
    }

    /**
     * @param string $number
     *
     * @return array
     */
    private function articleByNumber($number)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select('article.id')
            ->from('Shopware\Models\Article\Article', 'article')
            ->join('article.details', 'details')
            ->where('details.number = ?1')
            ->setParameter(1, $number)
            ->setFirstResult(0)
            ->setMaxResults(1);

        $articleId = $builder->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $article = [];
        if (!empty($articleId['id'])) {
            $article = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, $articleId['id']);
        }

        return $article;
    }

    /**
     * Convert Function called by handleElement()
     *
     * @param array $data
     * @param int   $category
     * @param array $element
     *
     * @return array
     */
    private function getBannerMappingLinks($data, $category, $element)
    {
        $mediaService = $this->get('shopware_media.media_service');

        if (!empty($data['link'])) {
            preg_match('/^([a-z]*:\/\/|shopware\.php|mailto:)/i', $data['link'], $matches);

            if (empty($matches) && substr($data['link'], 0, 1) === '/') {
                $data['link'] = $this->Request()->getBaseUrl() . $data['link'];
            }
        }

        $mappings = $data['bannerMapping'];
        if (!empty($mappings)) {
            $numbers = array_column($mappings, 'link');

            $numbers = $this->getProductIdsByNumbers($numbers);

            foreach ($mappings as $key => $mapping) {
                $number = $mapping['link'];

                if (!empty($number)) {
                    preg_match('/^([a-z]*:\/\/|shopware\.php|mailto:)/i', $number, $matches);

                    if (empty($matches)) {
                        if (substr($number, 0, 1) === '/') {
                            $mapping['link'] = $this->Request()->getBaseUrl() . $number;
                        } else {
                            $mapping['link'] = $this->get('config')->get('baseFile') . '?sViewport=detail&sArticle=' . $numbers[$number];
                            $mapping['ordernumber'] = $number;
                        }
                    }
                }

                $mappings[$key] = $mapping;
            }
        }

        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
        $mediaId = Shopware()->Db()->fetchOne('SELECT id FROM s_media WHERE path = ?', [$data['file']]);
        if ($mediaId) {
            $context = $this->get('shopware_storefront.context_service')->getShopContext();
            $media = $this->get('shopware_storefront.media_service')->get($mediaId, $context);
            if ($media instanceof \Shopware\Bundle\StoreFrontBundle\Struct\Media) {
                $mediaData = $this->get('legacy_struct_converter')->convertMediaStruct($media);
            } else {
                $mediaData = [];
            }
            $data = array_merge($mediaData, $data);

            $data['fileInfo'] = [
                'width' => $mediaData['width'],
                'height' => $mediaData['height'],
            ];
        }

        $data['bannerMapping'] = $mappings;

        return $data;
    }

    /**
     * Convert Function called by handleElement()
     *
     * @param array $data
     * @param int   $category
     * @param array $element
     *
     * @return array
     */
    private function getManufacturerSlider($data, $category, $element)
    {
        if (empty($data['manufacturer_type'])) {
            return $data;
        }

        // Get all manufacturers
        if ($data['manufacturer_type'] == 'manufacturers_by_cat') {
            $data['values'] = Shopware()->Modules()->Articles()->sGetAffectedSuppliers($data['manufacturer_category'], 12);
        } else {
            $ids = array_column($data['selected_manufacturers'], 'supplierId');
            $context = $this->get('shopware_storefront.context_service')
                ->getShopContext();

            $manufacturers = $this->get('shopware_storefront.manufacturer_service')
                ->getList($ids, $context);

            $values = array_map(function (Manufacturer $manufacturer) {
                return $this->get('legacy_struct_converter')
                    ->convertManufacturerStruct($manufacturer);
            }, $manufacturers);

            if (!empty($category) && $category != Shopware()->Shop()->getCategory()->getId()) {
                foreach ($values as &$value) {
                    $value['link'] = $value['link'] . '?c=' . (int) $category;
                }
            }

            $data['values'] = $values;
        }

        return $data;
    }

    /**
     * Convert Function called by handleElement()
     *
     * @param array $data
     * @param int   $category
     * @param array $element
     *
     * @return array
     */
    private function getBannerSlider($data, $category, $element)
    {
        $data['values'] = $data['banner_slider'];

        $mediaIds = array_column($data['values'], 'mediaId');
        $context = $this->get('shopware_storefront.context_service')->getShopContext();
        $media = $this->get('shopware_storefront.media_service')->getList($mediaIds, $context);
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        foreach ($data['values'] as &$value) {
            if (!empty($value['link'])) {
                preg_match('/^(http|https):\/\//', $value['link'], $matches);

                if (empty($matches)) {
                    $value['link'] = $this->Request()->getBaseUrl() . $value['link'];
                }
            }
            if (!isset($media[$value['mediaId']])) {
                continue;
            }

            $single = $media[$value['mediaId']];

            $single = $this->get('legacy_struct_converter')->convertMediaStruct($single);

            $value = array_merge($value, $single);
            $value['path'] = $mediaService->getUrl($value['path']);
            $value['fileInfo'] = [
                'width' => $value['width'],
                'height' => $value['height'],
            ];
        }

        return $data;
    }

    /**
     * Convert Function called by handleElement()
     *
     * @param array $data
     * @param int   $category
     * @param array $element
     *
     * @return array
     */
    private function getArticleSlider($data, $category, $element)
    {
        if (!isset($data['article_slider_select'])) {
            $data['article_slider_select'] = 'horizontal';
        }

        // Determinate how many products showing on initial request
        if ($data['article_slider_select'] == 'horizontal') {
            $perPage = $element['endCol'] - $element['startCol'] + 1;
        } else {
            $perPage = $element['endRow'] - $element['startRow'] + 1;
        }

        $category = (int) $data['article_slider_category'] ?: $category;

        $values = [];

        $max = $data['article_slider_max_number'];
        if ($perPage != 0) {
            $maxPages = round($max / $perPage);
        } else {
            $maxPages = 0;
        }

        switch ($data['article_slider_type']) {
            case 'product_stream':
                $temp = $this->getProductStream($data['article_slider_stream'], 0, $perPage);
                $values = $temp['values'];
                $data['pages'] = $temp['pages'] > $maxPages ? $maxPages : $temp['pages'];

                $query = [
                    'controller' => 'emotion',
                    'module' => 'widgets',
                    'action' => 'productStreamArticleSlider',
                    'streamId' => $data['article_slider_stream'],
                ];

                $data['ajaxFeed'] = Shopware()->Container()->get('router')->assemble($query);

                break;
            case 'selected_article':
                foreach ($data['selected_articles'] as &$article) {
                    $articleId = $article['articleId'];
                    $entry = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, $articleId, false);
                    if (!empty($entry['articleID'])) {
                        $values[] = $entry;
                    }
                }
                break;
            case 'topseller':
            case 'newcomer':
            case 'price_asc':
            case 'price_desc':
                $temp = $this->getProductSliderData(
                    $category,
                    0,
                    $perPage,
                    $data['article_slider_type']
                );

                $values = $temp['values'];
                $data['pages'] = $temp['pages'] > $maxPages ? $maxPages : $temp['pages'];

                $query = [
                    'controller' => 'emotion',
                    'module' => 'widgets',
                    'action' => 'emotionArticleSlider',
                    'sort' => $data['article_slider_type'],
                ];
                $data['ajaxFeed'] = Shopware()->Container()->get('router')->assemble($query);
                break;
            default:
                // Prevent the slider form endless loading
                $data['article_slider_type'] = 'selected_article';

                return $data;
        }

        $data['values'] = $values;
        $data['categoryId'] = $category;

        return $data;
    }

    /**
     * Returns a list of top sold products
     *
     * @param int    $category
     * @param int    $offset
     * @param int    $limit
     * @param string $sort
     *
     * @return array
     */
    private function getProductSliderData($category, $offset = 0, $limit, $sort = null)
    {
        $context = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext();
        $factory = Shopware()->Container()->get('shopware_search.store_front_criteria_factory');
        $criteria = $factory->createBaseCriteria([$category], $context);

        $criteria->offset($offset)
           ->limit($limit);

        switch ($sort) {
            case 'price_asc':
                $criteria->addSorting(new PriceSorting(SortingInterface::SORT_ASC));
                break;
            case 'price_desc':
                $criteria->addSorting(new PriceSorting(SortingInterface::SORT_DESC));
                break;
            case 'topseller':
                $criteria->addSorting(new PopularitySorting(SortingInterface::SORT_DESC));
                break;
            case 'newcomer':
                $criteria->addSorting(new ReleaseDateSorting(SortingInterface::SORT_DESC));
                break;
        }

        $criteria = $this->get('events')->filter(
            'Shopware_Controllers_Widgets_Emotion_GetProductSliderData_Filter_Criteria',
            $criteria,
            ['categoryId' => $category, 'sort' => $sort]
        );

        /** @var $result ProductSearchResult */
        $result = Shopware()->Container()->get('shopware_search.product_search')->search($criteria, $context);
        $data = Shopware()->Container()->get('legacy_struct_converter')->convertListProductStructList($result->getProducts());

        $count = $result->getTotalCount();
        if ($limit != 0) {
            $pages = round($count / $limit);
        } else {
            $pages = 0;
        }

        if ($pages == 0 && $count > 0) {
            $pages = 1;
        }

        return ['values' => $data, 'pages' => $pages];
    }

    /**
     * @param $numbers
     *
     * @return array
     */
    private function getProductIdsByNumbers($numbers)
    {
        /** @var Connection $connection */
        $connection = $this->get('dbal_connection');
        $query = $connection->createQueryBuilder();
        $query->select(['variant.ordernumber', 'variant.articleID'])
            ->from('s_articles_details', 'variant')
            ->where('variant.ordernumber IN (:numbers)')
            ->setParameter(':numbers', $numbers, Connection::PARAM_STR_ARRAY);

        /** @var $statement PDOStatement */
        $statement = $query->execute();

        return $statement->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    private function getProductStream($productStreamId, $offset = 0, $limit = 100)
    {
        $context = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext();
        $factory = Shopware()->Container()->get('shopware_search.store_front_criteria_factory');

        $category = $context->getShop()->getCategory()->getId();
        $criteria = $factory->createBaseCriteria([$category], $context);
        $criteria->offset($offset)
                 ->limit($limit);

        /** @var \Shopware\Components\ProductStream\RepositoryInterface $streamRepository */
        $streamRepository = $this->get('shopware_product_stream.repository');
        $streamRepository->prepareCriteria($criteria, $productStreamId);

        /** @var $result ProductSearchResult */
        $result = Shopware()->Container()->get('shopware_search.product_search')->search($criteria, $context);
        $data = Shopware()->Container()->get('legacy_struct_converter')->convertListProductStructList($result->getProducts());

        $count = $result->getTotalCount();

        if ($limit != 0) {
            $pages = round($count / $limit);
        } else {
            $pages = 0;
        }

        if ($pages == 0 && $count > 0) {
            $pages = 1;
        }

        return ['values' => $data, 'pages' => $pages];
    }

    /**
     * Convert media paths to full qualified paths
     *
     * @param array $data
     * @param int   $category
     * @param array $element
     *
     * @return array
     */
    private function getHtml5Video($data, $category, $element)
    {
        $mediaFields = ['webm_video', 'ogg_video', 'h264_video', 'fallback_picture'];
        $mediaService = $this->get('shopware_media.media_service');

        foreach ($mediaFields as $field) {
            if (!preg_match("/^media\/*/i", $data[$field])) {
                continue;
            }

            $data[$field] = $mediaService->getUrl($data[$field]);
        }

        return $data;
    }
}
