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

use Shopware\Models\Media\Media;
use Shopware\Models\Property\Group;
use Shopware\Models\Property\Option;
use Shopware\Models\Property\Value;

class Shopware_Controllers_Backend_Property extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Entity Manager
     *
     * @var \Shopware\Components\Model\ModelManager
     */
    protected $manager;

    /**
     * @var \Shopware\Models\Property\Repository
     */
    protected $propertyRepository;

    /**
     * Returns the groups for the sets grids
     */
    public function getSetsAction()
    {
        $limit = (int) $this->Request()->getParam('limit', 0);
        $offset = (int) $this->Request()->getParam('start', 0);
        $filter = $this->Request()->getParam('filter', []);
        $query = $this->getPropertyRepository()->getSetsQuery($offset, $limit, $filter);
        $totalCount = $this->getManager()->getQueryCount($query);
        $sets = $query->getArrayResult();

        $this->View()->assign(
            [
                'success' => true,
                'data' => $sets,
                'total' => $totalCount,
            ]
        );
    }

    /**
     * Returns the groups for the sets grids
     */
    public function getSetAssignsAction()
    {
        $setId = $this->Request()->getParam('setId');

        $query = $this->getPropertyRepository()->getSetAssignsQuery($setId);
        $assignments = $query->getArrayResult();

        $this->View()->assign(
            [
                'success' => true,
                'data' => $assignments,
                'total' => count($assignments),
            ]
        );
    }

    public function createSetAction()
    {
        $params = $this->Request()->getPost();

        $group = new Group();
        $group->fromArray($params);

        $modelManager = $this->get('models');
        try {
            $modelManager->persist($group);
            $modelManager->flush();
        } catch (\Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }
        $data = $this->getPropertyRepository()
                     ->getGroupDetailQuery($group->getId())
                     ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Updates one group identified by its identifier
     */
    public function updateSetAction()
    {
        if (!($id = $this->Request()->getParam('id'))) {
            $this->View()->assign(['success' => false, 'message' => 'Id not found']);

            return;
        }

        /* @var $group Group */
        $group = Shopware()->Models()->getRepository(Group::class)->find($id);
        if (!$group) {
            $this->View()->assign(['success' => false, 'message' => 'Group not found']);

            return;
        }

        $params = $this->Request()->getPost();
        $group->fromArray($params);

        try {
            Shopware()->Models()->flush();
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $data = $this->getPropertyRepository()
                     ->getGroupDetailQuery($group->getId())
                     ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    public function deleteSetAction()
    {
        if (!($id = $this->Request()->getParam('id'))) {
            $this->View()->assign(['success' => false, 'message' => 'Id not found']);

            return;
        }

        /* @var Group $group */
        $group = $this->get('models')->getRepository(Group::class)->find($id);
        if (!$group) {
            $this->View()->assign(['success' => false, 'message' => 'Group not found']);

            return;
        }

        $modelManager = $this->get('models');
        try {
            $modelManager->remove($group);
            $modelManager->flush();

            $this->removeSetRelationsFromProducts($id);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $this->View()->assign(['success' => true]);
    }

    public function onAddAssignmentAction()
    {
        if (!($optionId = $this->Request()->getParam('optionId'))) {
            $this->View()->assign(['success' => false, 'message' => 'OptionId not found']);

            return;
        }

        if (!($setId = $this->Request()->getParam('setId'))) {
            $this->View()->assign(['success' => false, 'message' => 'SetId not found']);

            return;
        }

        /* @var Group $group */
        $group = Shopware()->Models()->getRepository(Group::class)->find($setId);
        if (!$group) {
            $this->View()->assign(['success' => false, 'message' => 'Group not found']);

            return;
        }

        /* @var Option|null $option */
        $option = Shopware()->Models()->getReference(Option::class, $optionId);
        if (!$option) {
            $this->View()->assign(['success' => false, 'message' => 'Option not found']);

            return;
        }

        $group->addOption($option);

        try {
            Shopware()->Models()->flush();
        } catch (\Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $this->View()->assign(['success' => true]);
    }

    public function removeOptionFromGroupAction()
    {
        if (!($optionId = $this->Request()->getParam('optionId'))) {
            $this->View()->assign(['success' => false, 'message' => 'OptionId not found']);

            return;
        }

        if (!($groupId = $this->Request()->getParam('groupId'))) {
            $this->View()->assign(['success' => false, 'message' => 'GroupId not found']);

            return;
        }

        /* @var Group $group */
        $group = Shopware()->Models()->getRepository(Group::class)->find($groupId);
        if (!$group) {
            $this->View()->assign(['success' => false, 'message' => 'Group not found']);

            return;
        }

        /* @var Option $option */
        $option = Shopware()->Models()->getRepository(Option::class)->find($optionId);
        if (!$option) {
            $this->View()->assign(['success' => false, 'message' => 'Option not found']);

            return;
        }

        $group->removeOption($option);

        try {
            Shopware()->Models()->flush();
        } catch (\Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $this->View()->assign(['success' => true]);
    }

    /**
     * returns all groups(options) for the backend module
     */
    public function getGroupsAction()
    {
        $limit = (int) $this->Request()->getParam('limit', 0);
        $offset = (int) $this->Request()->getParam('start', 0);
        $filter = $this->Request()->getParam('filter', []);
        $query = $this->getPropertyRepository()->getOptionsQuery($offset, $limit, $filter);
        $totalCount = $this->getManager()->getQueryCount($query);
        $options = $query->getArrayResult();

        $this->View()->assign([
           'success' => true,
           'data' => $options,
           'total' => $totalCount,
        ]);
    }

    public function createGroupAction()
    {
        $params = $this->Request()->getPost();

        $option = new Option();
        $option->fromArray($params);

        $modelManager = $this->get('models');
        try {
            $modelManager->persist($option);
            $modelManager->flush();
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $data = Shopware()->Models()->toArray($option);
        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Updates one option identified by its identifier
     */
    public function updateGroupAction()
    {
        if (!($id = $this->Request()->getParam('id'))) {
            $this->View()->assign(['success' => false, 'message' => 'Id not found']);

            return;
        }

        /* @var Option $option */
        $option = Shopware()->Models()->getRepository(Option::class)->find($id);
        if (!$option) {
            $this->View()->assign(['success' => false, 'message' => 'Option not found']);

            return;
        }

        $option->fromArray($this->Request()->getPost());

        try {
            Shopware()->Models()->flush();
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $data = Shopware()->Models()->toArray($option);
        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Deletes one option identified by its identifier
     *
     * ALso deletes associated values due to cascade={"remove"})
     */
    public function deleteGroupAction()
    {
        if (!($id = $this->Request()->getParam('id'))) {
            $this->View()->assign(['success' => false, 'message' => 'Id not found']);

            return;
        }

        /* @var Option $option */
        $option = Shopware()->Models()->getRepository(Option::class)->find($id);
        if (!$option) {
            $this->View()->assign(['success' => false, 'message' => 'Snippet not found']);

            return;
        }

        $modelManager = $this->get('models');
        try {
            // Cascades remove to associated values
            $modelManager->remove($option);
            $modelManager->flush();
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $this->View()->assign(['success' => true]);
    }

    public function getOptionsAction()
    {
        if (!($optionId = $this->Request()->getParam('optionId'))) {
            $this->View()->assign(['success' => false, 'message' => 'OptionId not found']);

            return;
        }

        $values = $this->getPropertyRepository()
                       ->getPropertyValueByOptionIdQuery($optionId)
                       ->getArrayResult();

        $this->View()->assign([
            'success' => true,
            'data' => $values,
            'total' => count($values),
        ]);
    }

    public function createOptionAction()
    {
        $request = $this->Request();
        if (!($optionId = $request->getParam('optionId'))) {
            $this->View()->assign(['success' => false, 'message' => 'OptionId not found']);

            return;
        }

        /* @var Option|null $option */
        $option = Shopware()->Models()->getReference(Option::class, $optionId);
        if (!$option) {
            $this->View()->assign(['success' => false, 'message' => 'Option not found']);

            return;
        }

        $postValue = $request->getPost('value');
        $value = new Value($option, $postValue);

        $modelManager = $this->get('models');
        try {
            $modelManager->persist($value);
            $modelManager->flush();
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $data = Shopware()->Models()->toArray($value);
        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    public function updateOptionAction()
    {
        $request = $this->Request();
        if (!($id = $request->getParam('id'))) {
            $this->View()->assign(['success' => false, 'message' => 'Id not found']);

            return;
        }

        /* @var Value $value */
        $value = Shopware()->Models()->getRepository(Value::class)->find($id);
        if (!$value) {
            $this->View()->assign(['success' => false, 'message' => 'Value not found']);

            return;
        }

        $value->setValue($request->getPost('value'));

        if ($request->has('mediaId') && $request->getParam('mediaId')) {
            $media = $this->get('models')->find(Media::class, $request->getPost('mediaId'));
            $value->setMedia($media);
        } else {
            $value->setMedia(null);
        }

        try {
            Shopware()->Models()->flush();
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $query = $this->getPropertyRepository()->getPropertyValueByOptionIdQueryBuilder(1);
        $query->where('value.id = ?0')
            ->setParameter(0, $value->getId());

        $data = $query->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Deletes one value identified by its identifier
     */
    public function deleteOptionAction()
    {
        if (!($id = $this->Request()->getParam('id'))) {
            $this->View()->assign(['success' => false, 'message' => 'Id not found']);

            return;
        }

        /* @var Value $value */
        $value = Shopware()->Models()->getRepository(Value::class)->find($id);
        if (!$value) {
            $this->View()->assign(['success' => false, 'message' => 'Value not found']);

            return;
        }

        $modelManager = $this->get('models');
        try {
            $modelManager->remove($value);
            $modelManager->flush();
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $this->View()->assign(['success' => true]);
    }

    /**
     * Changes Position of option in group
     */
    public function changeGroupPositionAction()
    {
        $data = $this->Request()->getParam('data');
        $data = json_decode($data, true);

        $db = $this->get('db');
        foreach ($data as $row) {
            $db->update(
                's_filter_relations',
                ['position' => $row['position']],
                ['groupID = ?' => $row['groupId'], 'optionID = ?' => $row['optionId']]
            );
        }

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Changes Position of field
     */
    public function changeOptionPositionAction()
    {
        $data = $this->Request()->getParam('data');
        $positions = json_decode($data);

        $qb = $this->getManager()->createQueryBuilder();
        $qb->update(Value::class, 'value')
           ->andWhere('value.id = :valueId');

        foreach ($positions as $position => $valueId) {
            $qb->set('value.position', $position)
               ->setParameter('valueId', $valueId)
               ->getQuery()
               ->execute();
        }

        $this->View()->assign(['success' => true]);
    }

    /**
     * Changes Position of the set assignments
     */
    public function changeAssignmentPositionAction()
    {
        $request = $this->Request();
        $data = $request->getParam('data');
        $setId = $request->getParam('setId');

        foreach (json_decode($data) as $position => $valueId) {
            $test[] = [['position' => $position, 'optionId' => $valueId, 'groupId' => $setId]];

            Shopware()->Db()->update(
                's_filter_relations',
                ['position' => $position],
                ['groupID = ?' => $setId, 'optionID = ?' => $valueId]
            );
        }

        $this->View()->assign(['success' => true]);
    }

    /**
     * Helper function to get access to the property repository.
     *
     * @return \Shopware\Models\Property\Repository
     */
    private function getPropertyRepository()
    {
        if ($this->propertyRepository === null) {
            $this->propertyRepository = Shopware()->Models()->getRepository(Group::class);
        }

        return $this->propertyRepository;
    }

    /**
     * Internal helper function to get access to the entity manager.
     *
     * @return \Shopware\Components\Model\ModelManager
     */
    private function getManager()
    {
        if ($this->manager === null) {
            $this->manager = Shopware()->Models();
        }

        return $this->manager;
    }

    /**
     * @param int $filterSetId
     */
    private function removeSetRelationsFromProducts($filterSetId)
    {
        $sql = 'UPDATE s_articles SET filtergroupID = null WHERE filtergroupID = ?';
        $this->get('dbal_connection')->executeUpdate($sql, [$filterSetId]);
    }
}
