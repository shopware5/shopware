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

use Shopware\Components\Api\Exception\CustomValidationException;
use Shopware\Components\Api\Resource\User;
use Shopware\Components\Random;

class Shopware_Controllers_Api_Users extends Shopware_Controllers_Api_Rest
{
    /**
     * @var User
     */
    protected $resource;

    public function __construct(User $user)
    {
        $this->resource = $user;
        parent::__construct();
    }

    /**
     * Get list of users
     *
     * GET /api/users/
     */
    public function indexAction(): void
    {
        $limit = (int) $this->Request()->getParam('limit', 1000);
        $offset = (int) $this->Request()->getParam('start', 0);
        $sort = $this->Request()->getParam('sort', []);
        $filter = $this->Request()->getParam('filter', []);

        $result = $this->resource->getList($offset, $limit, $filter, $sort);

        $this->View()->assign($result);
        $this->View()->assign('success', true);
    }

    /**
     * Get one user
     *
     * GET /api/users/{id}
     */
    public function getAction(): void
    {
        $id = (int) $this->Request()->getParam('id');

        $user = $this->resource->getOne($id);

        $this->View()->assign('data', $user);
        $this->View()->assign('success', true);
    }

    /**
     * Create new user
     *
     * POST /api/users
     */
    public function postAction(): void
    {
        if (!$this->Request()->getParam('password')) {
            $passwordPlain = Random::generatePassword();
            $this->Request()->setPost('password', $passwordPlain);
        }

        if ($this->Request()->getParam('apiKey') && strlen($this->Request()->getParam('apiKey')) < 40) {
            throw new CustomValidationException('apiKey is too short. The minimal length is 40.');
        }

        $user = $this->resource->create($this->Request()->getPost());

        $location = $this->apiBaseUrl . 'users/' . $user->getId();
        $data = [
            'id' => $user->getId(),
            'location' => $location,
        ];
        if (isset($passwordPlain)) {
            $data['password'] = $passwordPlain;
        }

        $this->View()->assign(['success' => true, 'data' => $data]);
        $this->Response()->headers->set('location', $location);
    }

    /**
     * Update user
     *
     * PUT /api/users/{id}
     */
    public function putAction(): void
    {
        $id = (int) $this->Request()->getParam('id');

        if ($this->Request()->getParam('apiKey') && strlen($this->Request()->getParam('apiKey')) < 40) {
            throw new CustomValidationException('apiKey is too short. The minimal length is 40.');
        }

        $params = $this->Request()->getPost();

        $user = $this->resource->update($id, $params);

        $location = $this->apiBaseUrl . 'users/' . $user->getId();
        $data = [
            'id' => $user->getId(),
            'location' => $location,
        ];

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Delete user
     *
     * DELETE /api/users/{id}
     */
    public function deleteAction(): void
    {
        $id = (int) $this->Request()->getParam('id');

        $container = $this->container;

        if (!$container->initialized('auth')) {
            $this->View()->assign(['success' => false, 'errorMsg' => 'Auth not initialized.']);

            return;
        }

        $currentUser = (int) $container->get('auth')->getIdentity()->id;

        if ($currentUser === $id) {
            $this->View()->assign([
                'success' => false,
                'errorMsg' => 'For safety reasons, it is prohibited for a user to delete itself.',
            ]);

            return;
        }

        $this->resource->delete($id);

        $this->View()->assign(['success' => true]);
    }
}
