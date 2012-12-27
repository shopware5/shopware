<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Components_Model
 * @subpackage Model
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

namespace Shopware\Components\Model;

use Doctrine\ORM\EntityManager,
    Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\Common\Util\ClassUtils,
    Doctrine\ORM\Proxy\ProxyFactory as ProxyFactoryBase;

/**
 * Interface for the various standard models.
 *
 * This interface defines all standard functions for the various models
 * These standard function must later be implemented in the various models.
 *
 * <code>
 * $modelRepository = new Shopware\Components\Models\ModelRepository;
 * $modelRepository->createQueryBuilder();
 * </code>
 */
class ProxyFactory extends ProxyFactoryBase
{
    /**
     * Gets a reference proxy instance for the entity of the given type and identified by
     * the given identifier.
     *
     * @param string $className
     * @param mixed $identifier
     * @return object
     */
    public function getProxy($className, $identifier)
    {
        $fqn = ClassUtils::generateProxyClassName($className, $this->_proxyNamespace);

        if (!class_exists($fqn, false)) {
            $fileName = $this->getProxyFileName($className);
            if(!file_exists($fileName) || $this->_autoGenerate) {
                $this->_generateProxyClass($this->_em->getClassMetadata($className), $fileName, self::$_proxyClassTemplate);
            }
            require $fileName;
        }

        if ( ! $this->_em->getMetadataFactory()->hasMetadataFor($fqn)) {
            $this->_em->getMetadataFactory()->setMetadataFor($fqn, $this->_em->getClassMetadata($className));
        }

        $entityPersister = $this->_em->getUnitOfWork()->getEntityPersister($className);

        return new $fqn($entityPersister, $identifier);
    }

    /**
     * Generate the Proxy file name
     *
     * @param string $className
     * @return string
     */
    protected function getProxyFileName($className)
    {
        return $this->_proxyDir . DIRECTORY_SEPARATOR .
               \Shopware::REVISION . '__CG__' .
               str_replace('\\', '', $className) . '.php';
    }
}
