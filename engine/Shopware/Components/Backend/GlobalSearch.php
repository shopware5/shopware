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

namespace Shopware\Components\Backend;

use Shopware\Bundle\AttributeBundle\Repository\RepositoryInterface;
use Shopware\Bundle\AttributeBundle\Repository\SearchCriteria;
use Shopware\Models\Article\Article;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Order\Order;

class GlobalSearch
{
    /**
     * @var RepositoryInterface
     */
    private $productRepository;

    /**
     * @var RepositoryInterface
     */
    private $customerRepository;

    /**
     * @var RepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        RepositoryInterface $productRepository,
        RepositoryInterface $customerRepository,
        RepositoryInterface $orderRepository
    ) {
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param string $term
     *
     * @return array
     */
    public function search($term)
    {
        return [
            'articles' => $this->getArticles($term),
            'customers' => $this->getCustomers($term),
            'orders' => $this->getOrders($term),
        ];
    }

    /**
     * @param string $term
     *
     * @return array
     */
    private function getArticles($term)
    {
        $criteria = new SearchCriteria(Article::class);
        $criteria->term = $term;
        $criteria->limit = 5;

        $result = $this->productRepository->search($criteria);

        return $result->getData();
    }

    /**
     * @param string $term
     *
     * @return array
     */
    private function getCustomers($term)
    {
        $criteria = new SearchCriteria(Customer::class);
        $criteria->term = $term;
        $criteria->limit = 5;

        $result = $this->customerRepository->search($criteria);

        return $result->getData();
    }

    /**
     * @param string $term
     *
     * @return array
     */
    private function getOrders($term)
    {
        $criteria = new SearchCriteria(Order::class);
        $criteria->term = $term;
        $criteria->limit = 5;

        $result = $this->orderRepository->search($criteria);

        return $result->getData();
    }
}
