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

namespace Shopware\Bundle\ContentTypeBundle\Controller\Frontend;

use Shopware\Bundle\ContentTypeBundle\Field\TemplateProvidingFieldInterface;
use Shopware\Bundle\ContentTypeBundle\Services\RepositoryInterface;
use Shopware\Bundle\ContentTypeBundle\Structs\Criteria;
use Shopware\Bundle\ContentTypeBundle\Structs\Type;

class ContentType extends \Enlight_Controller_Action
{
    private const LIMIT = 10;

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var Type
     */
    private $type;

    public function __construct(RepositoryInterface $repository, Type $type)
    {
        $this->repository = $repository;
        parent::__construct();
        $this->type = $type;
    }

    public function indexAction(int $p = 1): void
    {
        $criteria = new Criteria();
        $criteria->offset = ($p - 1) * self::LIMIT;
        $criteria->limit = self::LIMIT;
        $criteria->sort = [['property' => 'id', 'direction' => 'DESC']];

        $this->get('events')->notify(sprintf('Content_Type_Frontend_Criteria_Creation_%s', $this->type->getInternalName()), [
            'subject' => $this,
            'criteria' => $criteria,
        ]);

        $result = $this->repository->findAll($criteria);

        $this->View()->assign('sPage', $p);
        $this->View()->assign('pages', ceil($result->total / self::LIMIT));
        $this->View()->assign('sItems', $result->items);
        $this->View()->assign('sTotal', $result->total);
        $this->View()->assign('sBreadcrumb', $this->getBreadcrumb());
    }

    public function detailAction(int $id = 1): void
    {
        $criteria = new Criteria();
        $criteria->limit = 1;
        $criteria->filter = [['property' => 'id', 'value' => $id]];

        $result = $this->repository->findAll($criteria);

        if (count($result->items) === 0) {
            throw new \Enlight_Controller_Exception(sprintf('Cannot find element with id %d of type \'%s\'', $id, $this->type->getInternalName()));
        }

        $item = current($result->items);

        $this->View()->assign('sItem', $item);
        $this->View()->assign('sBreadcrumb', $this->getBreadcrumb($item));
    }

    public function postDispatch()
    {
        parent::postDispatch();

        $this->View()->assign('sType', $this->type);
        $this->View()->assign('sFields', $this->getFields());
        $this->View()->assign('sAction', $this->Request()->getActionName());
        $this->View()->assign('sTitleKey', $this->type->getViewTitleFieldName());
        $this->View()->assign('sMetaTitleKey', $this->type->getViewMetaTitleFieldName());
        $this->View()->assign('sDescriptionKey', $this->type->getViewDescriptionFieldName());
        $this->View()->assign('sMetaDescriptionKey', $this->type->getViewMetaDescriptionFieldName());
        $this->View()->assign('sImageKey', $this->type->getViewImageFieldName());

        if (!$this->View()->templateExists($this->View()->Template()->template_resource)) {
            if ($this->Request()->getActionName() === 'index') {
                $this->View()->Template()->template_resource = 'frontend/content_type/index.tpl';

                return;
            }

            $this->View()->Template()->template_resource = 'frontend/content_type/detail.tpl';
        }
    }

    public function getBreadcrumb(array $item = null): array
    {
        $breadCrumb = [
            [
                'id' => $this->type->getInternalName(),
                'name' => $this->type->getName(),
                'blog' => false,
                'link' => 'shopware.php?sViewport=' . $this->type->getControllerName() . '&sAction=index',
            ],
        ];

        if ($item) {
            $breadCrumb[] = [
                'id' => $this->type->getInternalName(),
                'name' => $item[$this->type->getViewMetaTitleFieldName()] ?? $item[$this->type->getViewTitleFieldName()],
                'blog' => false,
                'link' => 'shopware.php?sViewport=' . $this->type->getControllerName() . '&sAction=detail&&id=' . $item['id'],
            ];
        }

        return $breadCrumb;
    }

    private function getFields(): array
    {
        $fields = [];

        foreach ($this->type->getFields() as $field) {
            if (!$field->getType() instanceof TemplateProvidingFieldInterface) {
                continue;
            }

            $tmpField = $field->jsonSerialize();
            $tmpField['template'] = $field->getType()::getTemplate();

            $fields[] = $tmpField;
        }

        return $fields;
    }
}
