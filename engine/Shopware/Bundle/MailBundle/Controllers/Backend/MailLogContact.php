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

namespace Shopware\Bundle\MailBundle\Controllers\Backend;

use Shopware\Models\Mail\Contact;

class MailLogContact extends \Shopware_Controllers_Backend_Application
{
    /**
     * {@inheritdoc}
     */
    protected $model = Contact::class;

    /**
     * {@inheritdoc}
     */
    protected $alias = 'mailLogContact';

    /**
     * {@inheritdoc}
     */
    protected function getList($offset, $limit, $sort = [], $filter = [], array $wholeParams = [])
    {
        if (!array_key_exists('id', $wholeParams)) {
            return parent::getList($offset, $limit, $sort, $filter, $wholeParams);
        }

        $filter[] = [
            'property' => 'id',
            'operator' => '=',
            'value' => $wholeParams['id'],
        ];

        return parent::getList($offset, $limit, $sort, $filter, $wholeParams);
    }

    /**
     * {@inheritdoc}
     */
    protected function initAcl(): void
    {
        $this->addAclPermission('index', 'read', 'Insufficient permissions');
        $this->addAclPermission('load', 'read', 'Insufficient permissions');
        $this->addAclPermission('list', 'read', 'Insufficient permissions');
        $this->addAclPermission('detail', 'read', 'Insufficient permissions');
        $this->addAclPermission('create', 'manage', 'Insufficient permissions');
        $this->addAclPermission('update', 'manage', 'Insufficient permissions');
        $this->addAclPermission('delete', 'manage', 'Insufficient permissions');
    }

    /**
     * {@inheritdoc}
     */
    protected function addAclPermission($action, $privilege, $errorMessage = ''): void
    {
        parent::addAclPermission($action, $privilege, $errorMessage);

        /*
         * Set this controllers resource to maillog, since a separate resource
         * isn't needed for log-entries and contacts.
         */
        $this->aclPermissions[$action]['resource'] = 'maillog';
    }
}
