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

namespace Shopware\Components\Api\Resource;

use Shopware\Components\Api\Exception as ApiException;
use Shopware\Components\Auth\Validator\UserValidator;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Config\Element;
use Shopware\Models\Shop\Locale;
use Shopware\Models\User\Role;
use Shopware\Models\User\User as UserModel;

/**
 * User API Resource
` */
class User extends Resource
{
    /**
     * @return \Shopware\Models\User\Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository(UserModel::class);
    }

    /**
     * @return \Shopware\Models\User\Repository
     */
    public function getRoleRepository()
    {
        return $this->getManager()->getRepository(Role::class);
    }

    /**
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return array|UserModel
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read', 'usermanager');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        $builder = $this->getRepository()->createQueryBuilder('user');
        $builder->select(['users', 'attribute'])
            ->from(UserModel::class, 'users')
            ->leftJoin('users.attribute', 'attribute')
            ->where('users.id = ?1')
            ->setParameter(1, $id);

        /** @var UserModel|array|null $user */
        $user = $builder->getQuery()->getOneOrNullResult($this->getResultMode());

        if (!$user) {
            throw new ApiException\NotFoundException(sprintf('User by id %s not found', $id));
        }

        if (!$this->hasPrivilege('create', 'usermanager')
            && !$this->hasPrivilege('update', 'usermanager')) {
            if (is_array($user)) {
                unset($user['apiKey'], $user['sessionId'], $user['password'], $user['encoder']);
            } else {
                $user->setApiKey('');
                $user->setSessionId('');
                $user->setPassword('');
                $user->setEncoder('');
            }
        }

