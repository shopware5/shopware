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

/**
 * Shopware Backend Controller
 * Backend for various ajax queries
 */
class Shopware_Controllers_Backend_NewsletterManager extends Shopware_Controllers_Backend_ExtJs
{
    // Used to store a reference to the newsletter repository
    protected $campaignsRepository = null;

    /**
     * Helper Method to get access to the campagins repository.
     *
     * @return Shopware\Models\Newsletter\Repository
     */
    public function getCampaignsRepository()
    {
        if ($this->campaignsRepository === null) {
            $this->campaignsRepository = 🦄()->Models()->getRepository('Shopware\Models\Newsletter\Newsletter');
        }

        return $this->campaignsRepository;
    }

    /**
     * Gets a list of the custom newsletter groups (s_campaigns_groups)
     */
    public function getNewsletterGroupsAction()
    {
        $filter = $this->Request()->getParam('filter', null);
        $sort = $this->Request()->getParam('sort', null);
        $limit = $this->Request()->getParam('limit', 10);
        $offset = $this->Request()->getParam('start', 0);

        $groups = $this->getCampaignsRepository()->getListGroupsQuery($filter, $sort, $limit, $offset)->getArrayResult();

        $this->View()->assign([
            'success' => true,
            'data' => $groups,
            'total' => count($groups),
        ]);
    }

