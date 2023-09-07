<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components\Model;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Proxy;
use Exception;

/**
 * @ORM\MappedSuperclass()
 * @ORM\HasLifecycleCallbacks()
 */
abstract class LazyFetchModelEntity extends ModelEntity
{
    /**
     * @var EntityManagerInterface|null
     */
    private static $em;

    public static function setEntityManager(EntityManagerInterface $em)
    {
        self::$em = $em;
    }

    /**
     * @param object $object
     *
     * @return bool
     */
    public function isProxy($object)
    {
        return $object instanceof Proxy;
    }

    /**
     * @template TModel of object
     *
     * @param TModel|null $object
     * @param array       $condition
     *
     * @throws Exception
     *
     * @return TModel|null
     */
    public function fetchLazy($object, $condition, ?EntityManagerInterface $em = null)
    {
        if (!$object instanceof Proxy || $object->__isInitialized() || !$this->getId() || !method_exists($object, 'getId')) {
            return $object;
        }

        if ($object->getId()) {
            $object->__load();

            return $object;
        }

        if ($em === null) {
            $em = self::$em;
        }

        if ($em === null) {
            throw new Exception('Lazy fetch class not supported.');
        }

        /** @var class-string<TModel> $class */
        $class = get_parent_class($object);

        return $em->getRepository($class)->findOneBy($condition);
    }
}
