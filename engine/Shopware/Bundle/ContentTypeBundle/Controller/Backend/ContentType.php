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

namespace Shopware\Bundle\ContentTypeBundle\Controller\Backend;

use Shopware\Bundle\ContentTypeBundle\Services\ExtjsBuilderInterface;
use Shopware\Bundle\ContentTypeBundle\Services\RepositoryInterface;
use Shopware\Bundle\ContentTypeBundle\Structs\Criteria;
use Shopware\Bundle\ContentTypeBundle\Structs\Type;
use Shopware\Components\DependencyInjection\Container;

class ContentType extends \Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var Type
     */
    protected $type;

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var ExtjsBuilderInterface
     */
    private $extjsBuilder;

    public function __construct(ExtjsBuilderInterface $extjsBuilder, Type $type, RepositoryInterface $repository)
    {
        parent::__construct();
        $this->extjsBuilder = $extjsBuilder;
        $this->type = $type;
        $this->repository = $repository;
    }

    public function setContainer(Container $loader = null)
    {
        parent::setContainer($loader);

        $this->get('events')->addListener('Enlight_Controller_Plugin_ScriptRenderer_formatControllerName', static function () {
            return 'ContentType';
        });
    }

    public function preDispatch(): void
    {
        parent::preDispatch();
        $this->View()->Engine()->setCompileId($this->View()->Engine()->getCompileId() . '_' . $this->Request()->getControllerName());
    }

    public function indexAction(): void
    {
        parent::indexAction();
        $this->View()->loadTemplate('backend/content_type/app.js');
    }

    public function postDispatch(): void
    {
        parent::postDispatch();

        if (in_array(strtolower($this->Request()->getActionName()), ['index', 'load', 'extends'])) {
            $this->View()->assign('controllerName', $this->Request()->getControllerName());
            $this->View()->assign('modelFields', $this->extjsBuilder->buildModelFields($this->type));
            $this->View()->assign('listColumns', $this->extjsBuilder->buildColumns($this->type));
            $this->View()->assign('detailFields', $this->extjsBuilder->buildFieldSets($this->type));
            $this->View()->assign('type', $this->type);
        }
    }

    public function listAction(int $start = 0, int $limit = 20, array $sort = [], array $filter = []): void
    {
        $criteria = new Criteria();
        $criteria->offset = $start;
        $criteria->limit = $limit;
        $criteria->sort = $sort;
        $criteria->filter = $filter;
        $criteria->loadAssociations = false;
        $criteria->loadTranslations = false;

        $result = $this->repository->findAll($criteria);

        $this->View()->assign(
            [
                'data' => $result->items,
                'total' => $result->total,
                'success' => true,
            ]
        );
    }

    public function detailAction(int $id): void
    {
        $this->View()->assign(
            $this->getDetail($id)
        );
    }

    public function createAction(): void
    {
        $id = $this->repository->save(
            $this->Request()->getParams()
        );

        $this->View()->assign(
            $this->getDetail($id)
        );
    }

    public function updateAction(int $id): void
    {
        $this->repository->save(
            $this->Request()->getParams(),
            $id
        );

        $this->View()->assign(
            $this->getDetail($id)
        );
    }

    public function getDetail(int $id): array
    {
        $criteria = new Criteria();
        $criteria->limit = 1;
        $criteria->filter = [['property' => 'id', 'value' => $id]];
        $criteria->loadAssociations = false;
        $criteria->loadTranslations = false;

        $result = $this->repository->findAll($criteria)->items;

        return ['success' => true, 'data' => current($result)];
    }

    public function deleteAction(int $id): void
    {
        $this->repository->delete($id);

        $this->View()->assign(['success' => true]);
    }

    protected function initAcl(): void
    {
        $this->addAclPermission('index', 'read', 'Insufficient permissions');
        $this->addAclPermission('load', 'read', 'Insufficient permissions');
        $this->addAclPermission('list', 'read', 'Insufficient permissions');
        $this->addAclPermission('detail', 'read', 'Insufficient permissions');
        $this->addAclPermission('create', 'create', 'Insufficient permissions');
        $this->addAclPermission('update', 'edit', 'Insufficient permissions');
        $this->addAclPermission('delete', 'delete', 'Insufficient permissions');
    }
}
