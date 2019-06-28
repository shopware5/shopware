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

namespace ShopwarePlugins\RestApi\Components;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\User\User;

class StaticResolver implements \Zend_Auth_Adapter_Http_Resolver_Interface
{
    /**
     * Contains the shopware model manager
     *
     * @var ModelManager
     */
    protected $modelManager;

    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * Resolve username/realm to password/hash/etc.
     *
     * @param string $username Username
     * @param string $realm    Authentication Realm
     *
     * @return string|false user's shared secret, if the user is found in the
     *                      realm, false otherwise
     */
    public function resolve($username, $realm)
    {
        $repository = $this->modelManager->getRepository(User::class);
        $user = $repository->findOneBy(['username' => $username, 'active' => true]);

        if (!$user) {
            return false;
        }

        $apiKey = $user->getApiKey();

        if (empty($apiKey)) {
            return false;
        }

        return md5($username . ':' . $realm . ':' . $apiKey);
    }
}
