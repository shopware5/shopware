<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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

/**
 * Backend Controller of the Bundle Plugin.
 *
 * This controller handles all actions around the shopware backend to define bundles.
 * The controller handles the actions of the bundle overview module and the actions
 * of the extended article bundle tab.
 * The article extension are defined in the SwagBundle/Views/backend/article folder.
 * The bundle overview are defined in the SwagBundle/Views/backend/bundle folder.
 *
 * @category Shopware
 * @package Shopware\Plugins\SwagBundle\Controllers\Backend
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_Bundle extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Internal helper property to make the backend controller unit test possible without mocking the whole shopware project
     * to get access on a model repository.
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $bundleRepository = null;

    /**
     * Internal helper property to make the backend controller unit test possible without mocking the whole shopware project.
     *
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $articleRepository = null;

    /**
     * Internal helper property to make the backend controller unit test possible without mocking the whole shopware project.
     *
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $customerGroupRepository = null;

    /**
     * Internal helper property to make the backend controller unit test possible without mocking the whole shopware project.
     *
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $articleDetailRepository = null;

    /**
     * The getArticleDetailRepository function is an internal helper function to make the backend controller unit test
     * possible without mocking the whole shopware project.
     * @return null|\Shopware\Components\Model\ModelRepository
     */
    public function getArticleDetailRepository()
    {
    	if ($this->articleDetailRepository === null) {
    		$this->articleDetailRepository = Shopware()->Models()->getRepository('Shopware\Models\Article\Detail');
    	}
    	return $this->articleDetailRepository;
    }

    /**
     * The getCustomerGroupRepository function is an internal helper function to make the backend controller unit test
     * possible without mocking the whole shopware project.
     * @return null|\Shopware\Components\Model\ModelRepository
     */
    public function getCustomerGroupRepository()
    {
    	if ($this->customerGroupRepository === null) {
    		$this->customerGroupRepository = Shopware()->Models()->getRepository('Shopware\Models\Customer\Group');
    	}
    	return $this->customerGroupRepository;
    }

    /**
     * Pre dispatch event of the bundle backend module.
     */
    public function preDispatch()
    {
        if(!in_array($this->Request()->getActionName(), array('index', 'load', 'validateNumber'))) {
            $this->Front()->Plugins()->Json()->setRenderer();
        }
    }

    /**
     * The getArticleRepository function is an internal helper function to make the backend controller unit test
     * possible without mocking the whole shopware project.
     * @return null|\Shopware\Components\Model\ModelRepository
     */
    public function getArticleRepository()
    {
    	if ($this->articleRepository === null) {
    		$this->articleRepository = Shopware()->Models()->getRepository('Shopware\Models\Article\Article');
    	}
    	return $this->articleRepository;
    }

    /**
     * The initAcl function initials the shopware acl for the bundle extension.
     */
    protected function initAcl()
    {
        $this->addAclPermission('index', 'read', 'Insufficient Permissions');
        $this->addAclPermission("createBundle","create","Insufficient Permissions");
        $this->addAclPermission("updateBundle","update","Insufficient Permissions");
        $this->addAclPermission("deleteBundle","delete","Insufficient Permissions");
    }

    /**
     * The getBundleRepository function is an internal helper function to make the backend controller unit test
     * possible without mocking the whole shopware project.
     * @return Shopware\CustomModels\Bundle\Repository
     */
    public function getBundleRepository()
    {
        if ($this->bundleRepository === null) {
            $this->bundleRepository = Shopware()->Models()->getRepository('Shopware\CustomModels\Bundle\Bundle');
        }
        return $this->bundleRepository;
    }

    /**
     * Global interface to get an offset of defined bundles.
     * The getListAction expects the standard listing parameters directly in the request parameters start, limit, filter and sort.
     * @return boolean The function assigns the result to the controller view.
     */
    public function getListAction()
    {

        $this->View()->assign(
            $this->getList(
                $this->Request()->getParam('articleId', null),
                $this->Request()->getParam('filter', array()),
                $this->Request()->getParam('sort', array()),
                $this->Request()->getParam('start', null),
                $this->Request()->getParam('limit', null)
            )
        );
        return true;
    }


    /**
     * Global interface to get an offset of defined bundles with the whole bundle data.
     * The getFullListAction expects the standard listing parameters directly in the request parameters start, limit, filter and sort.
     * @return boolean The function assigns the result to the controller view.
     */
    public function getFullListAction()
    {
        $this->View()->assign(
            $this->getFullList(
                $this->Request()->getParam('filter', array()),
                $this->Request()->getParam('sort', array()),
                $this->Request()->getParam('start', null),
                $this->Request()->getParam('limit', null)
            )
        );
        return true;
    }

    /**
     * Global interface to get the whole data for a single article bundle.
     * The getDetailAction expects the bundle id in the request parameters.
     * @return bool
     */
    public function getDetailAction()
    {
        $this->View()->assign(
            $this->getDetail(
                $this->Request()->getParam('id', null)
            )
        );
        return true;
    }

    /**
     * Global interface to create a new bundle.
     * The createBundleAction expects the bundle data directly in the request parameters.
     * <code>
     * Example:
     * $this->Request()->getParams() => Whole bundle data
     * </code>
     * @return boolean The function assigns the result to the controller view.
     */
    public function createBundleAction()
    {
        $this->View()->assign(
            $this->saveBundle(
                $this->Request()->getParams()
            )
        );
        return true;
    }

    /**
     * Global interface to update an existing bundle.
     * The updateBundleAction expects the bundle data directly in the request parameters.
     * <code>
     * Example:
     * $this->Request()->getParams() => Whole bundle data
     * </code>
     * @return boolean The function assigns the result to the controller view.
     */
    public function updateBundleAction()
    {
        $this->View()->assign(
            $this->saveBundle(
                $this->Request()->getParams()
            )
        );
        return true;
    }

    /**
     * Global interface to update an existing bundle.
     * The updateBundleAction expects the bundle data directly in the request parameters.
     * <code>
     * Example:
     * $this->Request()->getParams() => Whole bundle data
     * </code>
     * @return boolean The function assigns the result to the controller view.
     */
    public function deleteBundleAction()
    {
        $this->View()->assign(
            $this->deleteBundle(
                $this->Request()->getParam('bundles', array(array('id' => (int) $this->Request()->getParam('id'))))
            )
        );
        return true;
    }

    /**
     * Global interface which used for a custom article suggest search component.
     * Returns an offset of articles.
     *
     * @return array The function assigns the result to the controller view.
     */
    public function searchArticleAction()
    {
        $filters = $this->Request()->getParam('filter');

        $this->View()->assign(
            $this->searchArticle(
                $filters[0]['value']
            )
        );
        return true;
    }

    /**
     * Global interface which used for the bundle backend extension of the article module.
     * Returns an offset of article variants for the passed article id.
     * @return bool
     */
    public function getVariantsAction() {
        $this->View()->assign(
            $this->getVariants(
                $this->Request()->getParam('articleId'),
                $this->Request()->getParam('start', 0),
                $this->Request()->getParam('limit', 20)
            )
        );
        return true;
    }

    /**
     * Global interface to validate a bundle order number.
     * Used as remote validation for the number field of the backend module.
     * Expects the number in the "value" request parameter and a optional bundle id
     * parameter in the "param" key.
     */
    public function validateNumberAction() {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();

        echo $this->validateNumber(
            $this->Request()->getParam('value'),
            $this->Request()->getParam('param')
        );
    }


    /**
     * Internal helper function to get an offset of defined bundles.
     * The getFullListAction expects the standard listing parameters directly in the request parameters start, limit, filter and sort.
     *
     * @param array $filter     An array of listing filters to filter the result set
     * @param array $sort       An array of listing order by condition to sort the result set
     * @param int   $offset     An offset for a paginated listing.
     * @param int   $limit      An limit for a paginated listing.
     *
     * @return array Result of the listing query or the exception code and message
     */
    protected function getFullList($filter, $sort, $offset, $limit)
    {
        try {
            if (!empty($filter) && $filter[0]['property'] == 'free')  {
                $filter = array(
                    array('property' => 'bundle.name', 'value' => $filter[0]['value'], 'operator' => 'LIKE'),
                    array('property' => 'bundle.number', 'value' => $filter[0]['value'], 'operator' => 'LIKE'),
                    array('property' => 'article.name', 'value' => $filter[0]['value'], 'operator' => 'LIKE'),
                    array('property' => 'articleMainDetail.number', 'value' => $filter[0]['value'], 'operator' => 'LIKE')
                );
            }

            /**@var $query \Doctrine\ORM\Query*/
            $query = $this->getBundleRepository()->getFullListQuery($filter, $sort, $offset, $limit);

            $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

            $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

            //returns the total count of the query
            $total = $paginator->count();

            $result = $paginator->getIterator()->getArrayCopy();

            foreach($result as &$bundle) {
                $tax = $bundle['article']['tax'];
                foreach($bundle['prices'] as &$price) {
                    $price['net'] = $price['price'];
                    if ($price['customerGroup']['taxInput'] && $bundle['discountType'] == 'abs') {
                        $price['price'] = $price['price'] / 100 * (100 + $tax['tax']);
                    }
                }
            }

            return array(
                'success' => true,
                'data' => $result,
                'total' => $total
            );
        }
        catch (Exception $e) {
            return array(
                'success' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            );
        }
    }


    /**
     * Internal helper function to get an offset of defined bundles.
     * The getListAction expects the standard listing parameters directly in the request parameters start, limit, filter and sort.
     *
     * @param int   $articleId  Identifier for the article
     * @param array $filter     An array of listing filters to filter the result set
     * @param array $sort       An array of listing order by condition to sort the result set
     * @param int   $offset     An offset for a paginated listing.
     * @param int   $limit      An limit for a paginated listing.
     *
     * @return array Result of the listing query or the exception code and message
     */
    protected function getList($articleId, $filter, $sort, $offset, $limit)
    {
        try {
            /**@var $query \Doctrine\ORM\Query*/
            $query = $this->getBundleRepository()->getArticleBundlesQuery($articleId, $filter, $sort, $offset, $limit);

            $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

            $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

            //returns the total count of the query
            $total = $paginator->count();

            $result = $paginator->getIterator()->getArrayCopy();

            return array(
                'success' => true,
                'data' => $result,
                'total' => $total
            );
        }
        catch (Exception $e) {
            return array(
                'success' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Internal function to get the whole data for a single bundle. The bundle will be identified over the
     * passed id parameter. The second parameter "$hydrationMode" can be use to control the result data type.
     *
     * @param     $id
     * @param int $hydrationMode
     *
     * @return array
     */
    protected function getDetail($id, $hydrationMode = \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY)
    {
        try {
            $query = $this->getBundleRepository()
                          ->getBundleQuery($id);

            $query->setHydrationMode($hydrationMode);

            $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

            $bundle = $paginator->getIterator()->getArrayCopy();
            $bundle = $bundle[0];

            foreach($bundle['articles'] as &$article) {
                //we have to convert the bundle article prices to gross prices
                $tax = $article['articleDetail']['article']['tax'];
                foreach($article['articleDetail']['prices'] as &$price) {
                    $price['net'] = $price['price'];
                    if ($price['customerGroup']['taxInput']) {
                        $price['price'] = $price['price'] / 100 * (100 + $tax['tax']);
                    }
                }
            }

            if ($bundle['discountType'] === 'abs') {
                $tax = $bundle['article']['tax'];
                foreach($bundle['prices'] as &$price) {
                    $price['net'] = $price['price'];
                    if ($price['customerGroup']['taxInput']) {
                        $price['price'] = $price['price'] / 100 * (100 + $tax['tax']);
                    }
                }
            }

            return array(
                'success' => true,
                'data' => $bundle
            );
        }
        catch (Exception $e) {
            return array(
                'success' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Internal function to save a bundle. Used from the createBundleAction and updateBundleAction interface.
     * Contains the whole source code logic to save a single bundle.
     *
     * @param  array $data The whole bundle data as array
     * @return array       Result of the delete process
     */
    protected function saveBundle($data)
    {
        try {
			/**@var $bundle \Shopware\CustomModels\Bundle\Bundle*/
			if (empty($data['id'])) {
				$bundle = new Shopware\CustomModels\Bundle\Bundle();
                Shopware()->Models()->persist($bundle);
                $data['created'] = new DateTime();
			} else {
				$bundle = Shopware()->Models()->find('Shopware\CustomModels\Bundle\Bundle', $data['id']);
                unset($data['created']);
			}
 
			$data = $this->prepareBundleData($data);

            //prepare bundle data thrown an exception or not all required parameters passed.
            if (isset($data['success']) && $data['success'] === false) {
                return $data;
            }

			$bundle->fromArray($data);
			Shopware()->Models()->flush();

            $data = $this->getDetail($bundle->getId());

            return array(
				'success' => true,
				'data' => $data['data']
			);

        } catch (Exception $e) {
            return array(
                'success' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Internal helper function which used for the article suggest search in the bundle backend
     * extension of the article module.
     *
     * @param $searchValue
     *
     * @return array
     */
    protected function searchArticle($searchValue)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('details', 'prices', 'customerGroup', 'article', 'tax'))
                ->from('Shopware\Models\Article\Detail', 'details')
                ->innerJoin('details.article', 'article')
                ->innerJoin('details.prices', 'prices')
                ->innerJoin('prices.customerGroup', 'customerGroup')
                ->innerJoin('article.tax', 'tax')
                ->where('article.name LIKE :searchValue')
                ->orWhere('details.number LIKE :searchValue')
                ->orWhere('article.description LIKE :searchValue')
                ->setParameters(array('searchValue' => $searchValue))
                ->setFirstResult(0)
                ->setMaxResults(10);

        $query = $builder->getQuery();

        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

        $articles = $paginator->getIterator()->getArrayCopy();

        foreach($articles as &$article) {
            $tax = $article['article']['tax'];
            foreach($article['prices'] as &$price) {
                if ($price['customerGroup']['taxInput']) {
                    $price['price'] = $price['price'] / 100 * (100 + $tax['tax']);
                }
            }
        }

        return array(
            'success' => true,
            'data' => $articles
        );
    }

    /**
     * Internal helper function which returns an offset of variants for the passed article id.
     *
     * @param $articleId int
     * @param $offset int
     * @param $limit int
     *
     * @return array
     */
    protected function getVariants($articleId, $offset, $limit)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('details'))
                ->from('Shopware\Models\Article\Detail', 'details')
                ->where('details.articleId = :articleId')
                ->setParameters(array('articleId' => $articleId))
                ->setFirstResult($offset)
                ->setMaxResults($limit);

        $query = $builder->getQuery();

        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

        return array(
            'success' => true,
            'total' => $paginator->count(),
            'data' => $paginator->getIterator()->getArrayCopy(),
        );
    }

	/**
	 * Internal helper function to prepare the associated data of a single bundle resource.
	 * @param array $data
	 * @return array $data
	 */
	protected function prepareBundleData($data)
	{
        if (!empty($data['articleId'])) {
            $data['article'] = Shopware()->Models()->find('Shopware\Models\Article\Article', $data['articleId']);
        } else {
            return array(
                'success' => false,
                'noArticleId' => true
            );
        }

        if (!empty($data['articles'])) {
            foreach($data['articles'] as &$articleData) {
                if (!empty($articleData['articleDetailId'])) {
                    $articleData['articleDetail'] = $this->getArticleDetailRepository()->find(
                        (int) $articleData['articleDetailId']
                    );
                }
            }
        }

        if (!empty($data['customerGroups'])) {
            $customerGroups = array();
            foreach($data['customerGroups'] as $groupData) {
                if (!empty($groupData['id'])) {
                    $customerGroups[] = Shopware()->Models()->find('Shopware\Models\Customer\Group', $groupData['id']);
                }
            }
            $data['customerGroups'] = $customerGroups;
        }
 
        /**@var $tax \Shopware\Models\Tax\Tax*/
        $tax = $data['article']->getTax();
        $data['tax'] = $tax;

        if (!empty($data['prices'])) {
            $prices = array();
            foreach($data['prices'] as $priceData) {
                /**@var $customerGroup \Shopware\Models\Customer\Group*/
                $customerGroup = $this->getCustomerGroupRepository()->find(
                    $priceData['customerGroup'][0]['id']
                );

                if ($data['discountType'] === 'abs' && $customerGroup->getTaxInput()) {
                    $priceData['price'] = ($priceData['price'] / (100 + $tax->getTax())) * 100;
                }
                $priceData['customerGroup'] = $customerGroup;

                $prices[] = $priceData;
            }
            $data['prices'] = $prices;
        }

        if (!empty($data['limitedDetails'])) {
            $limitedDetails = array();
            foreach($data['limitedDetails'] as $limitData) {
                $limitedDetails[] = $this->getArticleDetailRepository()->find(
                    $limitData['id']
                );
            }
            $data['limitedDetails'] = $limitedDetails;
        }

		return $data;
	}

    /**
     * Internal function to delete a bundle. Used from the deleteBundleAction interface.
     * Contains the whole source code logic to delete a single bundle.
     *
     * @param  array $bundles An array of bundle ids.
     * @return array Result of the delete process
     */
    protected function deleteBundle($bundles)
    {
        try {
            foreach($bundles as $bundleId) {
                /**@var $bundle \Shopware\CustomModels\Bundle\Bundle*/
                $bundle = Shopware()->Models()->find('Shopware\CustomModels\Bundle\Bundle', $bundleId);
                Shopware()->Models()->remove($bundle);
            }
			Shopware()->Models()->flush();

            return array(
				'success' => true,
				'data' => $bundles
			);
        }
        catch (Exception $e) {
            return array(
                'success' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Internal function which validates the passed order number for article bundles.
     * Each bundle order number can only be defined one time.
     * Returns true if the passed number is unique.
     *
     * @param string $number Number to validate
     * @param int $bundleId Optional bundle id, sent to exclude an existing bundle
     * @return boolean
     */
    protected function validateNumber($number, $bundleId = null)
    {
        $parameters = array('number' => $number);

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('bundle'))
                ->from('Shopware\CustomModels\Bundle\Bundle', 'bundle')
                ->where('bundle.number = :number');

        if ($bundleId !== null) {
            $builder->andWhere('bundle.id != :bundleId');
            $parameters['bundleId'] = $bundleId;
        }
        $builder->setParameters($parameters);
        $result = $builder->getQuery()->getArrayResult();

        return empty($result);
    }


}
