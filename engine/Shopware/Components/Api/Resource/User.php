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

use Shopware\Bundle\AccountBundle\Service\Validator\UserValidator;
use Shopware\Components\Api\Exception as ApiException;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\User\User as UserModel;

/**
 * User API Resource
 *
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class User extends Resource
{
    /**
     * @return \Shopware\Models\User\Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository('Shopware\Models\User\User');
    }

    /**
     * @return \Shopware\Models\User\Repository
     */
    public function getRoleRepository()
    {
        return $this->getManager()->getRepository('Shopware\Models\User\Role');
    }

    /**
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return array|\Shopware\Models\User\User
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        $builder = $this->getRepository()->createQueryBuilder('user');
        $builder->select(['users', 'attribute'])
            ->from('Shopware\Models\User\User', 'users')
            ->leftJoin('users.attribute', 'attribute')
            ->where('users.id = ?1')
            ->setParameter(1, $id);

        /** @var $user \Shopware\Models\User\User */
        $user = $builder->getQuery()->getOneOrNullResult($this->getResultMode());

        if (!$user) {
            throw new ApiException\NotFoundException("User by id $id not found");
        }

        return $user;
    }

    /**
     * Returns a list of user objects.
     *
     * @param int   $offset
     * @param int   $limit
     * @param array $criteria
     * @param array $orderBy
     *
     * @return array
     */
    public function getList($offset = 0, $limit = 25, array $criteria = [], array $orderBy = [])
    {
        $this->checkPrivilege('read');

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

        return [
            'total' => $paginator->count(),
            'data' => $users,
        ];
    }

    /**
     * @param array $params
     *
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @throws \Shopware\Components\Api\Exception\ValidationException
     *
     * @return \Shopware\Models\User\User
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');

        // create models
        $user = new UserModel();
        $params = $this->prepareAssociatedData($params, $user);
        $user->fromArray($params);

        /** @var UserValidator $userValidator */
        $userValidator = $this->getContainer()->get('shopware_account.user_validator');
        $userValidator->validate($user);

        $this->getManager()->persist($user);
        $this->flush();

        return $user;
    }

    /**
     * @param int   $id
     * @param array $params
     *
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\ValidationException
     *
     * @return \Shopware\Models\User\User
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        $builder = $this->getManager()->createQueryBuilder();
        $builder->select([
            'user',
            'userAttribute',
            'role',
        ])
            ->from('Shopware\Models\User\User', 'user')
            ->leftJoin('user.attribute', 'userAttribute')
            ->leftJoin('user.role', 'role')
            ->where('user.id = ?1')
            ->setParameter(1, $id);

        /** @var $user \Shopware\Models\User\User */
        $user = $builder->getQuery()->getOneOrNullResult(self::HYDRATE_OBJECT);

        if (!$user) {
            throw new ApiException\NotFoundException("User with id $id not found");
        }

        $params = $this->prepareAssociatedData($params, $user);
        $user->fromArray($params);

        /** @var UserValidator $userValidator */
        $userValidator = $this->getContainer()->get('shopware_account.user_validator');
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
     * @return \Shopware\Models\User\User
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $user \Shopware\Models\User\User */
        $user = $this->getRepository()->find($id);

        if (!$user) {
            throw new ApiException\NotFoundException("User by id $id not found");
        }

        $this->getManager()->remove($user);
        $this->flush();

        return $user;
    }

    /**
     * @see https://gist.github.com/tylerhall/521810
     *
     * @param int   $length
     * @param array $availableSets
     *
     * @return string
     *
     * Generates a strong password of N length containing at least one lower case letter,
     * one uppercase letter, one digit, and one special character. The remaining characters
     * in the password are chosen at random from those four sets.
     *
     * The available characters in each set are user friendly - there are no ambiguous
     * characters such as i, l, 1, o, 0, etc. This makes it much easier for users to manually
     * type or speak their passwords.
     */
    public function generatePassword($length = 9, $availableSets = ['l', 'u', 'd', 's'])
    {
        $sets = [];
        if (in_array('l', $availableSets)) {
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        }
        if (in_array('u', $availableSets)) {
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        }
        if (in_array('d', $availableSets)) {
            $sets[] = '23456789';
        }
        if (in_array('s', $availableSets)) {
            $sets[] = '!@#$%&*?';
        }

        $all = '';
        $password = '';

        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }

        $all = str_split($all);
        for ($i = 0; $i < $length - count($sets); ++$i) {
            $password .= $all[array_rand($all)];
        }
        $password = str_shuffle($password);

        return $password;
    }

    /**
     * @param array                      $data
     * @param \Shopware\Models\User\User $user
     *
     * @throws ApiException\CustomValidationException
     *
     * @return array
     */
    protected function prepareAssociatedData(array $data, UserModel $user)
    {
        // check if a role id or role name is passed and load the role model or set the role parameter to null.
        if (!empty($data['roleId'])) {
            $data['role'] = $this->getManager()->find('Shopware\Models\User\Role', $data['roleId']);

            if (empty($data['role'])) {
                throw new ApiException\CustomValidationException(sprintf('Role by id %s not found', $data['roleId']));
            }
        } elseif (isset($data['role']) && ($data['role'] >= 0)) {
            $role = $this->getManager()->getRepository('Shopware\Models\User\Role')->findOneBy(['name' => $data['role']]);

            if (!$role) {
                throw new ApiException\CustomValidationException(sprintf('Role by name %s not found', $data['role']));
            }
            $data['role'] = $role;
        } else {
            unset($data['role']);
        }

        // check if a locale id or name is passed.
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
            throw new ApiException\CustomValidationException('An E-Mail is required');
        }
        if (empty($data['username']) && empty($user->getUsername())) {
            throw new ApiException\CustomValidationException('A Username is required');
        }
        if (empty($data['name']) && empty($user->getName())) {
            throw new ApiException\CustomValidationException('A Name is required');
        }

        if (!isset($data['localeId'])) {
            $data['localeId'] = 1;    // de_DE
        }

        /** @var \Shopware\Components\Password\Manager $passwordEncoderRegistry */
        $passwordEncoderRegistry = $this->getContainer()->get('PasswordEncoder');
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
        $localeIds = [1, 2];

        return in_array($id, $localeIds);
    }

    /**
     * @param string $locale
     *
     * @return int
     */
    private function getLocaleIdFromLocale($locale)
    {
        $locales = [
            'de_de' => 1,
            'en_gb' => 2,
        ];
        $locale = strtolower($locale);

        if (!isset($locales[$locale])) {
            return null;
        }

        return $locales[$locale];
    }
}
