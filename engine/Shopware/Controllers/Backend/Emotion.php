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
 *
 * @category   Shopware
 * @package    Shopware_Controllers
 * @subpackage Customer
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Oliver Denter
 * @author     $Author$
 */
/**
 * Backend Controller for the customer backend module.
 *
 * Displays all customers in an Ext.grid.Panel and allows to delete,
 * add and edit customers. On the detail page the customer data displayed
 * and a list of all done orders shown.
 */
/**
 * Backend Controller for the customer backend module.
 * Displays all customers in an Ext.grid.Panel and allows to delete,
 * add and edit customers. On the detail page the customer data displayed
 * and a list of all done orders shown.
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Oliver Denter
 * @author $Author$
 * @package Shopware_Controllers
 * @subpackage Backend
 */
class Shopware_Controllers_Backend_Emotion extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Emotion repository. Declared for an fast access to the emotion repository.
     *
     * @var \Shopware\Models\Emotion\Repository
     * @access private
     */
    public static $repository = null;

    /**
     * Entity Manager
     * @var null
     */
    protected $manager = null;

	protected function initAcl()
	{
		$this->addAclPermission('list', 'read', 'Insufficient Permissions');
		$this->addAclPermission('detail', 'read', 'Insufficient Permissions');
		$this->addAclPermission('library', 'read', 'Insufficient Permissions');
		$this->addAclPermission('fill', 'read', 'Insufficient Permissions');

		$this->addAclPermission('delete', 'delete', 'Insufficient Permissions');

		$this->addAclPermission('save', 'save', 'Insufficient Permissions');

		$this->addAclPermission('duplicate', 'create', 'Insufficient Permissions');
	}

    /**
     * Internal helper function to get access to the entity manager.
     * @return null
     */
    private function getManager() {
        if ($this->manager === null) {
            $this->manager= Shopware()->Models();
        }
        return $this->manager;
    }

    /**
     * Helper function to get access on the static declared repository
     *
     * @return null|Shopware\Models\Emotion\Repository
     */
    protected function getRepository()
    {
        if(self::$repository === null) {
            self::$repository = Shopware()->Models()->getRepository('Shopware\Models\Emotion\Emotion');
        }
        return self::$repository;
    }

    /**
     * Event listener function of the listing store of the emotion backend module.
     * Returns an array of all defined emotions.
     * @return array
     */
    public function listAction()
    {
        $limit = $this->Request()->getParam('limit', null);
        $offset = $this->Request()->getParam('start', 0);
        $sort = $this->Request()->getParam('sort', null);
        $filter = $this->Request()->getParam('filter', null);
        if (!empty($filter)) {
            $filter = $filter[0]['value'];
        }

        $query = $this->getRepository()->getListQuery($filter, $sort, $offset, $limit);
        $count = Shopware()->Models()->getQueryCount($query);
        $emotions = $query->getArrayResult();
        foreach ($emotions as &$emotion){
            $categories = array();
            foreach ($emotion["categories"] as $category){
                $categories[] = $category["name"];
            }
            $emotion["categoriesNames"] = implode(",",$categories);
        }
        $this->View()->assign(array(
            'success' => true,
            'data' => $emotions,
            'total' => $count
        ));
    }

    /**
     * Event listener function of the emotion detail store of the backend module.
     * Fired when the user clicks the edit button in the listing. The function returns
     * all data for a single emotion.
     * @return array
     */
    public function detailAction()
    {
        $id = $this->Request()->getParam('id', null);
        $query = $this->getRepository()->getEmotionDetailQuery($id);

        $emotion = $query->getArrayResult();
        $emotion = $emotion[0];

        if (!empty($emotion["isLandingPage"])){
            $emotion["link"] = "/Campaign/index/emotionId/".$emotion["id"];
        }else {
            $emotion["categoryId"] = !empty($emotion["categories"][0]["id"]) ? $emotion["categories"][0]["id"] : 0;
        }

        $validFrom = $emotion['validFrom'];
        $validTo = $emotion['validTo'];

        /**@var $validFrom \DateTime*/
        if ($validFrom instanceof \DateTime) {
            $emotion['validFrom'] = $validFrom->format('d.m.Y');
            $emotion['validFromTime'] = $validFrom->format('H:i:s');
        }

        /**@var $validTo \DateTime*/
        if ($validTo instanceof \DateTime) {
            $emotion['validTo'] = $validTo->format('d.m.Y');
            $emotion['validToTime'] = $validTo->format('H:i:s');
        }

        foreach($emotion['elements'] as &$element) {
            $elementQuery = $this->getRepository()->getElementDataQuery($element['id'], $element['componentId']);
            $componentData = $elementQuery->getArrayResult();
            $data = array();
            foreach($componentData as $entry) {
                $value = '';
                switch(strtolower($entry['valueType'])) {
                    case "json":
                        $value = Zend_Json::decode($entry['value']);
                        break;
                    case "string":
                    default:
                        $value = $entry['value'];
                        break;
                }
                $data[] = array(
                    'id' => $entry['id'],
                    'valueType' => $entry['valueType'],
                    'key' => $entry['name'],
                    'value' => $value
                );
            }
            $element['data'] = $data;
        }
        $this->View()->assign(array(
            'success' => true,
            'data' => $emotion,
            'total' => 1
        ));
    }

    /**
     * Event listener function of the library store.
     * @return array
     */
    public function libraryAction()
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('components', 'fields'))
                ->from('Shopware\Models\Emotion\Library\Component', 'components')
                ->leftJoin('components.fields', 'fields');

        $components = $builder->getQuery()->getArrayResult();
        $this->View()->assign(array(
            'success' => true,
            'data' => $components
        ));
    }

    /**
     * Model event listener function which fired when the user configure an emotion over the backend
     * module and clicks the save button.
     *
     * @return mixed
     */
    public function saveAction()
    {

        /** @var $namespace Enlight_Components_Snippet_Namespace */
        $namespace = Shopware()->Snippets()->getNamespace('backend/emotion');
        try {
            $id = $this->Request()->getParam('id', null);
            $data = $this->Request()->getParams();

            if (!empty($id)) {
                /**@var $model \Shopware\Models\Emotion\Emotion*/
                $emotion = Shopware()->Models()->find('Shopware\Models\Emotion\Emotion', $id);
                if (!$emotion) {
                    $this->View()->assign(array(
                        'success' => false,
                        'data' => $this->Request()->getParams(),
                        'message' => $namespace->get('no_valid_id', 'No valid emotion id passed.'))
                    );
                    return;
                }
                unset($data['createDate']);
                $this->clearEmotionData($emotion);
            } else {
                $emotion = new \Shopware\Models\Emotion\Emotion();
                $data["createDate"] = new \DateTime();
            }

            if (!empty($data['validFrom']) && !empty($data['validFromTime'])) {
                $fromDate = new \DateTime($data['validFrom']);
                $fromTime = new \DateTime($data['validFromTime']);
                $data['validFrom'] = $fromDate->format('d.m.Y') . ' ' . $fromTime->format('H:i');
            } else {
                $data['validFrom'] = null;
            }

            if (!empty($data['validTo']) && !empty($data['validToTime'])) {
                $toDate = new \DateTime($data['validTo']);
                $toTime = new \DateTime($data['validToTime']);
                $data['validTo'] = $toDate->format('d.m.Y') . ' ' . $toTime->format('H:i');
            } else {
                $data['validTo'] = null;
            }

            $data['attribute'] = $data['attribute'][0];
            $data['modified'] = new \DateTime();
            $data['elements'] = $this->fillElements($emotion, $data);

            if ($data['isLandingPage']) {
                if (empty($data['categories'])) {
                    $data['categories'] = null;
                } else {
                    $categories = array();
                    foreach ($data['categories'] as $category) {
                        $categories[] = Shopware()->Models()->find('Shopware\Models\Category\Category', $category);
                    }
                    $data['categories'] = $categories;
                }
            } else {
                $data['categories'] = array(Shopware()->Models()->find('Shopware\Models\Category\Category', $data['categoryId']));
            }

            unset($data['user']);
            if (Shopware()->Auth()->getIdentity()->id) {
                $data['user'] = Shopware()->Models()->find('Shopware\Models\User\User', Shopware()->Auth()->getIdentity()->id);
            }
            $emotion->fromArray($data);

            Shopware()->Models()->persist($emotion);
            Shopware()->Models()->flush();

            $this->View()->assign(array(
                'data' => $data,
                'success' => true
            ));
        }
        catch (\Doctrine\ORM\ORMException $e) {
            $this->View()->assign(array(
                'data' => $this->Request()->getParams(),
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Internal helper function which deletes all data for the passed emotion.
     * @param $emotion
     */
    private function clearEmotionData($emotion) {
        $query = Shopware()->Models()->createQuery('DELETE Shopware\Models\Emotion\Data u WHERE u.emotionId = ?1');
        $query->setParameter(1,$emotion->getId());
        $query->execute();

        $query = Shopware()->Models()->createQuery('DELETE Shopware\Models\Emotion\Element u WHERE u.emotionId = ?1');
        $query->setParameter(1,$emotion->getId());
        $query->execute();
    }

    /**
     * Internal helper function which interpreted the passed emotion elements and save convert the data array
     * to an model array.
     *
     * @param $emotion
     * @param $data
     * @return array
     */
    private function fillElements($emotion, $data) {
        $elements= array();

        foreach($data['elements'] as $elementData) {
            $element = new \Shopware\Models\Emotion\Element();
            $component = Shopware()->Models()->find('Shopware\Models\Emotion\Library\Component', $elementData['componentId']);

            foreach($elementData['data'] as $item) {
                $model = new \Shopware\Models\Emotion\Data();
                $field = Shopware()->Models()->find('Shopware\Models\Emotion\Library\Field', $item['id']);
                $model->setComponent($component);
                $model->setComponentId($component->getId());
                $model->setElement($element);
                $model->setFieldId($item['id']);

                /**@var $field \Shopware\Models\Emotion\Library\Field*/
                $model->setField($field);
                $value = '';
                switch(strtolower($field->getValueType())) {
                    case "json":
                        $value = Zend_Json::encode($item['value']);
                        break;
                    case "string":
                    default:
                        $value = $item['value'];
                        break;
                }
                $model->setValue($value);
                $model->setEmotionId($emotion->getId());
                Shopware()->Models()->persist($model);
            }

            $elementData['emotion'] = $emotion;
            $elementData['component'] = $component;
            unset($elementData['data']);
            $element->fromArray($elementData);
            $elements[] = $element;
        }
        return $elements;
    }

    /**
     * Model event listener function which fired when the user select an emotion row
     * in the backend listing and clicks the remove button or the action column.
     *
     * @return mixed
     */
    public function deleteAction()
    {
        try {
            //get posted customers
            $emotions = $this->Request()->getParam('emotions', array(array('id' => $this->Request()->getParam('id'))));

            //iterate the customers and add the remove action
            foreach($emotions as $emotion) {
                if (empty($emotion['id'])) {
                    continue;
                }
                /**@var $entity \Shopware\Models\Emotion\Emotion*/
                $entity = $this->getRepository()->find($emotion['id']);
                Shopware()->Models()->remove($entity);
            }
            Shopware()->Models()->flush();

            $this->View()->assign(array(
                'data' => $this->Request()->getParams(),
                'success' => true
            ));
        }
        catch (\Doctrine\ORM\ORMException $e) {
            $this->View()->assign(array(
                'data' => $this->Request()->getParams(),
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    public function duplicateAction() {
        $emotionId = (int) $this->Request()->getParam('emotionId');

        $this->View()->assign(array('success' => true, 'data' => array()));
    }

}