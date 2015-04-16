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

use Shopware\Models\Form\Form;
use Shopware\Models\Form\Field;

/**
 * Shopware Backend Controller for the form module
 */
class Shopware_Controllers_Backend_Form extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var \Shopware\Models\Form\Repository
     * @access private
     */
    public $repository = null;

    /**
     * Contains the shopware model manager
     *
     * @var \Shopware\Components\Model\ModelManager
     */
    public $manager = null;

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
     * Internal helper function to get access to the entity manager.
     * @return null
     */
    private function getManager()
    {
        if ($this->manager === null) {
            $this->manager = Shopware()->Models();
        }
        return $this->manager;
    }

    /**
     * Internal helper function to get access to the form repository.
     *
     * @return Shopware\Models\Form\Repository
     */
    private function getRepository()
    {
        if ($this->repository === null) {
            $this->repository = Shopware()->Models()->getRepository('Shopware\Models\Form\Form');
        }
        return $this->repository;
    }

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
        $filter = $this->prefixProperties($this->Request()->getParam('filter', array()), 'form');
        $order = $this->prefixProperties($this->Request()->getParam('sort', array()), 'form');

        $query = $this->getRepository()->getListQuery($filter, $order, $offset, $limit);

        // returns the total count of the query
        $totalResult = $this->getManager()->getQueryCount($query);

        $forms = $query->getArrayResult();

        foreach ($forms as &$form) {
            $form['shopIds'] = $this->explodeShopIds($form['shopIds']);
        }

        $this->View()->assign(array('success' => true, 'data' => $forms, 'total' => $totalResult));
    }

    /**
     * Gets a single form incl. it's fields
     *
     * @param $id
     */
    protected function getSingleForm($id)
    {
        $data = $this->getRepository()->getFormQuery($id)->getArrayResult();

        foreach ($data as &$form) {
            $form['shopIds'] = $this->explodeShopIds($form['shopIds']);
        }

        if (empty($data)) {
            $this->View()->assign(array('success' => false, 'message' => 'Form not found'));
            return;
        }

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => 1));
        return;
    }

    /**
     * Gets a | delimited and separated shop id list
     * and converts it into an array of ints
     *
     * @param string $shopIds
     * @return array The list of shop ids
     */
    private function explodeShopIds($shopIds)
    {
        if (empty($shopIds)) {
            return array();
        }

        $explodedShopIds = explode('|', trim($shopIds, '|'));

        $explodedShopIds = array_map(function ($elem) {
            return (int)$elem;
        }, $explodedShopIds);

        return $explodedShopIds;
    }

    /**
     * Creates new form
     */
    public function createFormAction()
    {
        $params = $this->Request()->getParams();

        $formModel = new Form();
        $params['attribute'] = $params['attribute'][0];
        $params['shopIds'] = $params['shopIds'] ? '|' . implode('|', $params['shopIds']) . '|' : null;

        $formModel->fromArray($params);

        $this->getManager()->persist($formModel);
        $this->getManager()->flush();

        $data = $this->getManager()->toArray($formModel);
        $data['shopIds'] = $this->explodeShopIds($data['shopIds']);

        $this->View()->assign(array('success' => true, 'data' => $data));
    }

    /**
     * Updates form
     */
    public function updateFormAction()
    {
        if (!($id = $this->Request()->getParam('id'))) {
            $this->View()->assign(array('success' => false, 'message' => 'No valid form Id'));
            return;
        }

        /* @var $result Form */
        $result = $this->getRepository()->find($id);
        if (!$result) {
            $this->View()->assign(array('success' => false, 'message' => 'Form not found'));
            return;
        }

        $params = $this->Request()->getParams();
        $params['attribute'] = $params['attribute'][0];

        // unset fields - fields should only be updated via the updateField-Action
        unset($params['fields']);

        $params['shopIds'] = $params['shopIds'] ? '|' . implode('|', $params['shopIds']) . '|' : null;

        $result->fromArray($params);
        $this->getManager()->persist($result);
        $this->getManager()->flush();

        $this->getSingleForm($id);
    }


    /**
     * Removes form
     */
    public function removeFormAction()
    {
        if (!($id = $this->Request()->getParam('id'))) {
            $this->View()->assign(array('success' => false, 'message' => 'No valid form Id'));
            return;
        }

        /* @var $result Form */
        $result = $this->getRepository()->find($id);
        if (!$result) {
            $this->View()->assign(array('success' => false, 'message' => 'Form not found'));
            return;
        }

        $this->getManager()->remove($result);
        $this->getManager()->flush();

        $this->View()->assign(array('success' => true));
    }

    /**
     * Copies form
     */
    public function copyFormAction()
    {
        if (!($id = $this->Request()->getParam('id'))) {
            $this->View()->assign(array('success' => false, 'message' => 'No valid field Id'));
            return;
        }

        /* @var $result Form */
        $result = $this->getRepository()->find($id);
        if (!$result) {
            $this->View()->assign(array('success' => false, 'message' => 'Form not found'));
            return;
        }

        $clonedForm = $result->getClone();
        $clonedForm->setName('Copy of ' . $result->getName());

        $this->getManager()->persist($clonedForm);
        $this->getManager()->flush();

        $this->View()->assign(array('success' => true));
    }

    /**
     * Returns fields
     */
    public function getFieldsAction()
    {
        if (!($id = $this->Request()->getParam('formId'))) {
            $this->View()->assign(array('success' => false, 'message' => 'No valid field Id'));
            return;
        }

        $result = $this->getManager()->getRepository('Shopware\Models\Form\Field')->findBy(
            array('formId' => $id),
            array('position' => 'ASC')
        );

        $resultArray = $this->getManager()->toArray($result);

        $this->View()->assign(array('success' => true, 'data' => $resultArray, 'total' => count($resultArray)));
    }

    /**
     * Updates form
     */
    public function updateFieldAction()
    {
        if (!($id = $this->Request()->getParam('id'))) {
            $this->View()->assign(array('success' => false, 'message' => 'No valid field Id'));
            return;
        }

        /* @var $result Field */
        $result = $this->getManager()->getRepository('Shopware\Models\Form\Field')->find($id);
        if (!$result) {
            $this->View()->assign(array('success' => false, 'message' => 'Field not found'));
            return;
        }

        $params = $this->Request()->getParams();
        $result->fromArray($params);

        $this->getManager()->persist($result);
        $this->getManager()->flush();

        $data = $this->getManager()->toArray($result);
        $this->View()->assign(array('success' => true, 'data' => $data));
    }

    /**
     * Creates new field
     */
    public function createFieldAction()
    {
        if (!($id = $this->Request()->getParam('formId'))) {
            $this->View()->assign(array('success' => false, 'message' => 'No valid field Id'));
            return;
        }

        /* @var $form Form */
        $form = $this->getRepository()->find($id);
        if (!$form) {
            $this->View()->assign(array('success' => false, 'message' => 'Form not found'));
            return;
        }

        $params = $this->Request()->getParams();

        $fieldModel = new Field();
        $fieldModel->fromArray($params);
        $fieldModel->setForm($form);

        $this->getManager()->persist($fieldModel);
        $this->getManager()->flush();

        $data = $this->getManager()->toArray($fieldModel);
        $this->View()->assign(array('success' => true, 'data' => $data));
    }

    /**
     * Removes field
     */
    public function removeFieldAction()
    {
        if (!($id = $this->Request()->getParam('id'))) {
            $this->View()->assign(array('success' => false, 'message' => 'No valid field Id'));
            return;
        }

        /* @var $result Field */
        $result = $this->getManager()->find('Shopware\Models\Form\Field', $id);
        if (!$result) {
            $this->View()->assign(array('success' => false, 'message' => 'Field not found'));
            return;
        }

        $this->getManager()->remove($result);
        $this->getManager()->flush();

        $this->View()->assign(array('success' => true));
    }

    /**
     * Changes Position of field
     */
    public function changeFieldPositionAction()
    {
        $data = $this->Request()->getParam('data');
        $positions = json_decode($data);

        $qb = $this->getManager()->createQueryBuilder();
        $qb->update('Shopware\Models\Form\Field', 'field')
            ->andWhere('field.id = :fieldId');

        foreach ($positions as $position => $fieldid) {
            $qb->set('field.position', $position)
                ->setParameter('fieldId', $fieldid)
                ->getQuery()
                ->execute();
        }

        $this->View()->assign(array('success' => true));
    }

    /**
     * Helper method to prefix properties
     *
     * @param array $properties
     * @param string $prefix
     * @return array
     */
    protected function prefixProperties($properties = array(), $prefix = '')
    {
        foreach ($properties as $key => $property) {
            if (isset($property['property'])) {
                $properties[$key]['property'] = $prefix . '.' . $property['property'];
            }
        }

        return $properties;
    }
}