        return $user;
    }

    /**
     * Returns a list of user objects.
     *
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getList($offset = 0, $limit = 25, array $criteria = [], array $orderBy = [])
    {
        $this->checkPrivilege('read', 'usermanager');

        /** @var QueryBuilder $builder */
        $builder = $this->getRepository()->createQueryBuilder('user')
            ->join('user.role', 'role');

        $builder->addFilter($criteria)
            ->addOrderBy($orderBy)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $query = $builder->getQuery();
        $query->setHydrationMode($this->getResultMode());

        $paginator = $this->getManager()->createPaginator($query);

        $users = $paginator->getIterator()->getArrayCopy();

        if (!$this->hasPrivilege('create', 'usermanager')
            && !$this->hasPrivilege('update', 'usermanager')) {
            foreach ($users as &$user) {
                unset($user['apiKey'], $user['sessionId'], $user['password'], $user['encoder']);
            }
        }

        return [
            'total' => $paginator->count(),
            'data' => $users,
        ];
    }

    /**
     * @return UserModel
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create', 'usermanager');

        // Create models
        $user = new UserModel();
        $params = $this->prepareAssociatedData($params, $user);
        $user->fromArray($params);

        /** @var UserValidator $userValidator */
        $userValidator = $this->getContainer()->get('shopware.auth.validator.user_validator');
        $userValidator->validate($user);

        $this->getManager()->persist($user);
        $this->flush();

        return $user;
    }

    /**
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     *
     * @return UserModel
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update', 'usermanager');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        $builder = $this->getManager()->createQueryBuilder();
        $builder->select([
            'user',
            'userAttribute',
            'role',
        ])
            ->from(UserModel::class, 'user')
            ->leftJoin('user.attribute', 'userAttribute')
            ->leftJoin('user.role', 'role')
            ->where('user.id = ?1')
            ->setParameter(1, $id);

        /** @var UserModel|null $user */
        $user = $builder->getQuery()->getOneOrNullResult(self::HYDRATE_OBJECT);

        if (!$user) {
            throw new ApiException\NotFoundException(sprintf('User by id %s not found', $id));
        }

        $params = $this->prepareAssociatedData($params, $user);
        $user->fromArray($params);

        /** @var UserValidator $userValidator */
        $userValidator = $this->getContainer()->get('shopware.auth.validator.user_validator');
        $userValidator->validate($user);

        $this->getManager()->persist($user);
        $this->flush();

        return $user;
    }

    /**
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return UserModel
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete', 'usermanager');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        /** @var UserModel|null $user */
        $user = $this->getRepository()->find($id);

        if (!$user) {
            throw new ApiException\NotFoundException(sprintf('User by id %s not found', $id));
        }

        $this->getManager()->remove($user);
        $this->flush();

        return $user;
    }

    /**
     * @param string                                   $privilege
     * @param string|\Zend_Acl_Resource_Interface|null $resource
     *
     * @throws ApiException\PrivilegeException
     */
    public function checkPrivilege($privilege, $resource = null)
    {
        if (!$this->getRole() || !$this->getAcl()) {
            throw new ApiException\PrivilegeException('Unable to get role or acl');
        }

        if (!$resource) {
            $calledClass = get_called_class();
            $calledClass = explode('\\', $calledClass);
            $resource = strtolower(end($calledClass));
        }

        if (!$this->getAcl()->has($resource)) {
            throw new ApiException\PrivilegeException(sprintf('No resource "%s" found', $resource));
        }

        $role = $this->getRole();

        if (!$this->getAcl()->isAllowed($role, $resource, $privilege)) {
            throw new ApiException\PrivilegeException(
                sprintf(
                    'Role "%s" is not allowed to "%s" on resource "%s"',
                    is_string($role) ? $role : $role->getRoleId(),
                    $privilege,
                    is_string($resource) ? $resource : $resource->getResourceId()
                )
            );
        }
    }

    /**
     * @param string      $privilege
     * @param string|null $resource
     *
     * @return bool
     */
    public function hasPrivilege($privilege, $resource = null)
    {
        try {
            $this->checkPrivilege($privilege, $resource);
        } catch (ApiException\PrivilegeException $exception) {
            return false;
        }

        return true;
    }

    /**
     * @throws ApiException\CustomValidationException
     *
     * @return array
     */
    protected function prepareAssociatedData(array $data, UserModel $user)
    {
        // Check if a role id or role name is passed and load the role model or set the role parameter to null.
        if (!empty($data['roleId'])) {
            $data['role'] = $this->getManager()->find(Role::class, $data['roleId']);

            if (empty($data['role'])) {
                throw new ApiException\CustomValidationException(sprintf('Role by id %s not found', $data['roleId']));
            }
        } elseif (isset($data['role']) && ($data['role'] >= 0)) {
            $role = $this->getManager()->getRepository(Role::class)->findOneBy(['name' => $data['role']]);

            if (!$role) {
                throw new ApiException\CustomValidationException(sprintf('Role by name %s not found', $data['role']));
            }
            $data['role'] = $role;
        } else {
            unset($data['role']);
        }

        // Check if a locale id or name is passed.
        if (!empty($data['localeId'])) {
            if (!$this->isLocaleId($data['localeId'])) {
                throw new ApiException\CustomValidationException(sprintf('Locale by id %s not found', $data['localeId']));
            }
        } elseif (!empty($data['locale'])) {
            $localeId = $this->getLocaleIdFromLocale($data['locale']);
            if (!$localeId) {
                throw new ApiException\CustomValidationException(sprintf('Locale by name %s not found', $data['locale']));
            }
            $data['localeId'] = $localeId;
        } else {
            unset($data['locale']);
        }

        if (!empty($data['attribute'])) {
            foreach ($data['attribute'] as $key => $value) {
                if (is_numeric($key)) {
                    $data['attribute']['attribute' . $key] = $value;
                    unset($data[$key]);
                }
            }
        }

        if (empty($data['email']) && empty($user->getEmail())) {
            throw new ApiException\CustomValidationException('An e-mail is required');
        }
        if (empty($data['username']) && empty($user->getUsername())) {
            throw new ApiException\CustomValidationException('A username is required');
        }
        if (empty($data['name']) && empty($user->getName())) {
            throw new ApiException\CustomValidationException('A name is required');
        }

        if (!isset($data['localeId'])) {
            $data['localeId'] = 2; // en_GB
        }

        /** @var \Shopware\Components\Password\Manager $passwordEncoderRegistry */
        $passwordEncoderRegistry = $this->getContainer()->get('passwordencoder');
        $defaultEncoderName = $passwordEncoderRegistry->getDefaultPasswordEncoderName();
        $encoder = $passwordEncoderRegistry->getEncoderByName($defaultEncoderName);

        $data['password'] = $encoder->encodePassword($data['password']);
        $data['encoder'] = $encoder->getName();

        return $data;
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    private function isLocaleId($id)
    {
        $elementRepository = Shopware()->Models()->getRepository(Element::class);
        $element = $elementRepository->findOneByName('backendLocales');
        if (!$element) {
            return false;
        }

        $locales = $element->getValue();

        return in_array($id, $locales);
    }

    /**
     * @param string $locale
     *
     * @return int|null
     */
    private function getLocaleIdFromLocale($locale)
    {
        $localeRepository = Shopware()->Models()->getRepository(Locale::class);

        /** @var \Shopware\Models\Shop\Locale|null $locale */
        $locale = $localeRepository->findOneByLocale($locale);
        if (!$locale) {
            return null;
        }

        $localeId = $locale->getId();
        if (!$this->isLocaleId($localeId)) {
            return null;
        }

        return $localeId;
    }
}
