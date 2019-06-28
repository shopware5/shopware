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

use Shopware\Models\Site\Site;

class Shopware_Controllers_Backend_Site extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Entity Manager
     *
     * @var \Shopware\Components\Model\ModelManager
     */
    protected $manager;

    /**
     * @var \Shopware\Models\Site\Repository
     */
    protected $siteRepository;

    /**
     * required for creating the tree
     * takes a nodeName and creates all children for that particular node
     */
    public function getNodesAction()
    {
        $node = $this->Request()->getParam('node');

        // Create root nodes
        if ($node === 'root') {
            try {
                $query = $this->getSiteRepository()->getGroupListQuery();
                $sites = $query->getArrayResult();
                $this->View()->assign(['success' => true, 'nodes' => $sites]);
            } catch (Exception $e) {
                $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            try {
                // Call the getSitesByNodeName helper function, which will return an array containing all children of that node
                $sites = $this->getSitesByNodeName($node);

                // Hand that array to the view
                $this->View()->assign(['success' => true, 'nodes' => $sites]);
            } catch (Exception $e) {
                // Catch all errors
                $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
            }
        }
    }

    /**
     * this function enables the user to create groups
     * after taking a groupName and a templateVariable, it will check if either one already exists and if so, throw an exception
     * otherwise it will append the new group to cmsPositions in s_core_config
     */
    public function createGroupAction()
    {
        $manager = $this->getManager();
        $repository = $manager->getRepository(\Shopware\Models\Site\Group::class);
        $data = $this->Request()->getPost();
        $data = isset($data[0]) ? array_pop($data) : $data;

        $name = empty($data['groupName']) ? null : $data['groupName'];
        $key = empty($data['templateVar']) ? null : $data['templateVar'];

        if ($key === null) {
            $this->View()->assign([
                'success' => false,
                'message' => 'Template Variable may not be empty',
            ]);

            return;
        }

        if ($name === null) {
            $this->View()->assign([
                'success' => false,
                'message' => 'Name may not be empty',
            ]);

            return;
        }

        // Check if name exists
        $model = $repository->findOneBy(['name' => $name]);
        if ($model !== null) {
            $this->View()->assign([
                'success' => false,
                'message' => 'nameExists',
            ]);

            return;
        }

        // Check if key exists
        $model = $repository->findOneBy(['key' => $key]);
        if ($model === null) {
            $model = new \Shopware\Models\Site\Group();
            $model->setKey($key);
        } else {
            $this->View()->assign([
                'success' => false,
                'message' => 'variableExists',
            ]);

            return;
        }
        $model->setName($name);

        $manager->persist($model);
        $manager->flush();

        $this->View()->assign(['success' => true]);
    }

    /**
     * This function enables the user to delete groups
     * after taking the $templateVariable, it will get all groups and remove the requested group based on the tag
     * it will then update s_core_config accordingly
     * it will also move any orphans to the group gDisabled
     */
    public function deleteGroupAction()
    {
        $manager = $this->getManager();
        $repository = $manager->getRepository(\Shopware\Models\Site\Group::class);

        $data = $this->Request()->getPost();
        $data = isset($data[0]) ? array_pop($data) : $data;

        $key = empty($data['templateVar']) ? null : $data['templateVar'];

        /** @var \Shopware\Models\Site\Group $model */
        $model = $repository->findOneBy(['key' => $key]);
        if ($model !== null) {
            $manager->remove($model);
            $manager->flush();
        }

        try {
            // First, get an array containing all sites id and grouping
            $sites = Shopware()->Db()->fetchAssoc('SELECT id,grouping FROM s_cms_static');

            // Check is associated with the requested group
            // If so, either just delete it or, if the site would become an orphan, move it to the group disabled
            foreach ($sites as $site) {
                // Try to explode into an array
                $groups = explode('|', $site['grouping']);

                // If we only have one group, exploding isn't possible, thus we create the array
                (count($groups) == 1) ? $groups = [$site['grouping']] : null;

                // If the current site is associated with the requested group and has no other groups
                if (in_array($key, $groups) && count($groups) == 1) {
                    //set group to gDisabled to prevent orphanage
                    Shopware()->Db()->query('UPDATE s_cms_static SET grouping = ? WHERE id = ?',
                        ['disabled', $site['id']]);
                } // If the current site is associated with the requested group and does have other associations
                else {
                    if (in_array($key, $groups) && count($groups) > 1) {
                        // Remove the requested group from the groupings field
                        $site['grouping'] = str_replace($key, '', $site['grouping']);
                        $site['grouping'] = str_replace('|', '', $site['grouping']);

                        // Update the table
                        $sql = 'UPDATE s_cms_static SET grouping = ? WHERE id = ?';
                        Shopware()->Db()->query($sql, [$site['grouping'], $site['id']]);
                    }
                }
            }
            // Success
            $this->View()->assign(['success' => true]);
        } catch (Exception $e) {
            // Catch all errors
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * this function enables the user to delete a site
     * it will take the site id and simply remove it from the model
     * it will also set the parentID of all children of that site to zero
     */
    public function deleteSiteAction()
    {
        //get id
        $params = $this->Request()->getParams();
        $siteId = empty($params['siteId']) ? null : $params['siteId'];

        if (!empty($siteId)) {
            try {
                //remove site
                $model = $this->getSiteRepository()->find($siteId);
                $this->getManager()->remove($model);

                $this->getManager()->flush();

                //set the parentID of all children to 0
                //we don't want orphans
                $sql = 'UPDATE s_cms_static SET parentID = 0 WHERE parentID = ?';
                Shopware()->Db()->query($sql, [$siteId]);

                //hand siteId to view
                $this->View()->assign(['success' => true, 'data' => $siteId]);
            } catch (Exception $e) {
                //catch all errors
                $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
            }
        }
    }

    /**
     * this function enables the user to save or update a site
     * depending on whether or not a helperId is set,
     * it will either update a site with new values or create a new one, using the values provided
     */
    public function saveSiteAction()
    {
        //get the id from the helperId field
        $params = $this->Request()->getParams();
        $siteId = empty($params['helperId']) ? null : $params['helperId'];

        if (empty($params['shopIds'])) {
            $params['shopIds'] = null;
        }

        // This was a javascript array
        // Change it back to the actual db format
        $params['grouping'] = str_replace(',', '|', $params['grouping']);

        // Check whether we create a new site or are updating one
        // Also, check if we have the necessary rights
        try {
            if (!empty($siteId)) {
                if (!$this->_isAllowed('updateSite', 'site')) {
                    $this->View()->assign(['success' => false, 'message' => 'Permission denied.']);

                    return;
                }

                $site = $this->getSiteRepository()->find($siteId);
            } else {
                if (!$this->_isAllowed('createSite', 'site')) {
                    $this->View()->assign(['success' => false, 'message' => 'Permission denied.']);

                    return;
                }

                $site = new Site();
            }

            $site->fromArray($params);
            $site->setChanged();

            $this->getManager()->persist($site);

            $this->getManager()->flush();

            $data = $this->getSiteRepository()
                ->getSiteQuery($site->getId())
                ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

            $this->View()->assign(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            // Catch all errors
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Builds an array containing all groups to be displayed in the itemSelectorField
     */
    public function getGroupsAction()
    {
        try {
            $grouping = $this->Request()->getParam('grouping');
            $grouping = explode('|', $grouping);

            $query = $this->getSiteRepository()->getGroupListQuery();
            $groups = $query->getArrayResult();

            foreach ($groups as $groupKey => $group) {
                if (in_array($group['key'], $grouping)) {
                    unset($groups[$groupKey]);
                }
            }
            $groups = array_values($groups);

            $this->View()->assign(['success' => true, 'groups' => $groups]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getSelectedGroupsAction()
    {
        try {
            $grouping = $this->Request()->getParam('grouping');
            $grouping = explode('|', $grouping);

            $query = $this->getSiteRepository()->getGroupListQuery();
            $groups = $query->getArrayResult();

            foreach ($groups as $groupKey => $group) {
                if (!in_array($group['key'], $grouping)) {
                    unset($groups[$groupKey]);
                }
            }
            $groups = array_values($groups);

            $this->View()->assign(['success' => true, 'groups' => $groups]);
        } catch (Exception $e) {
            // Catch all errors
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Registers the different acl permission for the different controller actions.
     */
    protected function initAcl()
    {
        /*
         * Permission to create a Group
         */
        $this->addAclPermission('createGroup', 'createGroup', 'Insufficient Permissions');

        /*
         * Permission to delete a site
         */
        $this->addAclPermission('deleteSite', 'deleteSite', 'Insufficient Permissions');

        /*
         * Permission to delete a group
         */
        $this->addAclPermission('deleteGroup', 'deleteGroup', 'Insufficient Permissions');

        /*
         * Permission to get nodes / read
         */
        $this->addAclPermission('getNodes', 'read', 'Insufficient Permissions');

        /*
         * Permission to create a site
         */
        $this->addAclPermission('saveSite', 'updateSite', 'Insufficient Permissions');
    }

    /**
     * Helper function to get access to the site repository.
     *
     * @return \Shopware\Models\Site\Repository
     */
    private function getSiteRepository()
    {
        if ($this->siteRepository === null) {
            $this->siteRepository = Shopware()->Models()->getRepository(\Shopware\Models\Site\Site::class);
        }

        return $this->siteRepository;
    }

    /**
     * Internal helper function to get access to the entity manager.
     *
     * @return Shopware\Components\Model\ModelManager
     */
    private function getManager()
    {
        if ($this->manager === null) {
            $this->manager = Shopware()->Models();
        }

        return $this->manager;
    }

    /**
     * Helper function to build children of a node $nodeName
     *
     * @param string $nodeName
     *
     * @return array|bool
     */
    private function getSitesByNodeName($nodeName)
    {
        if (!empty($nodeName)) {
            $sites = $this->getSiteRepository()
                ->getSitesByNodeNameQuery($nodeName)
                ->getResult();

            $nodes = [];

            foreach ($sites as $site) {
                // Call getSiteNode helper function to build the final array structure
                $nodes[] = $this->getSiteNode($nodeName . '_', $site);
            }

            return $nodes;
        }

        return false;
    }

    /**
     * Helper function to build the final array to be handed to the view
     *
     * @param string $idPrefix
     *
     * @return array
     */
    private function getSiteNode($idPrefix, Site $site)
    {
        // Set icons
        if ($site->getLink()) {
            $iconCls = 'sprite-chain-small';
        } else {
            $iconCls = 'sprite-blue-document-text';
        }

        // Build the structure
        $node = [
            'id' => $idPrefix . $site->getId(),
            'active' => $site->getActive(),
            'text' => $site->getDescription() . '(' . $site->getId() . ')',
            'helperId' => $site->getId(),
            'iconCls' => $iconCls,
            'tpl1variable' => $site->getTpl1Variable(),
            'tpl1path' => $site->getTpl1Path(),
            'tpl2variable' => $site->getTpl2Variable(),
            'tpl2path' => $site->getTpl2Path(),
            'tpl3variable' => $site->getTpl3Variable(),
            'tpl3path' => $site->getTpl3Path(),
            'description' => $site->getDescription(),
            'pageTitle' => $site->getPageTitle(),
            'metaKeywords' => $site->getMetaKeywords(),
            'metaDescription' => $site->getMetaDescription(),
            'html' => $site->getHtml(),
            'grouping' => $site->getGrouping(),
            'position' => $site->getPosition(),
            'link' => $site->getLink(),
            'target' => $site->getTarget(),
            'shopIds' => $site->getShopIds(),
            'changed' => $site->getChanged(),
            'parentId' => $site->getParentId(),
            'leaf' => true,
        ];

        // If the site has children, append them
        if ($site->getChildren()->count() > 0) {
            $children = [];
            foreach ($site->getChildren() as $child) {
                $children[] = $this->getSiteNode($idPrefix . $site->getId() . '_', $child);
            }
            $node['nodes'] = $children;
            $node['leaf'] = false;
        }

        return $node;
    }
}
