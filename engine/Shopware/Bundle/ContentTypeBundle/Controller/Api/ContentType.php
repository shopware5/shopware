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

namespace Shopware\Bundle\ContentTypeBundle\Controller\Api;

use Shopware\Bundle\ContentTypeBundle\Services\RepositoryInterface;
use Shopware\Bundle\ContentTypeBundle\Structs\Criteria;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\ShopRegistrationServiceInterface;
use Shopware\Models\Shop\Shop;

class ContentType extends \Shopware_Controllers_Api_Rest
{
    /**
     * @var RepositoryInterface
     */
    protected $repository;

    public function __construct(RepositoryInterface $repository, ModelManager $manager, ShopRegistrationServiceInterface $shopRegistrationService)
    {
        $this->repository = $repository;

        /** @var \Shopware\Models\Shop\Repository $shopRepository */
        $shopRepository = $manager->getRepository(Shop::class);
        $shopRegistrationService->registerShop($shopRepository->getDefault());
    }

    public function indexAction(
        int $start = 0,
        int $limit = 0,
        array $sort = [],
        array $filter = [],
        bool $resolve = false,
        bool $loadTranslations = false): void
    {
        $criteria = new Criteria();
        $criteria->offset = $start;
        $criteria->limit = $limit;
        $criteria->sort = $sort;
        $criteria->filter = $filter;
        $criteria->loadAssociations = $resolve;
        $criteria->loadTranslations = $loadTranslations;

        $result = $this->repository->findAll($criteria);

        $this->View()->assign('data', $result->items);
        $this->View()->assign('total', $result->total);
        $this->View()->assign('success', true);
    }

    public function getAction(int $id, bool $resolve = false, bool $loadTranslations = false): void
    {
        $filter = [['property' => 'id', 'value' => $id]];

        $criteria = new Criteria();
        $criteria->filter = $filter;
        $criteria->loadAssociations = $resolve;
        $criteria->loadTranslations = $loadTranslations;

        $result = current($this->repository->findAll($criteria)->items);

        if (!$result) {
            throw new NotFoundException(sprintf('Entity by id %d not found', $id));
        }

        $this->View()->assign('data', $result);
        $this->View()->assign('success', true);
    }

    public function postAction(): void
    {
        $params = $this->Request()->getPost();
        $result = [];
        $result['id'] = $this->repository->save($params);
        $result['location'] = $this->apiBaseUrl . $this->Request()->getControllerName() . '/' . $result['id'];

        $this->View()->assign($result);
        $this->Response()->setHeader('Location', $result['location']);
    }

    public function putAction(int $id): void
    {
        $params = $this->Request()->getPost();
        $result = [];
        $result['id'] = $this->repository->save($params, $id);
        $result['location'] = $this->apiBaseUrl . $this->Request()->getControllerName() . '/' . $result['id'];

        $this->View()->assign($result);
        $this->Response()->setHeader('Location', $result['location']);
    }

    public function deleteAction(int $id): void
    {
        $this->repository->delete($id);

        $this->View()->assign(['success' => true]);
    }
}
