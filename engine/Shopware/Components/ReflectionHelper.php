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

namespace Shopware\Components;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

class ReflectionHelper
{
    /**
     * Create a class instance from a class name string
     *
     * @template TClass of object
     *
     * @param class-string<TClass> $className
     * @param array                $arguments
     * @param bool                 $secure      Make sure that the class is a valid shopware class
     * @param string               $docPath     Optional document root parameter where the class should be found
     * @param array                $directories an array of directories in which the class should be found
     *
     * @see \Shopware\Components\ReflectionHelper::verifyClass()
     *
     * @throws ReflectionException      Class could not be found
     * @throws InvalidArgumentException
     * @throws RuntimeException         If the class has no namespace
     *
     * @return TClass
     */
    public function createInstanceFromNamedArguments($className, $arguments, $secure = true, $docPath = null, array $directories = [])
    {
        $reflectionClass = new ReflectionClass($className);

        $docPath = $docPath === null ? Shopware()->Container()->getParameter('shopware.app.rootDir') : $docPath;

        if (!\is_string($docPath)) {
            throw new RuntimeException('Parameter shopware.app.rootDir has to be an string');
        }

        /** @var string[] $folders */
        $folders = Shopware()->Container()->getParameter('shopware.plugin_directories');

        $folders[] = Shopware()->DocPath('engine_Shopware');
        $folders[] = Shopware()->DocPath('vendor_shopware_shopware');

        foreach ($folders as $folder) {
            $directories[] = substr($folder, \strlen($docPath));
        }

        if ($secure) {
            $this->verifyClass(
                $reflectionClass,
                $docPath,
                $directories
            );
        }

        if (!$reflectionClass->getConstructor()) {
            return $reflectionClass->newInstance();
        }

        $constructorParams = $reflectionClass->getConstructor()->getParameters();

        $newParams = [];
        foreach ($constructorParams as $constructorParam) {
            $paramName = $constructorParam->getName();

            if (!isset($arguments[$paramName])) {
                if (!$constructorParam->isOptional()) {
                    throw new RuntimeException(sprintf('Required constructor parameter missing: "$%s".', $paramName));
                }
                $newParams[] = $constructorParam->getDefaultValue();

                continue;
            }

            $newParams[] = $arguments[$paramName];
        }

        return $reflectionClass->newInstanceArgs($newParams);
    }

    /**
     * Verify that a given ReflectionClass object is within the documentroot (docPath)
     * and (optionally) that said class belongs to certain directories.
     *
     * @param string        $docPath     Path to the project's document root
     * @param array<string> $directories Set of directories in which the class file should be in
     *
     * @throws InvalidArgumentException If the class is out of scope (docpath mismatch)   (code: 1)
     * @throws InvalidArgumentException If the class is out of scope (directory mismatch) (code: 2)
     */
    private function verifyClass(ReflectionClass $class, string $docPath, array $directories): void
    {
        $fileName = $class->getFileName();
        if ($fileName === false) {
            throw new RuntimeException('Could not get file name');
        }
        $fileDir = substr($fileName, 0, \strlen($docPath));

        // Trying to execute a class outside the Shopware DocumentRoot
        if ($fileDir !== $docPath) {
            throw new InvalidArgumentException(sprintf('Class "%s" out of scope', $class->getFileName()), 1);
        }

        $fileName = substr($fileName, \strlen($docPath));

        $error = true;

        foreach ($directories as $directory) {
            $directory = strtolower(trim($directory, DIRECTORY_SEPARATOR));

            $classDir = substr($fileName, 0, \strlen($directory));
            $classDir = trim($classDir, DIRECTORY_SEPARATOR);
            $classDir = strtolower($classDir);

            if ($directory === $classDir) {
                $error = false;
                break;
            }
        }

        if ($error) {
            throw new InvalidArgumentException(sprintf('Class "%s" out of scope', $class->getFileName()), 2);
        }

        $className = $class->getName();
        $classImplements = class_implements($className);
        if (!\is_array($classImplements) || !\array_key_exists(ReflectionAwareInterface::class, $classImplements)) {
            throw new InvalidArgumentException(sprintf('Class %s has to implement the interface %s', $className, ReflectionAwareInterface::class));
        }
    }
}
