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

use Shopware\Models\Property\Group;
use Shopware\Models\Property\Option;
use Shopware\Models\Property\Value;

/**
 * Shopware Backend Controller for the property module
 */
class Shopware_Controllers_Backend_Property extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Entity Manager
     *
     * @var null
     */
    protected $manager = null;

    /**
     * @var \Shopware\Models\Property\Repository
     */
    protected $propertyRepository = null;

    /**
     * returns the groups for the sets grids
     */
    public function getSetsAction()
    {
        $limit = intval($this->Request()->limit);
        $offset = intval($this->Request()->start);
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
     * returns the groups for the sets grids
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

        try {
            Shopware()->Models()->persist($group);
            Shopware()->Models()->flush();
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
        $group = Shopware()->Models()->getRepository('Shopware\Models\Property\Group')->find($id);
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

        /* @var $group Group */
        $group = $this->get('models')->getRepository(Group::class)->find($id);
        if (!$group) {
            $this->View()->assign(['success' => false, 'message' => 'Group not found']);

            return;
        }

        try {
            $this->get('models')->remove($group);
            $this->get('models')->flush();

            $this->removeSetRelationsFromArticles($id);
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

        /* @var $group Group */
        $group = Shopware()->Models()->getRepository('Shopware\Models\Property\Group')->find($setId);
        if (!$group) {
            $this->View()->assign(['success' => false, 'message' => 'Group not found']);

            return;
        }

        /* @var $option Option */
        $option = Shopware()->Models()->getReference('Shopware\Models\Property\Option', $optionId);
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

        /* @var $group Group */
        $group = Shopware()->Models()->getRepository('Shopware\Models\Property\Group')->find($groupId);
        if (!$group) {
            $this->View()->assign(['success' => false, 'message' => 'Group not found']);

            return;
        }

        /* @var $option Option */
        $option = Shopware()->Models()->getRepository('Shopware\Models\Property\Option')->find($optionId);
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
        $limit = intval($this->Request()->limit);
        $offset = intval($this->Request()->start);
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

        try {
            Shopware()->Models()->persist($option);
            Shopware()->Models()->flush();
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

        /* @var $option Option */
        $option = Shopware()->Models()->getRepository('Shopware\Models\Property\Option')->find($id);
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

        /* @var $option Option */
        $option = Shopware()->Models()->getRepository('Shopware\Models\Property\Option')->find($id);
        if (!$option) {
            $this->View()->assign(['success' => false, 'message' => 'Snippet not found']);

            return;
        }

        try {
            // Cascades remove to associated values
            Shopware()->Models()->remove($option);
            Shopware()->Models()->flush();
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
        if (!($optionId = $this->Request()->getParam('optionId'))) {
            $this->View()->assign(['success' => false, 'message' => 'OptionId not found']);

            return;
        }

        /* @var $option Option */
        $option = Shopware()->Models()->getReference('Shopware\Models\Property\Option', $optionId);
        if (!$option) {
            $this->View()->assign(['success' => false, 'message' => 'Option not found']);

            return;
        }

        $postValue = $this->Request()->getPost('value');
        $value = new Value($option, $postValue);

        try {
            Shopware()->Models()->persist($value);
            Shopware()->Models()->flush();
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $data = Shopware()->Models()->toArray($value);
        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    public function updateOptionAction()
    {
        if (!($id = $this->Request()->getParam('id'))) {
            $this->View()->assign(['success' => false, 'message' => 'Id not found']);

            return;
        }

        /* @var $value Value */
        $value = Shopware()->Models()->getRepository('Shopware\Models\Property\Value')->find($id);
        if (!$value) {
            $this->View()->assign(['success' => false, 'message' => 'Value not found']);

            return;
        }

        $value->setValue($this->Request()->getPost('value'));

        if ($this->Request()->has('mediaId') && $this->Request()->getParam('mediaId', null)) {
            $media = $this->get('models')->find('Shopware\Models\Media\Media', $this->Request()->getPost('mediaId'));
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

        /* @var $value Value */
        $value = Shopware()->Models()->getRepository('Shopware\Models\Property\Value')->find($id);
        if (!$value) {
            $this->View()->assign(['success' => false, 'message' => 'Value not found']);

            return;
        }

        try {
            Shopware()->Models()->remove($value);
            Shopware()->Models()->flush();
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

        foreach ($data as $row) {
            Shopware()->Db()->update(
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
        $qb->update('Shopware\Models\Property\Value', 'value')
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
        $data = $this->Request()->getParam('data');
        $setId = $this->Request()->getParam('setId');
        $positions = json_decode($data);

        foreach ($positions as $position => $valueId) {
            $test[] = ([['position' => $position, 'optionId' => $valueId, 'groupId' => $setId]]);

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
            $this->propertyRepository = Shopware()->Models()->getRepository('Shopware\Models\Property\Group');
        }

        return $this->propertyRepository;
    }

    /**
     * Internal helper function to get access to the entity manager.
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
    private function removeSetRelationsFromArticles($filterSetId)
    {
        $sql = 'UPDATE s_articles SET filtergroupID = null WHERE filtergroupID = ?';
        $this->get('dbal_connection')->executeUpdate($sql, [$filterSetId]);
    }
}