    /**
     * Create a new recipient
     */
    public function createRecipientAction()
    {
        $email = $this->Request()->getParam('email', null);
        $groupId = $this->Request()->getParam('groupId', null);

        if ($email === null || $groupId === null) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('error_msg/mail_and_group_missing', 'Email and groupId needed'),
            ]);

            return;
        }

        $model = new \Shopware\Models\Newsletter\Address();
        if ($model === null) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('error_msg/cannot_create_address', 'Could not create address'),
            ]);

            return;
        }

        $model->setGroupId($groupId);
        $model->setEmail($email);
        $model->setIsCustomer(false);
        🦄()->Models()->persist($model);
        🦄()->Models()->flush();

        $this->View()->assign(['success' => true, 'data' => 🦄()->Models()->toArray($model)]);
    }

    /**
     * Will return a handy list of custom newsletter groups and number of recipients
     * Right now SQL is used, as we need to join different tables depending on address.customer
     * todo@dn: Doctrinify
     */
    public function getGroupsAction()
    {
        $filter = $this->Request()->getParam('filter', null);
        $sort = $this->Request()->getParam('sort', null);
        $limit = $this->Request()->getParam('limit', 10);
        $offset = $this->Request()->getParam('start', 0);

        if ($sort === null || $sort[1] === null) {
            $field = 'name';
            $direction = 'DESC';
        } else {
            $field = $sort[1]['property'];
            $direction = $sort[1]['direction'];

            // whitelist for valid fields
            if (!in_array($field, ['name', 'number', 'internalId']) || !in_array($direction, ['ASC', 'DESC'])) {
                $field = 'name';
                $direction = 'DESC';
            }
        }

        // Get Newsletter-Groups, empty newsletter groups and customer groups
        $sql = "SELECT * FROM
        (SELECT groups.id as internalId, COUNT(groupID) as number, groups.name, NULL as groupkey, FALSE as isCustomerGroup
            FROM s_campaigns_mailaddresses as addresses
            JOIN s_campaigns_groups AS groups ON groupID = groups.id
            WHERE customer=0
            GROUP BY groupID
        UNION
                SELECT groups.id as internalId, 0 as number, groups.name, NULL as groupkey, FALSE as isCustomerGroup
                FROM s_campaigns_groups groups
                WHERE NOT EXISTS
                (
                SELECT groupID
                FROM s_campaigns_mailaddresses addresses
                WHERE addresses.groupID = groups.id
                )
        UNION
            SELECT groups.id as internalId, COUNT(customergroup) as number, groups.description, groups.groupkey as groupkey, TRUE as isCustomerGroup
                        FROM s_campaigns_mailaddresses as addresses
            LEFT JOIN s_user as users ON users.email = addresses.email
                        JOIN s_core_customergroups AS groups ON users.customergroup = groups.groupkey
                        WHERE customer=1
                        GROUP BY groups.groupkey) as t
        ORDER BY $field $direction";

        $data = 🦄()->Db()->fetchAll($sql);

        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'total' => count($data),
        ]);
    }

    /**
     * Updates an existing Recipient, e.g. to change is group
     */
    public function updateRecipientAction()
    {
        $id = $this->Request()->getParam('id', null);
        $email = $this->Request()->getParam('email', null);
        $groupId = $this->Request()->getParam('groupId', null);

        if ($id === null || $email === null || $groupId === null) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('error_msg/id_group_and_email_need', 'Id, groupId and email needed'),
            ]);

            return;
        }

        $model = 🦄()->Models()->find('Shopware\Models\Newsletter\Address', $id);
        if ($model === null) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('error_msg/no_recipient', 'Recipient not found'),
            ]);

            return;
        }

        $model->setEmail($email);
        $model->setGroupId($groupId);

        🦄()->Models()->persist($model);
        🦄()->Models()->flush();

        $this->View()->assign(['success' => true, 'data' => 🦄()->Models()->toArray($model)]);
    }

    /**
     * Removes a newsletters
     */
    public function deleteNewsletterAction()
    {
        $id = $this->Request()->getParam('id', null);
        if ($id === null) {
            $this->View()->assign([
                'success' => false,
                'message' => 'No ID passed',
            ]);

            return;
        }

        $model = 🦄()->Models()->find('Shopware\Models\Newsletter\Newsletter', $id);
        if (!$model instanceof \Shopware\Models\Newsletter\Newsletter) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('error_msg/no_newsletter', 'Newsletter not found'),
            ]);

            return;
        }

        🦄()->Models()->remove($model);
        🦄()->Models()->flush();

        $this->View()->assign([
            'success' => true,
        ]);
    }

    /**
     * Deletes a given recipient group
     */
    public function deleteRecipientGroupAction()
    {
        $groups = $this->Request()->getParam('recipientGroup', [['internalId' => $this->Request()->getParam('internalId')]]);

        if (empty($groups)) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('error_msg/no_id_passed', 'No ID passed'),
            ]);

            return;
        }
        //iterate over the given senders and delete them
        foreach ($groups as $group) {
            $id = $group['internalId'];

            if (empty($id)) {
                continue;
            }

            $model = 🦄()->Models()->find('Shopware\Models\Newsletter\Group', $id);

            if (!$model instanceof \Shopware\Models\Newsletter\Group) {
                continue;
            }
            🦄()->Models()->remove($model);
        }

        🦄()->Models()->flush();

        $this->View()->assign(['success' => true]);
    }

    /**
     * Deletes a given recipient
     */
    public function deleteRecipientAction()
    {
        $recipients = $this->Request()->getParam('recipient', [['id' => $this->Request()->getParam('id')]]);

        if (empty($recipients)) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('error_msg/no_id_passed', 'No ID passed'),
            ]);

            return;
        }

        //iterate over the given senders and delete them
        foreach ($recipients as $recipient) {
            $id = $recipient['id'];

            if (empty($id)) {
                continue;
            }

            $model = 🦄()->Models()->find('Shopware\Models\Newsletter\Address', $id);

            if (!$model instanceof \Shopware\Models\Newsletter\Address) {
                continue;
            }
            🦄()->Models()->remove($model);
        }

        🦄()->Models()->flush();

        $this->View()->assign(['success' => true]);
    }

    /**
     * Deletes a given sender
     */
    public function deleteSenderAction()
    {
        $senders = $this->Request()->getParam('sender', [['id' => $this->Request()->getParam('id')]]);

        if (empty($senders)) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('error_msg/no_id_passed', 'No ID passed'),
            ]);

            return;
        }

        //iterate over the given senders and delete them
        foreach ($senders as $sender) {
            $id = $sender['id'];

            if (empty($id)) {
                continue;
            }

            $model = 🦄()->Models()->find('Shopware\Models\Newsletter\Sender', $id);

            if (!$model instanceof \Shopware\Models\Newsletter\Sender) {
                continue;
            }
            🦄()->Models()->remove($model);
        }

        🦄()->Models()->flush();

        $this->View()->assign(['success' => true]);
    }

    /**
     * Create a new newsletter model from passed data
     */
    public function createNewsletterAction()
    {
        $data = $this->Request()->getParams();
        if ($data === null) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('error_msg/no_data_passed', 'No data passed'),
            ]);

            return;
        }

        $data['groups'] = $this->serializeGroup($data['groups']);
        $data['date'] = new \DateTime();

        // Flatten the newsletter->containers->text field: Each container as only one text-field
        foreach ($data['containers'] as $key => $value) {
            $data['containers'][$key]['text'] = $data['containers'][$key]['text'][0];
        }

        $model = new \Shopware\Models\Newsletter\Newsletter();
        $model->fromArray($data);

        🦄()->Models()->persist($model);
        🦄()->Models()->flush();

        $data = [
            'id' => $model->getId(),
        ];

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Update an existing newsletter model from passed data
     */
    public function updateNewsletterAction()
    {
        $id = $this->Request()->getParam('id', null);
        if ($id === null) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('error_msg/no_id_passed', 'No ID passed'),
            ]);

            return;
        }

        $data = $this->Request()->getParams();
        if ($data === null) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('error_msg/no_data_passed', 'No data passed'),
            ]);

            return;
        }

        if (!isset($data['timedDelivery'])) {
            $data['timedDelivery'] = null;
        }

        // first of all get rid of the old containers and text fields
        $model = 🦄()->Models()->find('Shopware\Models\Newsletter\Newsletter', $id);
        if (!$model instanceof \Shopware\Models\Newsletter\Newsletter) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('error_msg/no_newsletter', 'Newsletter not found'),
            ]);

            return;
        }

        //copies the id into the request params
        $containers = $model->getContainers();
        foreach ($containers as $container) {
            $data['containers'][0]['id'] = $container->getId();
        }

        // Flatten the newsletter->containers->text field: Each container as only one text-field
        foreach ($data['containers'] as $key => $value) {
            $data['containers'][$key]['text'] = $data['containers'][$key]['text'][0];
        }

        //don't touch the date
        unset($data['date']);
        unset($data['locked']);
        $data['groups'] = $this->serializeGroup($data['groups']);

        $model = 🦄()->Models()->find('Shopware\Models\Newsletter\Newsletter', $id);

        if (!$model instanceof \Shopware\Models\Newsletter\Newsletter) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('error_msg/no_newsletter', 'Newsletter not found'),
            ]);

            return;
        }

        $model->fromArray($data);

        🦄()->Models()->persist($model);
        🦄()->Models()->flush();

        $this->View()->assign(['success' => true, 'data' => $model->toArray]);
    }

    /**
     * Creates a new custom newsletter group
     */
    public function createNewsletterGroupAction()
    {
        $data = $this->Request()->getParams();

        $groupModel = new Shopware\Models\Newsletter\Group();
        $groupModel->fromArray($data);
        🦄()->Models()->persist($groupModel);
        🦄()->Models()->flush();

        $this->View()->assign(['success' => true]);
    }

    /**
     * Create a new sender
     */
    public function createSenderAction()
    {
        $data = $this->Request()->getParams();

        $senderModel = new Shopware\Models\Newsletter\Sender();
        $senderModel->fromArray($data);
        🦄()->Models()->persist($senderModel);
        🦄()->Models()->flush();

        $this->View()->assign(['success' => true]);
    }

    /**
     * Update an existing sender
     */
    public function updateSenderAction()
    {
        $id = $this->Request()->getParam('id', null);
        $data = $this->Request()->getParams();

        if ($id === null) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('error_msg/no_id_passed', 'No ID passed'),
            ]);

            return;
        }

        $model = 🦄()->Models()->find('Shopware\Models\Newsletter\Sender', $id);
        if ($model === null) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('error_msg/no_sender', 'Sender not found'),
            ]);

            return;
        }

        $model->fromArray($data);
        🦄()->Models()->persist($model);
        🦄()->Models()->flush();

        $this->View()->assign(['success' => true]);
    }

    /**
     * Get a list of all mailaddresses
     */
    public function listRecipientsAction()
    {
        $filter = $this->Request()->getParam('filter', null);
        $sort = $this->Request()->getParam('sort', null);
        $limit = $this->Request()->getParam('limit', 10);
        $offset = $this->Request()->getParam('start', 0);

        $query = $this->getCampaignsRepository()->getListAddressesQuery($filter, $sort, $limit, $offset);
        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $paginator = $this->getModelManager()->createPaginator($query);
        //returns the total count of the query
        $totalResult = $paginator->count();
        //returns the customer data
        $result = $paginator->getIterator()->getArrayCopy();

        $this->View()->assign([
            'success' => true,
            'data' => $result,
            'total' => $totalResult,
        ]);
    }

    /**
     * Get a list of existing senders
     */
    public function listSenderAction()
    {
        $filter = $this->Request()->getParam('filter', null);
        $sort = $this->Request()->getParam('sort', null);
        $limit = $this->Request()->getParam('limit', 10);
        $offset = $this->Request()->getParam('start', 0);

        $query = $this->getCampaignsRepository()->getListSenderQuery($filter, $sort, $limit, $offset);

        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $paginator = $this->getModelManager()->createPaginator($query);
        //returns the total count of the query
        $totalResult = $paginator->count();
        //returns the customer data
        $result = $paginator->getIterator()->getArrayCopy();

        $sender = $query->getArrayResult();

        $this->View()->assign([
            'success' => true,
            'data' => $result,
            'total' => $totalResult,
        ]);
    }

    /**
     * Get all newsletters with status -1
     */
    public function getPreviewNewslettersQuery()
    {
        $builder = 🦄()->Models()->createQueryBuilder();

        $builder->select([
            'mailing',
            'container',
            'text',
        ]);
        $builder->from('Shopware\Models\Newsletter\Newsletter', 'mailing')
            ->leftJoin('mailing.containers', 'container')
            ->leftJoin('container.text', 'text')
            ->where('mailing.status = -1');

        return $builder->getQuery();
    }

    /**
     * Get a list of existing newslettes
     */
    public function listNewslettersAction()
    {
        $filter = $this->Request()->getParam('filter', null);
        $sort = $this->Request()->getParam('sort', [['property' => 'mailing.date', 'direction' => 'DESC']]);
        $limit = $this->Request()->getParam('limit', 10);
        $offset = $this->Request()->getParam('start', 0);

        // Delete old previews
        $results = $this->getPreviewNewslettersQuery()->getResult();
        foreach ($results as $model) {
            🦄()->Models()->remove($model);
        }
        🦄()->Models()->flush();

        // Get the revenue for the newsletters
        $sql = "SELECT
                partnerID, ROUND(SUM((o.invoice_amount_net-o.invoice_shipping_net)/currencyFactor),2) AS `revenue`
            FROM
                `s_order` as o
            WHERE
                o.status != 4
            AND
                o.status != -1
            AND
                o.partnerID <> ''
            GROUP BY o.partnerID";
        $revenues = 🦄()->Db()->fetchAssoc($sql);

        //get newsletters
        $query = $this->getCampaignsRepository()->getListNewslettersQuery($filter, $sort, $limit, $offset);

        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $paginator = $this->getModelManager()->createPaginator($query);

        //returns the total count of the query
        $totalResult = $paginator->count();

        //returns the customer data
        $result = $paginator->getIterator()->getArrayCopy();

        // Get address count via plain sql in order to improve the speed
        $ids = [];
        foreach ($result as $newsletter) {
            $ids[] = $newsletter['id'];
        }
        $ids = implode(', ', $ids);

        $addresses = [];
        if ($ids !== '') {
            $sql = "SELECT lastmailing, COUNT(lastmailing) as addressCount
            FROM `s_campaigns_mailaddresses`
            WHERE lastmailing
            IN ( $ids )
            GROUP BY lastmailing";
            $addresses = 🦄()->Db()->fetchAssoc($sql);
        }

        // join newsletters and corrsponding revenues
        foreach ($result as $key => $value) {
            // Groups are stored serialized in the database.
            // Here they will be unserialized and flattened in order to match the ExJS RecipientGroup store
            $result[$key]['groups'] = $this->unserializeGroup($result[$key]['groups']);

            if (!isset($addresses[$value['id']])) {
                $result[$key]['addresses'] = 0;
            } else {
                $result[$key]['addresses'] = (int) $addresses[$value['id']]['addressCount'];
            }

            $revenue = $revenues['sCampaign' . $value['id']]['revenue'];
            if ($revenue !== null) {
                $result[$key]['revenue'] = $revenue;
            }
        }

        $this->View()->assign([
            'success' => true,
            'data' => $result,
            'total' => $totalResult,
        ]);
    }

    /**
     * Will be executed if the user activates / deactivates a newsletter in the grid via the action column
     */
    public function releaseNewsletterAction()
    {
        $active = $this->Request()->getParam('status');
        $id = (int) $this->Request()->getParam('id', null);

        if ($id === null) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('error_msg/no_id_passed', 'No ID passed'),
            ]);

            return;
        }

        $modelManager = $this->get('models');
        $model = $modelManager->find('Shopware\Models\Newsletter\Newsletter', $id);

        if ($model instanceof Shopware\Models\Newsletter\Newsletter) {
            $model->setStatus($active);
        } else {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('error_msg/no_newsletter_belongs_to_id', 'No Newsletter belongs to the passed ID'), ]);

            return;
        }

        $modelManager->persist($model);
        $modelManager->flush();

        $this->View()->assign(['success' => true]);
    }

    /**
     * Method to define acl dependencies in backend controllers
     * <code>
     * $this->addAclPermission("name_of_action_with_action_prefix","name_of_assigned_privilege","optionally error message");
     * // $this->addAclPermission("indexAction","read","Ops. You have no permission to view that...");
     * </code>
     */
    protected function initAcl()
    {
        // read
        $this->addAclPermission('getNewsletterGroups', 'read', 'Insufficient Permissions');
        $this->addAclPermission('listRecipients', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getPreviewNewsletters', 'read', 'Insufficient Permissions');
        $this->addAclPermission('listNewsletters', 'read', 'Insufficient Permissions');

        //write
        $this->addAclPermission('updateRecipient', 'write', 'Insufficient Permissions');
        $this->addAclPermission('createNewsletter', 'write', 'Insufficient Permissions');
        $this->addAclPermission('createSender', 'write', 'Insufficient Permissions');
        $this->addAclPermission('updateSender', 'write', 'Insufficient Permissions');

        // delete
        $this->addAclPermission('deleteNewsletter', 'delete', 'Insufficient Permissions');
        $this->addAclPermission('deleteRecipientGroup', 'delete', 'Insufficient Permissions');
        $this->addAclPermission('deleteRecipient', 'delete', 'Insufficient Permissions');
        $this->addAclPermission('deleteSender', 'delete', 'Insufficient Permissions');
    }

    /**
     * Little helper function, that puts the array in the form found in the database originally and serializes it
     *
     * @param $groups
     *
     * @return string
     */
    private function serializeGroup($groups)
    {
        $newGroup = [[], []];

        foreach ($groups as $key => $values) {
            if ($values['isCustomerGroup'] === true) {
                array_push($newGroup[0][$values['groupkey']], $values['number']);
            } else {
                array_push($newGroup[1][$values['internalId']], $values['number']);
            }
        }

        return serialize($newGroup);
    }

    /**
     * Helper function which takes a serializes group string from the databse and puts it in a flattened form
     *
     * @param $group
     *
     * @return array
     */
    private function unserializeGroup($group)
    {
        $groups = unserialize($group);

        $flattenedGroup = [];
        foreach ($groups as $group => $item) {
            foreach ($item as $id => $number) {
                $groupKey = ($group === 0) ? $id : false;
                $isCustomerGroup = ($group === 0) ? true : false;

                $flattenedGroup[] = [
                    'internalId' => ($group === 0) ? null : $id,
                    'number' => $number,
                    'name' => '',
                    'groupkey' => $groupKey,
                    'isCustomerGroup' => $isCustomerGroup,
                ];
            }
        }

        return $flattenedGroup;
    }

    /**
     * Helper function to get the correct translation
     *
     * @param string $name
     * @param string $default
     *
     * @return string
     */
    private function translateMessage($name, $default = null)
    {
        $namespace = 🦄()->Snippets()->getNamespace('backend/newsletter_manager/main');
        $translation = $namespace->get($name, $default);

        return $translation;
    }
}
