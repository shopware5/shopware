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

use Shopware\Bundle\EmotionBundle\Struct\Element;
use Shopware\Bundle\EmotionBundle\Struct\Emotion;
use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\SearchBundle\Sorting\PopularitySorting;
use Shopware\Bundle\SearchBundle\Sorting\PriceSorting;
use Shopware\Bundle\SearchBundle\Sorting\ReleaseDateSorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactory;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Components\ProductStream\RepositoryInterface;

class Shopware_Controllers_Widgets_Emotion extends Enlight_Controller_Action
{
    /**
     * The getEmotions function selects all emotions for the passed category id
     * and sets the result into the view variable "sEmotions".
     */
    public function indexAction()
    {
        /** @var ShopContext $shopContext */
        $shopContext = $this->container->get('shopware_storefront.context_service')->getShopContext();
        $emotionService = $this->container->get('shopware_emotion.emotion_service');

        $emotionIds = [(int) $this->Request()->getParam('emotionId')];

        $emotions = $emotionService->getList($emotionIds, $shopContext);

        if (!count($emotions)) {
            return;
        }

        /** @var Emotion $emotion */
        $emotion = reset($emotions);

        if ($emotion->isPreview() && $emotion->getPreviewSecret() !== $this->Request()->getParam('secret')) {
            return;
        }

        $emotions = array_map([$this, 'getLegacyEmotion'], $emotions);

        if ($emotion->getTemplate()) {
            $this->View()->loadTemplate('widgets/emotion/' . $emotion->getTemplate()->getFile());
        }

        $this->View()->assign('categoryId', (int) $this->Request()->getParam('categoryId'));
        $this->View()->assign('Controller', (string) $this->Request()->getParam('controllerName'));
        $this->View()->assign('sEmotions', $emotions, true);
    }

    /**
     * Action that will be triggered by product slider type top seller
     */
    public function emotionArticleSliderAction()
    {
        $this->View()->loadTemplate('frontend/_includes/product_slider_items.tpl');

        $category = (int) $this->Request()->getParam('category');
        if (!$category) {
            $this->Response()->setStatusCode(404);

            return;
        }

        $limit = (int) $this->Request()->getParam('limit', 5);
        $sort = $this->Request()->getParam('sort', 'newcomer');
        $pages = (int) $this->Request()->getParam('pages');
        $offset = (int) $this->Request()->getParam('start', $limit * ($pages - 1));
        $max = (int) $this->Request()->getParam('max');

        if ($limit !== 0) {
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
        $emotionId = (int) $this->Request()->getParam('emotionId');

        $emotion = $this->get('emotion_device_configuration')->getById($emotionId);

        // The user can preview the emotion for every device.
        $emotion['devices'] = '0,1,2,3,4';

        $viewAssignments['emotion'] = $emotion;
        $viewAssignments['previewSecret'] = $this->Request()->getParam('secret');
        $viewAssignments['hasEmotion'] = !empty($emotion);

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

        $streamId = (int) $this->Request()->getParam('streamId');

        $pages = (int) $this->Request()->getParam('pages', 1);
        $offset = (int) $this->Request()->getParam('start', $limit * ($pages - 1));

        $maxPages = 0;
        $max = (int) $this->Request()->getParam('max');
        if ($limit !== 0) {
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
     * Returns a list of top sold products
     *
     * @param int    $category
     * @param int    $offset
     * @param int    $limit
     * @param string $sort
     *
     * @return array
     */
    private function getProductSliderData($category, $offset, $limit, $sort = null)
    {
        /** @var ShopContext $context */
        $context = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext();
        /** @var StoreFrontCriteriaFactory $factory */
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

        /** @var ProductSearchResult $result */
        $result = Shopware()->Container()->get('shopware_search.product_search')->search($criteria, $context);
        $data = Shopware()->Container()->get('legacy_struct_converter')->convertListProductStructList($result->getProducts());

        $count = $result->getTotalCount();
        if ($limit !== 0) {
            $pages = round($count / $limit);
        } else {
            $pages = 0;
        }

        if ($pages === 0 && $count > 0) {
            $pages = 1;
        }

        return ['values' => $data, 'pages' => $pages];
    }

    /**
     * @param int $productStreamId
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    private function getProductStream($productStreamId, $offset = 0, $limit = 100)
    {
        $context = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext();
        $factory = Shopware()->Container()->get('shopware_search.store_front_criteria_factory');

        $category = $context->getShop()->getCategory()->getId();
        $criteria = $factory->createBaseCriteria([$category], $context);
        $criteria->offset($offset)
                 ->limit($limit);

        /** @var RepositoryInterface $streamRepository */
        $streamRepository = $this->get('shopware_product_stream.repository');
        $streamRepository->prepareCriteria($criteria, $productStreamId);

        /** @var ProductSearchResult $result */
        $result = Shopware()->Container()->get('shopware_search.product_search')->search($criteria, $context);
        $data = Shopware()->Container()->get('legacy_struct_converter')->convertListProductStructList($result->getProducts());

        $count = $result->getTotalCount();

        if ($limit !== 0) {
            $pages = round($count / $limit);
        } else {
            $pages = 0;
        }

        if ($pages === 0 && $count > 0) {
            $pages = 1;
        }

        return ['values' => $data, 'pages' => $pages];
    }

    /**
     * @return array
     */
    private function getLegacyEmotion(Emotion $emotion)
    {
        $emotionConverter = $this->container->get('shopware_emotion.emotion_struct_converter');
        $legacyEmotion = $emotionConverter->convertEmotion($emotion);

        /** @var Element $element */
        foreach ($emotion->getElements() as $index => $element) {
            $legacyEmotion['elements'][$index] = $emotionConverter->convertEmotionElement($element);
        }

        $legacyEmotion['elements'] = array_values($legacyEmotion['elements']);

        return $legacyEmotion;
    }
}
