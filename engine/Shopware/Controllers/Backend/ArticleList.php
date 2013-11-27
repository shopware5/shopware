<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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
 * Backend Controller for the article list backend module
 *
 * @category  Shopware
 * @package   Shopware\Controllers\Backend
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_ArticleList extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Internal helper function to get access to the article repository.
     *
     * @return Shopware\Models\Article\Repository
     */
    private function getDetailRepository()
    {
        return Shopware()->Models()->getRepository('Shopware\Models\Article\Detail');
    }

    /**
     * Event listener function of the article store of the backend module.
     *
     * @return mixed
     */
    public function updateAction()
    {
        $id = (int) $this->Request()->getParam('id');

        /** @var $articleDetail \Shopware\Models\Article\Detail   */
        $articleDetail = $this->getDetailRepository()->find($id);
        if (!$articleDetail instanceof \Shopware\Models\Article\Detail) {
            $this->View()->assign(array(
                'success' => false
            ));
            return;
        }

        $article = $articleDetail->getArticle();
        if (!$article instanceof \Shopware\Models\Article\Article) {
            $this->View()->assign(array(
               'success' => false,
               'message' => 'article not found',
            ));
            return;
        }

        $price = $this->Request()->getPost('price');
        if ($price) {
            $price = floatval(str_replace(',' , '.', $price));
            $tax = $article->getTax();
            if (!$tax instanceof \Shopware\Models\Tax\Tax) {
                $this->View()->assign(array(
                    'success' => false
                ));
                return;
            }

            $price = $price / (100 + $tax->getTax()) * 100;
            Shopware()->Db()->update(
                's_articles_prices',
                array('price' => $price),
                array(
                     'pricegroup = ?'       => 'EK',
                     'articleId = ?'        => $article->getId(),
                     'articledetailsID = ?' => $articleDetail->getId(),
                     '`to` LIKE ?'          => 'beliebig',
                )
            );
            Shopware()->Events()->notify(
                'Shopware_Plugins_HttpCache_InvalidateCacheId',
                array('cacheId' => 'a' . $article->getId())
            );
        }

        $number = $this->Request()->getPost('number');
        $articleDetail->setNumber($number);

        $name = $this->Request()->getPost('name');
        $article->setName($name);

        $inStock = $this->Request()->getPost('inStock');
        $articleDetail->setInStock($inStock);

        $active = $this->Request()->getPost('active');
        if ($articleDetail->getKind() == 1) {
            $article->setActive($active);
        }
        $articleDetail->setActive($active);

        Shopware()->Models()->flush();

        $this->View()->assign(array(
            'success' => true,
            'data'    => $this->Request()->getPost()
        ));
    }

    /**
     * Event listener function of the article store of the backend module.
     *
     * @return mixed
     */
    public function deleteAction()
    {
        $id = (int) $this->Request()->getParam('id');

        /** @var $articleDetail \Shopware\Models\Article\Detail   */
        $articleDetail = $this->getDetailRepository()->find($id);
        if (!is_object($articleDetail)) {
            $this->View()->assign(array(
                'success' => false
            ));
        }else {
            if ($articleDetail->getKind() == 1) {
                $article = $articleDetail->getArticle();
                $this->removePrices($article->getId());
                $this->removeArticleEsd($article->getId());
                $this->removeAttributes($article->getId());
                $this->removeArticleDetails($article);
                Shopware()->Models()->remove($article);
            } else {
                Shopware()->Models()->remove($articleDetail);
            }

            Shopware()->Models()->flush();

            $this->View()->assign(array(
                'success' => true
            ));
        }
    }

    /**
     * Internal helper function to remove all article prices quickly.
     * @param $articleId
     */
    private function removePrices($articleId)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->delete('Shopware\Models\Article\Price', 'prices')
                ->where('prices.articleId = :id')
                ->setParameter('id',$articleId)
                ->getQuery()
                ->execute();
    }

    /**
     * Internal helper function to remove the article attributes quickly.
     * @param $articleId
     */
    private function removeAttributes($articleId)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->delete('Shopware\Models\Attribute\Article', 'attribute')
                ->where('attribute.articleId = :id')
                ->setParameter('id',$articleId)
                ->getQuery()
                ->execute();
    }

    /**
     * Internal helper function to remove the detail esd configuration quickly.
     * @param $articleId
     */
    private function removeArticleEsd($articleId)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->delete('Shopware\Models\Article\Esd', 'esd')
                ->where('esd.articleId = :id')
                ->setParameter('id',$articleId)
                ->getQuery()
                ->execute();
    }

    /**
     * @param $article \Shopware\Models\Article\Article
     */
    private function removeArticleDetails($article)
    {
        $sql= "SELECT id FROM s_articles_details WHERE articleID = ? AND kind != 1";
        $details = Shopware()->Db()->fetchAll($sql, array($article->getId()));

        foreach($details as $detail) {
            $builder = Shopware()->Models()->createQueryBuilder();
            $builder->delete('Shopware\Models\Article\Image', 'image')
                    ->where('image.articleDetailId = :id')
                    ->setParameter('id', $detail['id'])
                    ->getQuery()
                    ->execute();

            $sql= "DELETE FROM s_article_configurator_option_relations WHERE article_id = ?";
            Shopware()->Db()->query($sql, array($detail['id']));

            $builder = Shopware()->Models()->createQueryBuilder();
            $builder->delete('Shopware\Models\Article\Detail', 'detail')
                    ->where('detail.id = :id')
                    ->setParameter('id', $detail['id'])
                    ->getQuery()
                    ->execute();
        }
    }

    public function listAction()
    {
		if (!$this->_isAllowed('read', 'article')) {
			/** @var $namespace Enlight_Components_Snippet_Namespace */
			$this->View()->assign(array(
				'success' => false,
				'data' => $this->Request()->getParams(),
				'message' => 'Insufficient permissions' )
			);
			return;
		}

		$categoryId   = $this->Request()->getParam('categoryId');
		$filterParams = $this->Request()->getParam('filter', array());
        $filterBy     = $this->Request()->getParam('filterBy');
		$showVariants = (bool) $this->Request()->getParam('showVariants', false);
        $order        = $this->Request()->getParam('sort', null);
        $start        = $this->Request()->getParam('start', 0);
        $limit        = $this->Request()->getParam('limit', 20);

		$filters = array();
        foreach ($filterParams as $singleFilter) {
			$filters[$singleFilter['property']] = $singleFilter['value'];
		}


		$categorySql = '';
        $imageSQL = '';
		$sqlParams = array();


		$filterSql = 'WHERE 1 = 1';
		if (isset($filters['search'])) {
			$filterSql .= " AND (details.ordernumber LIKE :orderNumber OR articles.name LIKE :articleName OR suppliers.name LIKE :supplierName OR details.suppliernumber LIKE :supplierNumber OR articles.description_long LIKE :descriptionLong)";
            $searchFilter =  '%' . $filters['search'] . '%';

			$sqlParams["orderNumber"] = $searchFilter;
			$sqlParams["articleName"] = $searchFilter;
			$sqlParams["supplierName"] = $searchFilter;
			$sqlParams["supplierNumber"] = $searchFilter;
			$sqlParams["descriptionLong"] = $searchFilter;
		}

        if ($filterBy == 'notInStock') {
            $filterSql .= " AND details.instock <= 0 ";
        }

        if ($filterBy == 'noCategory') {
            $categorySql = "
                    LEFT JOIN s_articles_categories_ro ac
					ON ac.articleID = articles.id
            ";

            $filterSql .= " AND ac.id IS NULL ";
        } elseif (!empty($categoryId) && $categoryId !== 'NaN') {
			$categorySql =  "
                LEFT JOIN s_categories c
                    ON  c.id = :categoryId

                INNER JOIN s_articles_categories_ro ac
                    ON  ac.articleID  = articles.id
                    AND ac.categoryID = c.id
			";
            $sqlParams["categoryId"] = $categoryId;
        }

        if ($filterBy == 'noImage') {
            $imageSQL = "
                LEFT JOIN s_articles_img as mainImages
                ON mainImages.articleID = articles.id
            ";

            $filterSql .= " AND mainImages.id IS NULL ";
        }


        // Make sure that whe don't get a cold here
		$columns = array('number', 'name', 'supplier', 'active', 'inStock', 'price', 'tax' );
		$directions = array('ASC', 'DESC');

		if (null === $order || !in_array($order[0]['property'] , $columns) || !in_array($order[0]['direction'], $directions)) {
			$order = 'id DESC';
		} else {
			$order = array_shift($order);
			$order = $order['property'] . ' ' . $order['direction'];
		}

        list($sqlParams, $filterSql, $categorySql, $imageSQL, $order) = Enlight()->Events()->filter(
            'Shopware_Controllers_Backend_ArticleList_SQLParts',
            array($sqlParams, $filterSql, $categorySql, $imageSQL, $order),
            array('subject' => $this)
        );

		if ($showVariants) {
            $sql = "
				SELECT DISTINCT SQL_CALC_FOUND_ROWS
				   details.id as id,
				   articles.id as articleId,
				   articles.name as name,
				   articles.configurator_set_id,
				   suppliers.name as supplier,
				   articles.active as active,
				   details.id as detailId,
				   details.additionaltext as additionalText,
				   details.instock as inStock,
				   details.ordernumber as number,
                   ROUND(prices.price*(100+tax.tax)/100,2) as `price`,
                   tax.tax as tax
				FROM
					s_articles_details as details
				INNER JOIN s_articles as articles
					ON details.articleID = articles.id
				LEFT JOIN s_articles_supplier as suppliers
					ON articles.supplierID = suppliers.id

                LEFT JOIN s_articles_prices prices
                    ON prices.articledetailsID = details.id
                    AND prices.`to`= 'beliebig'
                    AND prices.pricegroup='EK'

				LEFT JOIN s_core_tax AS tax
			        ON tax.id = articles.taxID

				$categorySql
				$imageSQL

				$filterSql
				AND details.kind <> 3
				ORDER BY $order, details.ordernumber ASC
				LIMIT  $start, $limit
			";
        } else {
			$sql = "
				SELECT DISTINCT SQL_CALC_FOUND_ROWS
				       details.id as id,
					   articles.id as articleId,
					   articles.name as name,
					   articles.configurator_set_id,
					   suppliers.name as supplier,
					   articles.active as active,
					   details.id as detailId,
					   details.additionaltext as additionalText,
					   details.instock as inStock,
					   details.ordernumber as number,
					   ROUND(prices.price*(100+tax.tax)/100,2) as `price`,
					   tax.tax as tax

				FROM s_articles as articles

				INNER JOIN s_articles_details as details
					ON articles.main_detail_id = details.id

				LEFT JOIN s_articles_supplier as suppliers
					ON articles.supplierID = suppliers.id

                LEFT JOIN s_articles_prices prices
                    ON prices.articledetailsID = details.id
                    AND prices.`to`= 'beliebig'
                    AND prices.pricegroup='EK'

				LEFT JOIN s_core_tax AS tax
			        ON tax.id = articles.taxID

				$categorySql
				$imageSQL
				$filterSql

				ORDER BY $order, details.ordernumber ASC
				LIMIT  $start, $limit
			";
		}

        $sql = Enlight()->Events()->filter('Shopware_Controllers_Backend_ArticleList_ListSQL', $sql, array('subject' => $this, 'sqlParams' => $sqlParams));
		$articles = Shopware()->Db()->fetchAll($sql, $sqlParams);

		$sql= "SELECT FOUND_ROWS() as count";
		$count = Shopware()->Db()->fetchOne($sql);

		foreach ($articles as $key => $article) {
			// Check for configurator
			$isConfigurator = !empty($article['configurator_set_id']);
			$articles[$key]['hasConfigurator'] = ($isConfigurator !== false);

			// Check for Image
			$image = Shopware()->Db()->fetchOne(
				'SELECT img FROM s_articles_img WHERE articleID = ? AND main = 1 AND article_detail_id IS NULL',
				$article['articleId']
			);

			if ($image) {
				$articles[$key]['imageSrc']= $image . '_140x140.jpg';
			}

			// Check for Categories
			$hasCategories = Shopware()->Db()->fetchOne(
				'SELECT id FROM s_articles_categories_ro WHERE articleID = ?',
				$article['articleId']
			);
			$articles[$key]['hasCategories'] = ($hasCategories !== false);
		}

		$this->View()->assign(array(
			'success' => true,
			'data'    => $articles,
			'total'   => $count
		));
    }
}
