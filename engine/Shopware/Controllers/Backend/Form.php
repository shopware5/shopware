<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Shopware\Bundle\AttributeBundle\Service\DataPersister;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Form\Field;
use Shopware\Models\Form\Form;
use Shopware\Models\Form\Repository as FormRepository;

class Shopware_Controllers_Backend_Form extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var FormRepository
     */
    public $repository;

    /**
     * Contains the shopware model manager
     *
     * @var ModelManager
     */
    public $manager;

    /**
     * Returns available forms
     */
    public function getFormsAction()
    {
        // if id is provided return a single form instead of a collection
        if ($id = $this->Request()->getParam('id')) {
            return $this->getSingleForm($id);
        }

        $offset = $this->Request()->getParam('start');
        $limit = $this->Request()->getParam('limit', 20);
        $filter = $this->prefixProperties($this->Request()->getParam('filter', []), 'form');
        $order = $this->prefixProperties($this->Request()->getParam('sort', []), 'form');

        $query = $this->getRepository()->getListQuery($filter, $order, $offset, $limit);

        // returns the total count of the query
        $totalResult = $this->getManager()->getQueryCount($query);

        $forms = $query->getArrayResult();

        foreach ($forms as &$form) {
            $form['shopIds'] = $this->explodeShopIds($form['shopIds']);
        }

        $this->View()->assign(['success' => true, 'data' => $forms, 'total' => $totalResult]);
    }

    /**
     * Creates new form
     *
     * @throws OptimisticLockException
     * @throws ORMInvalidArgumentException
     */
    public function createFormAction()
    {
        $params = $this->Request()->getParams();

        $formModel = new Form();
        $params['shopIds'] = $params['shopIds'] ? '|' . implode('|', $params['shopIds']) . '|' : null;

        $formModel->fromArray($params);

        $this->getManager()->persist($formModel);
        $this->getManager()->flush();

        $data = $this->getManager()->toArray($formModel);
        $data['shopIds'] = $this->explodeShopIds($data['shopIds']);

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Updates form
     *
     * @throws OptimisticLockException
     * @throws ORMInvalidArgumentException
     */
    public function updateFormAction()
    {
        if (!($id = $this->Request()->getParam('id'))) {
            $this->View()->assign(['success' => false, 'message' => 'No valid form Id']);

            return;
        }

        $result = $this->getRepository()->find($id);
        if (!$result instanceof Form) {
            $this->View()->assign(['success' => false, 'message' => 'Form not found']);

            return;
        }

        $params = $this->Request()->getParams();

        // unset fields - fields should only be updated via the updateField-Action
        unset($params['fields']);

        $params['shopIds'] = $params['shopIds'] ? '|' . implode('|', $params['shopIds']) . '|' : null;

        $result->fromArray($params);
        $this->getManager()->persist($result);
        $this->getManager()->flush();

        $this->getSingleForm($id);
    }

    public function removeFormAction()
    {
        $id = (int) $this->Request()->getParam('id');
        if ($id === 0) {
            $this->View()->assign(['success' => false, 'message' => 'No valid form Id']);

            return;
        }

        $result = $this->getRepository()->find($id);
        if (!$result instanceof Form) {
            $this->View()->assign(['success' => false, 'message' => 'Form not found']);

            return;
        }

        $this->getManager()->remove($result);
        $this->getManager()->flush();

        $this->View()->assign(['success' => true]);
    }

    /**
     * Copies form
     *
     * @throws Exception
     * @throws OptimisticLockException
     * @throws ORMInvalidArgumentException
     */
    public function copyFormAction()
    {
        $id = (int) $this->Request()->getParam('id');
        if ($id === 0) {
            $this->View()->assign(['success' => false, 'message' => 'No valid form Id']);

            return;
        }

        $result = $this->getRepository()->find($id);
        if (!$result instanceof Form) {
            $this->View()->assign(['success' => false, 'message' => 'Form not found']);

            return;
        }

        $clonedForm = $result->getClone();
        $clonedForm->setName('Copy of ' . $result->getName());

        $this->getManager()->persist($clonedForm);
        $this->getManager()->flush();

        $persister = Shopware()->Container()->get(DataPersister::class);
        $persister->cloneAttribute('s_cms_support_attributes', $id, $clonedForm->getId());

        $this->View()->assign(['success' => true]);
    }

    public function getFieldsAction()
    {
        $id = (int) $this->Request()->getParam('formId');
        if ($id === 0) {
            $this->View()->assign(['success' => false, 'message' => 'No valid form Id']);

            return;
        }

        $result = $this->getManager()->getRepository(Field::class)->findBy(
            ['formId' => $id],
            ['position' => 'ASC']
        );

        $resultArray = $this->getManager()->toArray($result);

        $this->View()->assign(['success' => true, 'data' => $resultArray, 'total' => \count($resultArray)]);
    }

    public function updateFieldAction()
    {
        $id = (int) $this->Request()->getParam('id');
        if ($id === 0) {
            $this->View()->assign(['success' => false, 'message' => 'No valid field Id']);

            return;
        }

        $result = $this->getManager()->getRepository(Field::class)->find($id);
        if (!$result instanceof Field) {
            $this->View()->assign(['success' => false, 'message' => 'Field not found']);

            return;
        }

        $params = $this->Request()->getParams();
        $result->fromArray($params);

        $this->getManager()->persist($result);
        $this->getManager()->flush();

        $data = $this->getManager()->toArray($result);
        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    public function createFieldAction()
    {
        $id = (int) $this->Request()->getParam('formId');
        if ($id === 0) {
            $this->View()->assign(['success' => false, 'message' => 'No valid form Id']);

            return;
        }

        $form = $this->getRepository()->find($id);
        if (!$form instanceof Form) {
            $this->View()->assign(['success' => false, 'message' => 'Form not found']);

            return;
        }

        $params = $this->Request()->getParams();

        $fieldModel = new Field();
        $fieldModel->fromArray($params);
        $fieldModel->setForm($form);

        $this->getManager()->persist($fieldModel);
        $this->getManager()->flush();

        $data = $this->getManager()->toArray($fieldModel);
        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    public function removeFieldAction()
    {
        $id = (int) $this->Request()->getParam('id');
        if ($id === 0) {
            $this->View()->assign(['success' => false, 'message' => 'No valid field Id']);

            return;
        }

        $result = $this->getManager()->find(Field::class, $id);
        if (!$result instanceof Field) {
            $this->View()->assign(['success' => false, 'message' => 'Field not found']);

            return;
        }

        $this->getManager()->remove($result);
        $this->getManager()->flush();

        $this->View()->assign(['success' => true]);
    }

    /**
     * Changes Position of field
     */
    public function changeFieldPositionAction()
    {
        $data = $this->Request()->getParam('data');
        $positions = json_decode($data);

        $qb = $this->getManager()->createQueryBuilder();
        $qb->update(Field::class, 'field')
            ->andWhere('field.id = :fieldId');

        foreach ($positions as $position => $fieldid) {
            $qb->set('field.position', $position)
                ->setParameter('fieldId', $fieldid)
                ->getQuery()
                ->execute();
        }

        $this->View()->assign(['success' => true]);
    }

    /**
     * Method to define acl dependencies in backend controllers
     */
    protected function initAcl()
    {
        $this->addAclPermission('index', 'read');
        $this->addAclPermission('getForms', 'read');
        $this->addAclPermission('createForm', 'createupdate');
        $this->addAclPermission('updateForm', 'createupdate');
        $this->addAclPermission('removeForm', 'delete');
        $this->addAclPermission('copyForm', 'createupdate');

        $this->addAclPermission('removeField', 'createupdate');
        $this->addAclPermission('getFields', 'read');
        $this->addAclPermission('createField', 'createupdate');
        $this->addAclPermission('updateField', 'createupdate');
        $this->addAclPermission('changeFieldPosition', 'createupdate');
    }

    /**
     * Gets a single form incl. it's fields
     *
     * @param int      $id     Mandatory form id
     * @param int|null $shopId If specified, the form will be fetched from a specific store
     */
    protected function getSingleForm($id, $shopId = null)
    {
        $data = $this->getRepository()->getFormQuery($id, $shopId)->getArrayResult();

        foreach ($data as &$form) {
            $form['shopIds'] = $this->explodeShopIds($form['shopIds']);
        }

        if (empty($data)) {
            $this->View()->assign(['success' => false, 'message' => 'Form not found']);

            return;
        }

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => 1]);
    }

    /**
     * Helper method to prefix properties
     *
     * @param string $prefix
     *
     * @return array
     */
    protected function prefixProperties(array $properties = [], $prefix = '')
    {
        foreach ($properties as $key => $property) {
            if (isset($property['property'])) {
                $properties[$key]['property'] = $prefix . '.' . $property['property'];
            }
        }

        return $properties;
    }

    /**
     * Internal helper function to get access to the entity manager.
     */
    private function getManager(): ModelManager
    {
        if ($this->manager === null) {
            $this->manager = $this->get('models');
        }

        return $this->manager;
    }

    /**
     * Internal helper function to get access to the form repository.
     */
    private function getRepository(): FormRepository
    {
        if ($this->repository === null) {
            $this->repository = $this->getManager()->getRepository(Form::class);
        }

        return $this->repository;
    }

    /**
     * Gets a | delimited and separated shop id list
     * and converts it into an array of integer
     *
     * @return int[] The list of shop ids
     */
    private function explodeShopIds(?string $shopIds): array
    {
        if (empty($shopIds)) {
            return [];
        }

        $explodedShopIds = explode('|', trim($shopIds, '|'));

        return array_map('\intval', $explodedShopIds);
    }
}
