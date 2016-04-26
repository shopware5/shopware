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

use Doctrine\Common\Collections\ArrayCollection;

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
    private function getManager()
    {
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
        if (self::$repository === null) {
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
        $filter = $this->Request()->getParam('filter', null);
        $filterBy = $this->Request()->getParam('filterBy', null);
        $categoryId = $this->Request()->getParam('categoryId', null);

        $query = $this->getRepository()->getListingQuery($filter, $filterBy, $categoryId);

        $query->setFirstResult($offset)
            ->setMaxResults($limit);

        /**@var $statement PDOStatement*/
        $statement = $query->execute();
        $emotions = $statement->fetchAll(PDO::FETCH_ASSOC);

        $query->select('COUNT(emotions.id) as count')
            ->resetQueryPart('groupBy')
            ->resetQueryPart('orderBy')
            ->setFirstResult(0)
            ->setMaxResults(1)
        ;

        $statement = $query->execute();
        $count = $statement->fetch(PDO::FETCH_COLUMN);

        $this->View()->assign(array(
            'success' => true,
            'data' => $emotions,
            'total' => $count
        ));
    }

    /**
     * Returns all master landing pages.
     */
    public function getMasterLandingPagesAction()
    {
        $id = $this->Request()->getParam('id', null);
        $ownId = $this->Request()->getParam('ownId', null);

        $builder = $this->getRepository()->getListingQuery([], 'onlyLandingPageMasters');

        if ($id) {
            $builder->where('emotions.id = :id')
                ->setParameters(['id' => $id])
                ->setFirstResult(0)
                ->setMaxResults(1);
        }

        if ($ownId) {
            $builder->andWhere('emotions.id != :ownId')
                ->setParameter('ownId', $ownId);
        }

        $builder->andWhere('emotions.is_landingpage = 1')
            ->andWhere('emotions.parent_id IS NULL');

        $statement = $builder->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        $this->View()->assign(['success' => true, 'data' => $data]);
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
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        $emotion = $query->getArrayResult();
        $emotion = $emotion[0];
        $emotion['grid'] = array($emotion['grid']);

        if (!empty($emotion["isLandingPage"])) {
            $emotion["link"] = "shopware.php?sViewport=campaign&emotionId=".$emotion["id"];
        }

        $validFrom = $emotion['validFrom'];
        $validTo = $emotion['validTo'];

        /**@var $validFrom \DateTime*/
        if ($validFrom instanceof \DateTime) {
            $emotion['validFrom'] = $validFrom->format('d.m.Y');
            $emotion['validFromTime'] = $validFrom->format('H:i');
        }

        /**@var $validTo \DateTime*/
        if ($validTo instanceof \DateTime) {
            $emotion['validTo'] = $validTo->format('d.m.Y');
            $emotion['validToTime'] = $validTo->format('H:i');
        }

        foreach ($emotion['elements'] as &$element) {
            $elementQuery = $this->getRepository()->getElementDataQuery($element['id'], $element['componentId']);
            $componentData = $elementQuery->getArrayResult();
            $data = array();
            foreach ($componentData as $entry) {
                $value = '';
                switch (strtolower($entry['valueType'])) {
                    case "json":
                        if ($entry['value'] != '') {
                            $value = Zend_Json::decode($entry['value']);
                        } else {
                            $value = null;
                        }
                        break;
                    case "string":
                    default:
                        $value = $entry['value'];
                        break;
                }

                if ($entry['name'] === 'file') {
                    $value = $mediaService->getUrl($value);
                }

                if (in_array($entry['name'], ['selected_manufacturers', 'banner_slider'])) {
                    foreach ($value as $k => $v) {
                        if (isset($v['path'])) {
                            $value[$k]['path'] = $mediaService->getUrl($v['path']);
                        }
                    }
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
            ->leftJoin('components.fields', 'fields')
            ->orderBy('components.id', 'ASC')
            ->addOrderBy('fields.position', 'ASC');

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

            if (!empty($data['gridId'])) {
                $data['grid'] = Shopware()->Models()->find('Shopware\Models\Emotion\Grid', $data['gridId']);
            }
            if (!empty($data['templateId'])) {
                $data['template'] = Shopware()->Models()->find('Shopware\Models\Emotion\Template', $data['templateId']);
            } else {
                $data['template'] = null;
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

            if (empty($data['categories'])) {
                $data['categories'] = null;
            } else {
                $categories = array();
                foreach ($data['categories'] as $category) {
                    $categories[] = Shopware()->Models()->find('Shopware\Models\Category\Category', $category);
                }
                $data['categories'] = $categories;
            }

            unset($data['user']);
            if (Shopware()->Auth()->getIdentity()->id) {
                $data['user'] = Shopware()->Models()->find('Shopware\Models\User\User', Shopware()->Auth()->getIdentity()->id);
            }
            if (!$data['parentId']) {
                $emotion->setParentId(null);
            }

            $emotion->fromArray($data);

            Shopware()->Models()->persist($emotion);
            Shopware()->Models()->flush();

            $alreadyExists = $this->hasEmotionForSameDeviceType($data['categoryId']);

            $data['id'] = $emotion->getId();

            $this->View()->assign(array(
                'data' => $data,
                'success' => true,
                'alreadyExists' => $alreadyExists
            ));
        } catch (\Doctrine\ORM\ORMException $e) {
            $this->View()->assign(array(
                'data' => $this->Request()->getParams(),
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Fetch all emotions with same category Id and
     * mark existing emotions with same devices and category
     *
     * @param int $categoryId
     * @return bool
     */
    private function hasEmotionForSameDeviceType($categoryId)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder
            ->select(['emotions', 'categories'])
            ->from('Shopware\Models\Emotion\Emotion', 'emotions')
            ->leftJoin('emotions.categories', 'categories')
            ->where('categories.id = :categoryId');

        $builder->setParameters(['categoryId' => $categoryId]);
        $result = $builder->getQuery()->getArrayResult();

        $usedDevices = [];
        foreach ($result as $emotion) {
            $devices = explode(",", $emotion['device']);
            foreach ($devices as $device) {
                if (!in_array($device, $usedDevices)) {
                    $usedDevices[] = $device;
                } else {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Internal helper function which deletes all data for the passed emotion.
     * @param $emotion
     */
    private function clearEmotionData($emotion)
    {
        $query = Shopware()->Models()->createQuery('DELETE Shopware\Models\Emotion\Data u WHERE u.emotionId = ?1');
        $query->setParameter(1, $emotion->getId());
        $query->execute();
    }

    /**
     * Internal helper function which interpreted the passed emotion elements and save convert the data array
     * to an model array.
     *
     * Existing element entities will be updated.
     *
     * @param $emotion
     * @param $data
     * @return array
     */
    private function fillElements($emotion, $data)
    {
        /** @var \Shopware\Models\Emotion\Element $elementEntity */
        foreach($emotion->getElements() as $elementEntity) {
            $updated = false;
            foreach($data['elements'] as $postedElement) {
                // update existing elements
                if($postedElement['id'] === $elementEntity->getId()) {
                    $updated = true;

                    // update moving on the canvas
                    $elementEntity->setStartCol($postedElement['startCol']);
                    $elementEntity->setStartRow($postedElement['startRow']);
                    $elementEntity->setEndCol($postedElement['endCol']);
                    $elementEntity->setEndRow($postedElement['endRow']);

                    // update data
                    $this->fillElementData($postedElement, $elementEntity);
                    break; // exit $data['elements'] looping
                }
            }
            // remove deleted elements
            if($updated === false) {
                $emotion->getElements()->removeElement($elementEntity);
            }
        }

        // add new elements
        foreach($data['elements'] as $postedElement) {
            if($postedElement['id'] === 0 && $postedElement['emotionId'] === 0) {
                $element = new \Shopware\Models\Emotion\Element();
                $this->get('models')->persist($element);

                $component = $this->get('models')->find('Shopware\Models\Emotion\Library\Component', $postedElement['componentId']);

                $postedElement['emotion'] = $emotion;
                $postedElement['component'] = $component;
                $postedData = $postedElement['data'];
                unset($postedElement['data']);
                $element->fromArray($postedElement);
                $postedElement['data'] = $postedData;

                $this->get('models')->flush(); // need element id

                // update data
                $this->fillElementData($postedElement, $element);

                $emotion->getElements()->add($element);
            }
        }

        return $emotion->getElements() ? $emotion->getElements()->toArray() : array();
    }

    /**
     * Internal helper function to persist element data.
     *
     * This method creates new model entities for every data field.
     *
     * @param $postedElement
     * @param \Shopware\Models\Emotion\Element $elementEntity
     */
    private function fillElementData($postedElement, \Shopware\Models\Emotion\Element $elementEntity)
    {
        $component = $this->get('models')->find('Shopware\Models\Emotion\Library\Component', $postedElement['componentId']);

        foreach ($postedElement['data'] as $item) {
            $model = new \Shopware\Models\Emotion\Data();
            /** @var $field \Shopware\Models\Emotion\Library\Field */
            $field = $this->get('models')->find('Shopware\Models\Emotion\Library\Field', $item['id']);
            $model->setComponent($component);
            $model->setComponentId($component->getId());
            $model->setElement($elementEntity);
            $model->setElementId($elementEntity->getId());
            $model->setEmotion($elementEntity->getEmotion());
            $model->setEmotionId($elementEntity->getEmotion()->getId());
            $model->setField($field);
            $model->setFieldId($item['id']);

            switch (strtolower($field->getValueType())) {
                case "json":
                    if (is_array($item['value'])) {
                        foreach ($item['value'] as &$val) {
                            $val['path'] = $this->get('shopware_media.media_service')->normalize($val['path']);
                        }
                    }

                    $value = \Zend_Json::encode($item['value']);
                    break;
                case "string":
                default:
                    $value = $item['value'];
                    break;
            }

            if (in_array($field->getXType(), $this->getMediaXTypes())) {
                $value = $this->get('shopware_media.media_service')->normalize($value);
            }

            $model->setValue($value);
            $this->get('models')->persist($model);
        }
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
            foreach ($emotions as $emotion) {
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
        } catch (\Doctrine\ORM\ORMException $e) {
            $this->View()->assign(array(
                'data' => $this->Request()->getParams(),
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    public function duplicateAction()
    {
        $emotionId = (int) $this->Request()->getParam('emotionId');
        $device = $this->Request()->getParam('forDevice');

        if (!$emotionId) {
            $this->View()->assign(array('success' => false));
            return;
        }

        /** @var \Shopware\Models\Emotion\Emotion $emotion */
        $emotion = Shopware()->Models()->find('Shopware\Models\Emotion\Emotion', $emotionId);

        if (!$emotion) {
            $this->View()->assign(array('success' => false));
            return;
        }

        $new = clone $emotion;

        switch (true) {
            case ($emotion->getIsLandingPage() && $emotion->getParentId()):
                $new->setParentId($emotion->getParentId());
                break;
            case ($emotion->getIsLandingPage()):
                $new->setParentId($emotion->getId());
                break;
        }

        $new->setDevice($device);
        $new->setCreateDate(new \DateTime());
        $new->setModified(new \DateTime());

        Shopware()->Models()->persist($new);
        Shopware()->Models()->flush();

        $this->View()->assign(array('success' => true, 'data' => array()));
    }


    //Grid functions of the "own grids" listing

    /**
     * Controller action  to create a new grid.
     * Use the internal "saveGrid" function.
     * The request parameters are used as grid/model data.
     */
    public function createGridAction()
    {
        $this->View()->assign(
            $this->saveGrid(
                $this->Request()->getParams()
            )
        );
    }

    /**
     * Controller action to update an existing grid.
     * Use the internal "saveGrid" function.
     * The request parameters are used as grid/model data.
     * The updateGridAction should have a "id" request parameter which
     * contains the id of the existing grid.
     */
    public function updateGridAction()
    {
        $this->View()->assign(
            $this->saveGrid(
                $this->Request()->getParams()
            )
        );
    }

    /**
     * Controller action to delete a single grid.
     * Use the internal "deleteGrid" function.
     * Expects the grid id as request parameter "id".
     */
    public function deleteGridAction()
    {
        $this->View()->assign(
            $this->deleteGrid(
                $this->Request()->getParam('id', null)
            )
        );
    }

    public function deleteManyGridsAction()
    {
        $this->View()->assign(
            $this->deleteManyGrids(
                $this->Request()->getParam('records', array())
            )
        );
    }

    /**
     * The delete many grids function is used from the controller action deleteManyGridsAction
     * and contains the real deleteMany process. As parameter the function expects
     * and two dimensional array with model ids:
     *
     * Example:
     * array(
     *    array('id' => 1),
     *    array('id' => 2),
     *    ...
     * )
     *
     * The function iterates the passed records array and calls for each record
     * the "deleteGrid" function. If the delete action for the item was successfully,
     * the delete function returns the following array('success' => true).
     * If the delete function fails, the delete action returns array('success' => false, 'error'),
     * this errors will be collected.
     * Notice: The iteration don't stops if an errors occurs. It will be continue with the next record.
     *
     * After all records deleted, the function returns array('success' => true) if no errors occurs.
     * If one or more errors occurred the function return an array like this:
     *  array(
     *      'success' => false,
     *      'error' => array('Error 1', 'Error 2', ...)
     * )
     *
     * @param $records
     * @return array
     */
    protected function deleteManyGrids($records)
    {
        if (empty($records)) {
            return array('success' => false, 'error' => 'No grids passed');
        }
        $errors = array();
        foreach ($records as $record) {
            if (empty($record['id'])) {
                continue;
            }
            $result = $this->deleteGrid($record['id']);
            if ($result['success'] === false) {
                $errors[] = array($result['error']);
            }
        }

        return array(
            'success' => empty($errors),
            'error' => $errors
        );
    }

    /**
     * Controller action to duplicate a single grid.
     * Use the internal "duplicateGrid" function.
     * Expects the grid id as request parameter "id".
     */
    public function duplicateGridAction()
    {
        $this->View()->assign(
            $this->duplicateGrid(
                $this->Request()->getParam('id', null)
            )
        );
    }

    /**
     * Controller action to get a list of all defined grids.
     * You can paginate the list over the request parameters
     * "start" and "limit".
     * Use the internal "getGrids" function.
     */
    public function getGridsAction()
    {
        $this->View()->assign(
            $this->getGrids(
                $this->Request()->getParam('start', null),
                $this->Request()->getParam('limit', null)
            )
        );
    }

    /**
     * Returns a list with all defined grids.
     * The function return value is every time an array.
     *
     * Success case:
     *  array('success' => true, 'total' => Total listing count, 'data' => All defined grids)
     *
     * Failure case:
     *  array('success' => false, 'error' => Error message)
     *
     * @param null $offset
     * @param null $limit
     *
     * @return array
     */
    protected function getGrids($offset = null, $limit = null)
    {
        try {
            $query = $this->getGridsQuery($offset, $limit);
            $paginator = $this->getQueryPaginator($query->getQuery());

            $result = array(
                'success' => true,
                'total' => $paginator->count(),
                'data' => $paginator->getIterator()->getArrayCopy()
            );
        } catch (Exception $e) {
            return array('success' => false, 'error' => $e->getMessage());
        }

        return $result;
    }

    /**
     * @param null $offset
     * @param null $limit
     *
     * @return Doctrine\ORM\QueryBuilder|Shopware\Components\Model\QueryBuilder
     */
    protected function getGridsQuery($offset = null, $limit = null)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('grids'))
            ->from('Shopware\Models\Emotion\Grid', 'grids');

        if ($offset !== null  && $limit !== null) {
            $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $builder;
    }

    /**
     * Deletes a single grid which will be identified over
     * the passed id parameter. The return value is
     * every time an array.
     *
     * Success case:
     *  array('success' => true)
     *
     * Failure case:
     *  array('success' => false, 'error' => An error message)
     *
     *
     * @param null $id
     *
     * @return array
     */
    protected function deleteGrid($id = null)
    {
        if (empty($id)) {
            return array('success' => false, 'error' => "The request parameter id don't passed!");
        }

        try {
            $grid = Shopware()->Models()->find('Shopware\Models\Emotion\Grid', $id);
            if (!$grid instanceof \Shopware\Models\Emotion\Grid) {
                return array('success' => false, 'error' => "The passed grid id exist no more!");
            }
            Shopware()->Models()->remove($grid);
            Shopware()->Models()->flush();
        } catch (Exception $e) {
            return array('success' => false, 'error' => $e->getMessage());
        }

        return array('success' => true);
    }

    /**
     * Duplicates a single grid which will be identified over
     * the passed id parameter. The return value is
     * every time an array. The duplicate function used
     * the php __clone function of the model.
     *
     * Success case:
     *  array('success' => true, 'data' => New Grid data)
     *
     * Failure case:
     *  array('success' => false, 'error' => An error message)
     *
     *
     * @param null $id
     *
     * @return array
     */
    protected function duplicateGrid($id = null)
    {
        if (empty($id)) {
            return array('success' => false, 'error' => "The request parameter gridId don't passed!");
        }
        $data = array();

        try {
            $grid = Shopware()->Models()->find('Shopware\Models\Emotion\Grid', $id);
            if (!$grid instanceof \Shopware\Models\Emotion\Grid) {
                return array('success' => false, 'error' => "The passed grid id exist no more!");
            }

            $new = clone $grid;
            Shopware()->Models()->persist($new);
            Shopware()->Models()->flush();

            $data = $this->getGrid($new->getId());
        } catch (Exception $e) {
            return array('success' => false, 'error' => $e->getMessage());
        }

        return array('success' => true, 'data' => $data);
    }

    /**
     * Updates or creates a single grid. If the data parameter contains
     * an "id" property, this property is used to identify an existing grid.
     * The return value is every time an array.
     *
     * Success case:
     *  array('success' => true, 'data' => New Grid data)
     *
     * Failure case:
     *  array('success' => false, 'error' => An error message)
     *
     * @param $data
     *
     * @return array
     */
    protected function saveGrid($data)
    {
        $result = array();

        try {
            //we have to remove the emotions to prevent an assignment from this side!
            unset($data['emotions']);
            if (!empty($data['id'])) {
                $grid = Shopware()->Models()->find('Shopware\Models\Emotion\Grid', $data['id']);
            } else {
                $grid = new \Shopware\Models\Emotion\Grid();
            }

            if (!$grid instanceof \Shopware\Models\Emotion\Grid) {
                return array('success' => false, 'error' => "The passed grid id exist no more!");
            }

            $grid->fromArray($data);

            Shopware()->Models()->persist($grid);
            Shopware()->Models()->flush();

            $result = $this->getGrid($grid->getId());
        } catch (Exception $e) {
            return array('success' => false, 'error' => $e->getMessage());
        }

        return array('success' => true, 'data' => $result);
    }

    /**
     * Helper function to get the array data of a single grid.
     * The passed $id parameter is used to identify the grid.
     *
     * Success case:
     *  array(Data of the grid)
     *
     * Failure case:
     *  null
     *
     * @param null $id
     *
     * @return array
     */
    protected function getGrid($id)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('grid'))
            ->from('Shopware\Models\Emotion\Grid', 'grid')
            ->where('grid.id = :id')
            ->setParameter('id', $id);

        return $builder->getQuery()->getOneOrNullResult(
            \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY
        );
    }

    /**
     * @param     $query \Doctrine\ORM\Query
     * @param int $hydrationMode
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    private function getQueryPaginator($query, $hydrationMode = \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY)
    {
        $query->setHydrationMode($hydrationMode);

        return $this->getModelManager()->createPaginator($query);
    }

    /**
     * Controller action  to create a new template.
     * Use the internal "saveTemplate" function.
     * The request parameters are used as template/model data.
     */
    public function createTemplateAction()
    {
        $this->View()->assign(
            $this->saveTemplate(
                $this->Request()->getParams()
            )
        );
    }

    /**
     * Controller action to update an existing template.
     * Use the internal "saveTemplate" function.
     * The request parameters are used as template/model data.
     * The updateTemplateAction should have a "id" request parameter which
     * contains the id of the existing template.
     */
    public function updateTemplateAction()
    {
        $this->View()->assign(
            $this->saveTemplate(
                $this->Request()->getParams()
            )
        );
    }

    /**
     * Controller action to delete a single template.
     * Use the internal "deleteTemplate" function.
     * Expects the template id as request parameter "id".
     */
    public function deleteTemplateAction()
    {
        $this->View()->assign(
            $this->deleteTemplate(
                $this->Request()->getParam('id', null)
            )
        );
    }

    public function deleteManyTemplatesAction()
    {
        $this->View()->assign(
            $this->deleteManyTemplates(
                $this->Request()->getParam('records', array())
            )
        );
    }

    /**
     * The delete many templates function is used from the controller action deleteManyTemplatesAction
     * and contains the real deleteMany process. As parameter the function expects
     * and two dimensional array with model ids:
     *
     * Example:
     * array(
     *    array('id' => 1),
     *    array('id' => 2),
     *    ...
     * )
     *
     * The function iterates the passed records array and calls for each record
     * the "deleteTemplate" function. If the delete action for the item was successfully,
     * the delete function returns the following array('success' => true).
     * If the delete function fails, the delete action returns array('success' => false, 'error'),
     * this errors will be collected.
     * Notice: The iteration don't stops if an errors occurs. It will be continue with the next record.
     *
     * After all records deleted, the function returns array('success' => true) if no errors occurs.
     * If one or more errors occurred the function return an array like this:
     *  array(
     *      'success' => false,
     *      'error' => array('Error 1', 'Error 2', ...)
     * )
     *
     * @param $records
     * @return array
     */
    protected function deleteManyTemplates($records)
    {
        if (empty($records)) {
            return array('success' => false, 'error' => 'No templates passed');
        }
        $errors = array();
        foreach ($records as $record) {
            if (empty($record['id'])) {
                continue;
            }
            $result = $this->deleteTemplate($record['id']);
            if ($result['success'] === false) {
                $errors[] = array($result['error']);
            }
        }

        return array(
            'success' => empty($errors),
            'error' => $errors
        );
    }

    /**
     * Controller action to duplicate a single template.
     * Use the internal "duplicateTemplate" function.
     * Expects the template id as request parameter "id".
     */
    public function duplicateTemplateAction()
    {
        $this->View()->assign(
            $this->duplicateTemplate(
                $this->Request()->getParam('id', null)
            )
        );
    }

    /**
     * Controller action to get a list of all defined templates.
     * You can paginate the list over the request parameters
     * "start" and "limit".
     * Use the internal "getTemplates" function.
     */
    public function getTemplatesAction()
    {
        $this->View()->assign(
            $this->getTemplates(
                $this->Request()->getParam('start', null),
                $this->Request()->getParam('limit', null)
            )
        );
    }

    public function checkAvailabilityAction()
    {
        $emotionId = $this->Request()->getParam('emotionId', null);
        $deviceId = $this->Request()->getParam('deviceId', null);

        if (!$emotionId || !$deviceId) {
            return;
        }

        // get main Emotion
        $query = $this->getRepository()->getEmotionDetailQuery($emotionId);
        $emotion = $query->getArrayResult();
        $emotion = $emotion[0];

        // get emotion category
        $categoryId = $emotion['categories'][0]['id'];

        // return if no categoryId is defined, probably due to a landingpage.
        if (!$categoryId) {
            return;
        }

        // Search for categories with same device
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('emotions', 'categories'))
            ->from('Shopware\Models\Emotion\Emotion', 'emotions')
            ->leftJoin('emotions.categories', 'categories')
            ->where('categories.id = :categoryId')
            ->andWhere('emotions.id != :emotionId');

        $builder->setParameters(array(
            'categoryId' => $categoryId,
            'emotionId' => $emotionId
        ));
        $result = $builder->getQuery()->getArrayResult();

        $alreadyExists = false;
        foreach ($result as $emotion) {
            $devices = explode(",", $emotion['device']);

            if (in_array($deviceId, $devices)) {
                $alreadyExists = true;
                break;
            }
        }

        $this->View()->assign(array(
            'success' => true,
            'alreadyExists' => $alreadyExists
        ));
    }

    /**
     * Returns a list with all defined templates.
     * The function return value is every time an array.
     *
     * Success case:
     *  array('success' => true, 'total' => Total listing count, 'data' => All defined templates)
     *
     * Failure case:
     *  array('success' => false, 'error' => Error message)
     *
     * @param null $offset
     * @param null $limit
     *
     * @return array
     */
    protected function getTemplates($offset = null, $limit = null)
    {
        try {
            $query = $this->getTemplatesQuery($offset, $limit);
            $paginator = $this->getQueryPaginator($query->getQuery());

            $result = array(
                'success' => true,
                'total' => $paginator->count(),
                'data' => $paginator->getIterator()->getArrayCopy()
            );
        } catch (Exception $e) {
            return array('success' => false, 'error' => $e->getMessage());
        }

        return $result;
    }

    /**
     * @param null $offset
     * @param null $limit
     *
     * @return Doctrine\ORM\QueryBuilder|Shopware\Components\Model\QueryBuilder
     */
    protected function getTemplatesQuery($offset = null, $limit = null)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('templates'))
            ->from('Shopware\Models\Emotion\Template', 'templates');

        if ($offset !== null  && $limit !== null) {
            $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $builder;
    }

    /**
     * Deletes a single template which will be identified over
     * the passed id parameter. The return value is
     * every time an array.
     *
     * Success case:
     *  array('success' => true)
     *
     * Failure case:
     *  array('success' => false, 'error' => An error message)
     *
     *
     * @param null $id
     *
     * @return array
     */
    protected function deleteTemplate($id = null)
    {
        if (empty($id)) {
            return array('success' => false, 'error' => "The request parameter id don't passed!");
        }

        try {
            $template = Shopware()->Models()->find('Shopware\Models\Emotion\Template', $id);
            if (!$template instanceof \Shopware\Models\Emotion\Template) {
                return array('success' => false, 'error' => "The passed template id exist no more!");
            }
            Shopware()->Models()->remove($template);
            Shopware()->Models()->flush();
        } catch (Exception $e) {
            return array('success' => false, 'error' => $e->getMessage());
        }

        return array('success' => true);
    }

    /**
     * Duplicates a single template which will be identified over
     * the passed id parameter. The return value is
     * every time an array. The duplicate function used
     * the php __clone function of the model.
     *
     * Success case:
     *  array('success' => true, 'data' => New Template data)
     *
     * Failure case:
     *  array('success' => false, 'error' => An error message)
     *
     *
     * @param null $id
     *
     * @return array
     */
    protected function duplicateTemplate($id = null)
    {
        if (empty($id)) {
            return array('success' => false, 'error' => "The request parameter templateId don't passed!");
        }
        $data = array();

        try {
            $template = Shopware()->Models()->find('Shopware\Models\Emotion\Template', $id);
            if (!$template instanceof \Shopware\Models\Emotion\Template) {
                return array('success' => false, 'error' => "The passed template id exist no more!");
            }

            $new = clone $template;
            Shopware()->Models()->persist($new);
            Shopware()->Models()->flush();

            $data = $this->getTemplate($new->getId());
        } catch (Exception $e) {
            return array('success' => false, 'error' => $e->getMessage());
        }

        return array('success' => true, 'data' => $data);
    }

    /**
     * Updates or creates a single template. If the data parameter contains
     * an "id" property, this property is used to identify an existing template.
     * The return value is every time an array.
     *
     * Success case:
     *  array('success' => true, 'data' => New Template data)
     *
     * Failure case:
     *  array('success' => false, 'error' => An error message)
     *
     * @param $data
     *
     * @return array
     */
    protected function saveTemplate($data)
    {
        $result = array();

        try {
            //we have to remove the emotions to prevent an assignment from this side!
            unset($data['emotions']);
            if (!empty($data['id'])) {
                $template = Shopware()->Models()->find('Shopware\Models\Emotion\Template', $data['id']);
            } else {
                $template = new \Shopware\Models\Emotion\Template();
            }

            if (!$template instanceof \Shopware\Models\Emotion\Template) {
                return array('success' => false, 'error' => "The passed template id exist no more!");
            }

            $template->fromArray($data);
            Shopware()->Models()->persist($template);
            Shopware()->Models()->flush();

            $result = $this->getTemplate($template->getId());
        } catch (Exception $e) {
            return array('success' => false, 'error' => $e->getMessage());
        }

        return array('success' => true, 'data' => $result);
    }

    /**
     * Helper function to get the array data of a single template.
     * The passed $id parameter is used to identify the template.
     *
     * Success case:
     *  array(Data of the template)
     *
     * Failure case:
     *  null
     *
     * @param null $id
     *
     * @return array
     */
    protected function getTemplate($id)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('template'))
            ->from('Shopware\Models\Emotion\Template', 'template')
            ->where('template.id = :id')
            ->setParameter('id', $id);

        return $builder->getQuery()->getOneOrNullResult(
            \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY
        );
    }

    /**
     * Collects all media related x_types which needs to be normalized
     *
     * @return array
     */
    private function getMediaXTypes()
    {
        $mediaFields = new ArrayCollection([
            'mediaselectionfield',
            'mediatextfield'
        ]);

        $mediaFields = $this->get('events')->collect('Shopware_Plugin_Collect_MediaXTypes', $mediaFields);

        return $mediaFields->toArray();
    }
}
