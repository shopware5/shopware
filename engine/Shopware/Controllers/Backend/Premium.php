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

use Shopware\Models\Article\Detail;
use Shopware\Models\Premium\Premium;
use Shopware\Models\Shop\Shop;

class Shopware_Controllers_Backend_Premium extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $articleDetailRepository;

    /**
     * @var Shopware\Models\Premium\Repository
     */
    private $repository;

    public function initAcl()
    {
        $this->addAclPermission('getPremiumArticles', 'read', "You're not allowed to see the articles.");
        $this->addAclPermission('createPremiumArticle', 'create', "You're not allowed to create an article.");
        $this->addAclPermission('editPremiumArticle', 'update', "You're not allowed to update the article.");
        $this->addAclPermission('deletePremiumArticle', 'delete', "You're not allowed to delete the article.");
    }

    /**
     * Disable template engine for all actions
     */
    public function preDispatch()
    {
        if (!in_array($this->Request()->getActionName(), ['index', 'load', 'validateArticle'])) {
            $this->Front()->Plugins()->Json()->setRenderer(true);
        }
    }

    public function getSubShopsAction()
    {
        //load shop repository
        $repository = Shopware()->Models()->getRepository(Shop::class);

        $builder = $repository->createQueryBuilder('shops');
        $builder->select([
            'shops.id as id',
            'shopLocale.id as locale',
            'category.id as categoryId',
            'shops.name as name',
        ]);
        $builder->join('shops.category', 'category');
        $builder->leftJoin('shops.locale', 'shopLocale');
        $query = $builder->getQuery();

        //select all shops as array
        $data = $query->getArrayResult();

        //return the data and total count
        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Function to get all premium-products and it's name and subshop-name
     * Also used to enable the search of products
     */
    public function getPremiumArticlesAction()
    {
        $this->repository = Shopware()->Models()->getRepository(Premium::class);

        $start = $this->Request()->get('start');
        $limit = $this->Request()->get('limit');

        //order data
        $order = (array) $this->Request()->getParam('sort', []);

        //If a search-filter is set
        if ($this->Request()->get('filter')) {
            //Get the value itself
            $filter = $this->Request()->get('filter');
            $filter = $filter[count($filter) - 1];
            $filterValue = $filter['value'];

            $query = $this->repository->getBackendPremiumListQuery($start, $limit, $order, $filterValue);
            $totalResult = Shopware()->Models()->getQueryCount($query);
        } else {
            $query = $this->repository->getBackendPremiumListQuery($start, $limit, $order);
            $totalResult = Shopware()->Models()->getQueryCount($query);
        }

        try {
            $data = $query->getArrayResult();

            $this->View()->assign(['success' => true, 'data' => $data, 'total' => $totalResult]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * Function to create a premium-product
     *
     * @throws Exception
     */
    public function createPremiumArticleAction()
    {
        if (!$this->Request()->isPost()) {
            echo Zend_Json::encode(['success' => false, 'errorMsg' => 'Empty Post Request']);

            return;
        }

        $params = $this->Request()->getParams();
        $params['startPrice'] = str_replace(',', '.', $params['startPrice']);
        $premiumModel = new Shopware\Models\Premium\Premium();

        try {
            if (empty($params['orderNumberExport'])) {
                $params['orderNumberExport'] = $params['orderNumber'];
            }
            if (empty($params['orderNumber'])) {
                throw new Exception('No ordernumber was entered.');
            }
            //Fills the model by using the array $params
            $premiumModel->fromArray($params);

            //find the shop-model by using the subShopId
            /** @var Shop $shop */
            $shop = Shopware()->Models()->find(Shop::class, $params['shopId']);
            $premiumModel->setShop($shop);

            /** @var Detail $productVariant */
            $productVariant = $this->getArticleDetailRepository()->findOneBy(['number' => $params['orderNumber']]);
            $premiumModel->setArticleDetail($productVariant);

            //If the product is already set as a premium-product
            $repository = Shopware()->Models()->getRepository(Premium::class);
            $result = $repository->findByOrderNumber($params['orderNumber']);
            $result = Shopware()->Models()->toArray($result);

            if (!empty($result) && $params['shopId'] == $result[0]['shopId']) {
                $this->View()->assign(['success' => false, 'errorMsg' => 'The product is already a premium-product.']);

                return;
            }

            //saves the model
            Shopware()->Models()->persist($premiumModel);
            Shopware()->Models()->flush();

            $data = Shopware()->Models()->toArray($premiumModel);

            $this->View()->assign(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * Function to update a premium-product
     */
    public function editPremiumArticleAction()
    {
        $errorMsg = null;
        if (!$this->Request()->isPost()) {
            echo Zend_Json::encode(['success' => false, 'errorMsg' => 'Empty Post Request']);

            return;
        }

        $params = $this->Request()->getParams();
        $premiumModel = Shopware()->Models()->find(Premium::class, $params['id']);

        try {
            if (empty($params['orderNumberExport'])) {
                $params['orderNumberExport'] = $params['orderNumber'];
            }
            //Replace a comma with a dot
            $params['startPrice'] = str_replace(',', '.', $params['startPrice']);

            /* @var Premium $premiumModel */
            $premiumModel->fromArray($params);

            Shopware()->Models()->persist($premiumModel);
            Shopware()->Models()->flush();

            $this->View()->assign(['success' => true, 'data' => $params]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg', $e->getMessage()]);
        }
    }

    /**
     * Function to delete a single or multiple premium-product(s)
     */
    public function deletePremiumArticleAction()
    {
        try {
            if (!$this->Request()->isPost()) {
                $this->View()->assign(['success' => false, 'errorMsg' => 'Empty Post Request']);

                return;
            }
            $repository = Shopware()->Models()->getRepository(Premium::class);

            $params = $this->Request()->getParams();
            unset($params['module'], $params['controller'], $params['action'], $params['_dc']);

            if ($params[0]) {
                $data = [];
                foreach ($params as $values) {
                    $id = $values['id'];
                    /** @var Premium $model */
                    $model = $repository->find($id);
                    Shopware()->Models()->remove($model);
                    Shopware()->Models()->flush();
                    $data[] = Shopware()->Models()->toArray($model);
                }
            } else {
                $id = $this->Request()->get('id');
                /** @var Premium $model */
                $model = $repository->find($id);

                Shopware()->Models()->remove($model);
                Shopware()->Models()->flush();
                $data = Shopware()->Models()->toArray($model);
            }
            $this->View()->assign(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * Function to check if an product exists or is already added as a premium-product
     */
    public function validateArticleAction()
    {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
        $value = trim($this->Request()->get('value'));

        // Is there a value in the textfield?
        if ($value == null) {
            return;
        }

        // If the product exists
        $repository = Shopware()->Models()->getRepository(Detail::class);
        $result = $repository->findByNumber($value);

        if (!$result[0]) {
            return;
        }

        // If the product is already set as a premium-product
        $repository = Shopware()->Models()->getRepository(Premium::class);
        $result = $repository->findByOrderNumber($value);

        if ($result[0]) {
            return;
        }

        echo true;
    }

    /**
     * Helper function to get access to the articleDetail repository.
     *
     * @return \Shopware\Components\Model\ModelRepository
     */
    private function getArticleDetailRepository()
    {
        if ($this->articleDetailRepository === null) {
            $this->articleDetailRepository = Shopware()->Models()->getRepository(Detail::class);
        }

        return $this->articleDetailRepository;
    }
}
